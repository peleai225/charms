<?php

namespace Tests\Feature;

use Anthropic\Client;
use App\Models\Setting;
use App\Models\User;
use App\Services\AiService;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Tests\TestCase;

class AiServiceClaudeTest extends TestCase
{
    use RefreshDatabase;

    private object $transport;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    private function useClaude(array $responses, ?string $fallbackModel = null): void
    {
        Setting::set('ai_provider', 'anthropic');
        Setting::set('ai_api_key', 'sk-ant-test');
        Setting::set('ai_model', '');
        Setting::set('ai_fallback_model', $fallbackModel ?? '');
        Setting::clearCache();

        $this->transport = new class($responses) implements ClientInterface
        {
            public array $requests = [];

            public function __construct(private array $responses) {}

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->requests[] = $request;
                $next = array_shift($this->responses);

                return $next instanceof \Throwable ? throw $next : $next;
            }
        };

        $transport = $this->transport;
        $this->app->bind(Client::class, fn ($app, $params) => new Client(
            apiKey: $params['apiKey'],
            requestOptions: array_merge($params['requestOptions'] ?? [], ['transporter' => $transport]),
        ));
    }

    private function claudeAnswer(string $text, string $model = 'claude-opus-5-5'): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'id' => 'msg_test',
            'type' => 'message',
            'role' => 'assistant',
            'model' => $model,
            'content' => [['type' => 'text', 'text' => $text]],
            'stop_reason' => 'end_turn',
            'stop_sequence' => null,
            'usage' => ['input_tokens' => 10, 'output_tokens' => 2],
        ]));
    }

    private function claudeError(int $status, string $type, string $message): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode([
            'type' => 'error',
            'error' => ['type' => $type, 'message' => $message],
        ]));
    }

    private function body(int $index): array
    {
        return json_decode((string) $this->transport->requests[$index]->getBody(), true);
    }

    public function test_claude_receives_the_photo_and_the_prompt_and_its_answer_is_returned(): void
    {
        $this->useClaude([$this->claudeAnswer('OK')]);

        $answer = app(AiService::class)->generate('Décris ce produit', 'fake-image-bytes', 'image/png');

        $this->assertSame('OK', $answer);
        $this->assertCount(1, $this->transport->requests);

        $request = $this->transport->requests[0];
        $this->assertSame('https://api.anthropic.com/v1/messages', (string) $request->getUri()->withQuery(''));
        $this->assertStringContainsString('sk-ant-test', $request->getHeaderLine('x-api-key').$request->getHeaderLine('Authorization'));

        $body = $this->body(0);
        $this->assertSame('claude-opus-5-5', $body['model']);
        $this->assertSame('default', $body['fallbacks']);

        $content = $body['messages'][0]['content'];
        $image = collect($content)->firstWhere('type', 'image');
        $this->assertSame('base64', $image['source']['type']);
        $this->assertSame('image/png', $image['source']['media_type']);
        $this->assertSame(base64_encode('fake-image-bytes'), $image['source']['data']);
        $this->assertSame('Décris ce produit', collect($content)->firstWhere('type', 'text')['text']);
    }

    public function test_claude_switches_to_the_fallback_model_when_the_main_one_is_overloaded(): void
    {
        $this->useClaude([
            $this->claudeError(529, 'overloaded_error', 'Overloaded'),
            $this->claudeAnswer('OK', 'claude-haiku-5-5'),
        ], fallbackModel: 'claude-haiku-5-5');

        $answer = app(AiService::class)->generate('Réponds OK');

        $this->assertSame('OK', $answer);
        $this->assertSame('claude-opus-5-5', $this->body(0)['model']);
        $this->assertSame('claude-haiku-5-5', $this->body(1)['model']);
    }

    public function test_an_invalid_claude_key_gives_a_clear_message(): void
    {
        $this->useClaude([$this->claudeError(401, 'authentication_error', 'invalid x-api-key')]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Clé API invalide');

        app(AiService::class)->generate('Réponds OK');
    }

    public function test_claude_not_responding_gives_a_clear_message(): void
    {
        $this->useClaude([
            new ConnectException('Connection timed out', new Request('POST', 'https://api.anthropic.com/v1/messages')),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ne répond pas');

        app(AiService::class)->generate('Réponds OK');
    }

    public function test_admin_can_choose_claude_as_the_ai_provider(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/settings/ai', [
            'ai_provider' => 'anthropic',
            'ai_api_key' => 'sk-ant-test',
        ])->assertSessionHasNoErrors();

        Setting::clearCache();
        $this->assertSame('anthropic', Setting::get('ai_provider'));
    }
}
