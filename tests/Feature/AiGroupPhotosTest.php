<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Mode arrivage : l'IA regarde toutes les photos d'un coup et regroupe celles du même article.
 */
class AiGroupPhotosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function useProvider(string $provider): void
    {
        Setting::set('ai_provider', $provider);
        Setting::set('ai_api_key', 'test-key');
        Setting::clearCache();
    }

    private function fakeOpenRouterAnswer(array $json): void
    {
        $this->useProvider('openrouter');
        Http::fake(['openrouter.ai/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode($json)]]],
        ])]);
    }

    /**
     * Miniatures envoyées en JSON (data URL) : pas de limite PHP max_file_uploads (20 par défaut sur cPanel).
     */
    private function photos(int $count): array
    {
        return array_map(function ($i) {
            $file = UploadedFile::fake()->image("photo-{$i}.jpg", 300, 300);

            return 'data:image/jpeg;base64,'.base64_encode(file_get_contents($file->getRealPath()));
        }, range(1, $count));
    }

    private function group(array $photos)
    {
        return $this->actingAs($this->admin)->postJson(route('admin.ai.group-photos'), ['images' => $photos]);
    }

    public function test_the_ai_sorts_the_photos_of_an_arrival_by_article(): void
    {
        // L'IA numérote les photos à partir de 1 ; la page les repère à partir de 0
        $this->fakeOpenRouterAnswer(['groups' => [[1, 3], [2]]]);

        $this->group($this->photos(3))
            ->assertOk()
            ->assertExactJson(['groups' => [[0, 2], [1]]]);

        Http::assertSent(function ($request) {
            $content = $request->data()['messages'][0]['content'];
            $images = collect($content)->where('type', 'image_url');

            return $images->count() === 3
                && str_contains($content[0]['text'], 'MÊME article')
                && str_starts_with($images->first()['image_url']['url'], 'data:image/jpeg;base64,');
        });
    }

    public function test_a_photo_forgotten_or_repeated_by_the_ai_still_lands_in_exactly_one_article(): void
    {
        // Photo 2 citée deux fois, photo 9 inexistante, photo 3 oubliée
        $this->fakeOpenRouterAnswer(['groups' => [[1, 2], [2, 9], []]]);

        $this->group($this->photos(3))
            ->assertOk()
            ->assertExactJson(['groups' => [[0, 1], [2]]]);
    }

    public function test_gemini_receives_every_photo_in_a_single_request(): void
    {
        $this->useProvider('gemini');
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => '{"groups": [[1, 2]]}']]]]],
        ])]);

        $this->group($this->photos(2))->assertOk()->assertExactJson(['groups' => [[0, 1]]]);

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => collect($request->data()['contents'][0]['parts'])->whereNotNull('inline_data')->count() === 2);
    }

    public function test_an_unreadable_answer_is_reported_without_creating_anything(): void
    {
        $this->fakeOpenRouterAnswer(['réponse' => 'je ne sais pas']);

        $this->group($this->photos(2))
            ->assertStatus(422)
            ->assertJsonPath('message', "Réponse de l'IA illisible. Réessayez.");
    }

    public function test_sorting_needs_a_configured_ai(): void
    {
        $this->group($this->photos(2))
            ->assertStatus(422)
            ->assertJsonPath('message', "L'IA n'est pas configurée. Ajoutez une clé API dans Paramètres → IA.");
    }

    public function test_forty_photos_are_sorted_in_a_single_request(): void
    {
        $this->fakeOpenRouterAnswer(['groups' => [range(1, 40)]]);

        $this->group($this->photos(40))->assertOk()->assertExactJson(['groups' => [range(0, 39)]]);

        Http::assertSent(fn ($request) => collect($request->data()['messages'][0]['content'])->where('type', 'image_url')->count() === 40);
    }

    public function test_at_most_forty_photos_are_sorted_at_once(): void
    {
        $this->useProvider('openrouter');

        $this->group($this->photos(41))->assertStatus(422)->assertJsonValidationErrors('images');
    }

    public function test_something_that_is_not_a_photo_is_refused(): void
    {
        $this->useProvider('openrouter');

        $this->group([$this->photos(1)[0], 'data:image/jpeg;base64,'.base64_encode('pas une image')])
            ->assertStatus(422)
            ->assertJsonValidationErrors('images');

        $this->group([$this->photos(1)[0], 'https://exemple.ci/photo.jpg'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('images.1');
    }

    public function test_the_import_page_tells_which_ai_provider_paces_the_analysis(): void
    {
        $this->useProvider('openrouter');

        $this->actingAs($this->admin)->get(route('admin.products.import-images'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('aiProvider', 'openrouter'));
    }
}
