<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AiVariantProductFromPhotosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Attribute $color;

    private Attribute $shoeSize;

    private array $colors = [];

    private array $sizes = [];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Http::preventStrayRequests();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->color = Attribute::create(['name' => 'Couleur', 'slug' => 'couleur', 'type' => 'color']);
        foreach (['Noir' => '#000000', 'Rouge' => '#FF0000'] as $i => $hex) {
            $this->colors[$i] = AttributeValue::create(['attribute_id' => $this->color->id, 'value' => $i, 'color_code' => $hex]);
        }

        $this->shoeSize = Attribute::create(['name' => 'Pointure', 'slug' => 'pointure', 'type' => 'select']);
        foreach (['38', '39', '40'] as $order => $size) {
            $this->sizes[$size] = AttributeValue::create(['attribute_id' => $this->shoeSize->id, 'value' => $size, 'order' => $order]);
        }
    }

    private function photo(string $name): UploadedFile
    {
        return UploadedFile::fake()->image($name, 400, 400);
    }

    private function store(array $payload)
    {
        return $this->actingAs($this->admin)->postJson(route('admin.ai.store-variant-product'), $payload + [
            'name' => 'Ballerines',
            'sale_price' => 8000,
            'status' => 'draft',
        ]);
    }

    private function variantFor(Product $product, ?int $colorId, ?int $sizeId): ?ProductVariant
    {
        return $product->variants()->with('attributeValues')->get()->first(function ($v) use ($colorId, $sizeId) {
            $ids = $v->attributeValues->pluck('id')->sort()->values()->all();

            return $ids === collect([$colorId, $sizeId])->filter()->sort()->values()->all();
        });
    }

    public function test_photos_of_one_article_become_one_product_with_a_variant_per_color_and_size(): void
    {
        $variants = [];
        $stock = 1;
        foreach ([0, 1, 2] as $colorIndex) {
            foreach (['38', '39'] as $size) {
                $variants[] = ['color_index' => $colorIndex, 'value_id' => $this->sizes[$size]->id, 'stock' => $stock++];
            }
        }

        $response = $this->store([
            'images' => [$this->photo('noir.jpg'), $this->photo('rouge.jpg'), $this->photo('bordeaux.jpg')],
            'colors' => [
                ['id' => $this->colors['Noir']->id],
                ['id' => $this->colors['Rouge']->id],
                ['name' => 'Bordeaux', 'hex' => '#7B1E2B'],
            ],
            'option_attribute_id' => $this->shoeSize->id,
            'variants' => $variants,
        ]);

        $response->assertOk()->assertJsonStructure(['id', 'edit_url']);
        $this->assertSame(1, Product::count());

        $product = Product::with('images')->first();
        $this->assertTrue($product->has_variants);
        $this->assertSame(21, $product->stock_quantity);
        $this->assertSame(6, $product->variants()->count());
        $this->assertCount(3, $product->images);
        $this->assertTrue($product->images->sortBy('position')->first()->is_primary);

        $bordeaux = AttributeValue::where('attribute_id', $this->color->id)->where('value', 'Bordeaux')->first();
        $this->assertNotNull($bordeaux);
        $this->assertSame('#7B1E2B', $bordeaux->color_code);

        $rouge39 = $this->variantFor($product, $this->colors['Rouge']->id, $this->sizes['39']->id);
        $this->assertNotNull($rouge39);
        $this->assertSame(4, $rouge39->stock_quantity);
        $this->assertNull($rouge39->sale_price);
        $this->assertSame($product->images->firstWhere('position', 1)->path, $rouge39->image);
        $this->assertSame('Rouge / 39', $rouge39->name);

        $skus = $product->variants()->pluck('sku');
        $this->assertSame($skus->count(), $skus->unique()->count());
    }

    public function test_values_sent_as_text_by_the_browser_form_are_accepted(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.ai.store-variant-product'), [
            'images' => [$this->photo('a.jpg'), $this->photo('b.jpg')],
            'colors' => [['id' => (string) $this->colors['Noir']->id], ['name' => 'Bordeaux', 'hex' => '#7B1E2B']],
            'option_attribute_id' => (string) $this->shoeSize->id,
            'variants' => [
                ['color_index' => '0', 'value_id' => (string) $this->sizes['38']->id, 'stock' => '3'],
                ['color_index' => '1', 'value_id' => (string) $this->sizes['38']->id, 'stock' => '0'],
            ],
            'name' => 'Ballerines',
            'sale_price' => '8000',
            'category_id' => '',
            'status' => 'active',
        ])->assertOk()->assertJson(['variants' => 2]);

        $product = Product::first();
        $this->assertSame('active', $product->status);

        $this->actingAs($this->admin)->get(route('admin.products.edit', $product))->assertOk();
        $this->get(route('shop.product', $product->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('product.variants', fn ($variants) => count($variants) === 2));
    }

    public function test_a_new_color_name_matching_an_existing_color_reuses_it(): void
    {
        $this->store([
            'images' => [$this->photo('a.jpg'), $this->photo('b.jpg')],
            'colors' => [['name' => 'rouge'], ['id' => $this->colors['Noir']->id]],
            'variants' => [
                ['color_index' => 0, 'stock' => 3],
                ['color_index' => 1, 'stock' => 2],
            ],
        ])->assertOk();

        $this->assertSame(2, AttributeValue::where('attribute_id', $this->color->id)->count());
        $this->assertNotNull($this->variantFor(Product::first(), $this->colors['Rouge']->id, null));
    }

    public function test_a_single_photo_with_sizes_only_creates_size_variants(): void
    {
        $this->store([
            'images' => [$this->photo('a.jpg')],
            'option_attribute_id' => $this->shoeSize->id,
            'variants' => [
                ['value_id' => $this->sizes['38']->id, 'stock' => 2],
                ['value_id' => $this->sizes['40']->id, 'stock' => 0],
            ],
        ])->assertOk();

        $product = Product::first();
        $this->assertSame(2, $product->variants()->count());
        $this->assertSame(2, $this->variantFor($product, null, $this->sizes['38']->id)->stock_quantity);
    }

    public function test_a_variant_needs_a_color_or_a_size(): void
    {
        $this->store([
            'images' => [$this->photo('a.jpg')],
            'variants' => [['stock' => 1]],
        ])->assertJsonValidationErrors('variants.0');

        $this->assertSame(0, Product::count());
    }

    public function test_a_photo_used_as_a_color_must_have_a_color(): void
    {
        $this->store([
            'images' => [$this->photo('a.jpg')],
            'colors' => [[]],
            'variants' => [['color_index' => 0, 'stock' => 1]],
        ])->assertJsonValidationErrors('colors.0');
    }

    public function test_a_size_must_belong_to_the_chosen_attribute(): void
    {
        $this->store([
            'images' => [$this->photo('a.jpg')],
            'option_attribute_id' => $this->shoeSize->id,
            'variants' => [['value_id' => $this->colors['Noir']->id, 'stock' => 1]],
        ])->assertJsonValidationErrors('variants.0.value_id');
    }

    public function test_two_identical_variants_are_rejected(): void
    {
        $this->store([
            'images' => [$this->photo('a.jpg'), $this->photo('b.jpg')],
            'colors' => [['id' => $this->colors['Noir']->id], ['name' => 'noir']],
            'variants' => [
                ['color_index' => 0, 'stock' => 1],
                ['color_index' => 1, 'stock' => 1],
            ],
        ])->assertJsonValidationErrors('variants');

        $this->assertSame(0, Product::count());
    }

    public function test_the_import_page_suggests_the_sizes_already_used_in_the_category(): void
    {
        $shoes = Category::create(['name' => 'Chaussures', 'slug' => 'chaussures', 'is_active' => true]);
        $existing = Product::create(['name' => 'Sandales', 'sale_price' => 5000, 'status' => 'active', 'category_id' => $shoes->id, 'has_variants' => true]);
        foreach (['38', '39'] as $size) {
            $variant = ProductVariant::create(['product_id' => $existing->id, 'sku' => "SAN-{$size}", 'stock_quantity' => 1, 'is_active' => true]);
            DB::table('product_variant_values')->insert([
                'product_variant_id' => $variant->id,
                'attribute_id' => $this->shoeSize->id,
                'attribute_value_id' => $this->sizes[$size]->id,
            ]);
        }

        $this->actingAs($this->admin)
            ->get(route('admin.products.import-images'))
            ->assertInertia(fn (Assert $page) => $page
                ->where("categoryOptions.{$shoes->id}.attribute_id", $this->shoeSize->id)
                ->where("categoryOptions.{$shoes->id}.value_ids", fn ($ids) => collect($ids)->sort()->values()->all()
                    === [$this->sizes['38']->id, $this->sizes['39']->id])
                ->where('colors', fn ($colors) => collect($colors)->pluck('value')->contains('Noir'))
                ->where('optionAttributes', fn ($attrs) => collect($attrs)->pluck('id')->all() === [$this->shoeSize->id]));
    }

    private function fakeGeminiAnswer(array $json): void
    {
        Setting::set('ai_provider', 'gemini');
        Setting::set('ai_api_key', 'test-key');
        Setting::clearCache();

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($json)]]]]],
        ])]);
    }

    public function test_the_ai_recognizes_an_existing_color_on_the_photo(): void
    {
        $this->fakeGeminiAnswer([
            'name' => 'Ballerines', 'short_description' => 'Confort', 'description' => 'Plates.',
            'category_id' => null, 'color_id' => $this->colors['Rouge']->id, 'color_name' => 'Rouge', 'color_hex' => '#FF0000',
        ]);

        $result = app(AiService::class)->suggestProduct('img', 'image/jpeg');

        $this->assertSame($this->colors['Rouge']->id, $result['color_id']);
        $this->assertSame('Rouge', $result['color_name']);
        Http::assertSent(fn ($request) => str_contains(json_encode($request->data(), JSON_UNESCAPED_UNICODE), "{$this->colors['Noir']->id}: Noir"));
    }

    public function test_the_ai_names_a_color_that_does_not_exist_yet(): void
    {
        $this->fakeGeminiAnswer([
            'name' => 'Ballerines', 'short_description' => 'Confort', 'description' => 'Plates.',
            'category_id' => null, 'color_id' => 999, 'color_name' => 'Bordeaux', 'color_hex' => 'pas-un-code',
        ]);

        $result = app(AiService::class)->suggestProduct('img', 'image/jpeg');

        $this->assertNull($result['color_id']);
        $this->assertSame('Bordeaux', $result['color_name']);
        $this->assertNull($result['color_hex']);
    }
}
