<?php

namespace App\Services;

use Anthropic\Client as AnthropicClient;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Setting;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Service IA multi-fournisseurs (Gemini, OpenRouter, Anthropic Claude).
 * Le fournisseur, la clé et le modèle se règlent dans Admin → Paramètres → IA.
 */
class AiService
{
    public const PROVIDERS = [
        'gemini' => ['label' => 'Google Gemini', 'default_model' => 'gemini-3.8-flash'],
        'openrouter' => ['label' => 'OpenRouter (Qwen, DeepSeek…)', 'default_model' => 'qwen/qwen2.5-vl-72b-instruct:free'],
        'anthropic' => ['label' => 'Anthropic Claude', 'default_model' => 'claude-opus-5-5'],
    ];

    protected string $provider;

    protected ?string $apiKey;

    protected string $model;

    protected ?string $fallbackModel;

    public function __construct()
    {
        $this->provider = Setting::get('ai_provider', 'gemini');
        if (! isset(self::PROVIDERS[$this->provider])) {
            $this->provider = 'gemini';
        }
        $this->apiKey = Setting::get('ai_api_key') ?: null;
        $this->model = Setting::get('ai_model') ?: self::PROVIDERS[$this->provider]['default_model'];
        $this->fallbackModel = Setting::get('ai_fallback_model') ?: null;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Analyse une image (et/ou un nom) et propose une fiche produit + catégorie.
     *
     * @return array{name: string, short_description: string, description: string, category_id: ?int, category_name: ?string, color_id: ?int, color_name: ?string, color_hex: ?string}
     */
    public function suggestProduct(?string $imageBinary, ?string $mimeType, ?string $currentName = null): array
    {
        $categories = $this->categoryList();
        $colors = $this->colorList();

        $catalog = collect($categories)->map(fn ($c) => "{$c['id']}: {$c['path']}")->implode("\n");
        $palette = collect($colors)->map(fn ($c) => "{$c['id']}: {$c['value']}")->implode("\n") ?: '(aucune couleur enregistrée)';

        $prompt = <<<'PROMPT'
Tu es un expert e-commerce en Côte d'Ivoire. Analyse ce produit et rédige sa fiche en français, de façon simple, vendeuse et honnête (n'invente pas de matière, de marque ou de caractéristique invisible).
PROMPT;
        if ($currentName) {
            $prompt .= "\nNom actuel du produit : {$currentName}";
        }
        $prompt .= <<<PROMPT

Choisis la catégorie la plus précise possible dans cette liste (id: chemin). Préfère la catégorie la plus profonde qui correspond. Si aucune ne convient, mets null.
{$catalog}

Couleur principale de l'article lui-même (pas du fond ni de l'emballage). Si elle correspond à une couleur de cette liste (id: nom), mets son id dans color_id. Sinon mets color_id à null. Dans tous les cas, donne son nom simple en français dans color_name (ex : "Bordeaux") et son code hexadécimal dans color_hex.
{$palette}

Réponds UNIQUEMENT avec un objet JSON valide, sans texte autour :
{"name": "nom court et clair (max 60 caractères)", "short_description": "1 phrase d'accroche (max 160 caractères)", "description": "2 à 4 phrases descriptives", "category_id": 12, "color_id": 3, "color_name": "Rouge", "color_hex": "#C62828"}
PROMPT;

        $text = $this->generate($prompt, $imageBinary, $mimeType);
        $data = $this->parseJson($text);

        $categoryId = isset($data['category_id']) && is_numeric($data['category_id']) ? (int) $data['category_id'] : null;
        $category = $categoryId ? collect($categories)->firstWhere('id', $categoryId) : null;

        $colorId = isset($data['color_id']) && is_numeric($data['color_id']) ? (int) $data['color_id'] : null;
        $color = $colorId ? collect($colors)->firstWhere('id', $colorId) : null;
        $colorName = mb_substr(trim((string) ($data['color_name'] ?? '')), 0, 50);
        $colorHex = trim((string) ($data['color_hex'] ?? ''));

        return [
            'name' => mb_substr(trim((string) ($data['name'] ?? '')), 0, 255),
            'short_description' => mb_substr(trim((string) ($data['short_description'] ?? '')), 0, 500),
            'description' => trim((string) ($data['description'] ?? '')),
            'category_id' => $category ? $category['id'] : null,
            'category_name' => $category ? $category['path'] : null,
            'color_id' => $color ? $color['id'] : null,
            'color_name' => $color ? $color['value'] : ($colorName !== '' ? $colorName : null),
            'color_hex' => preg_match('/^#[0-9A-Fa-f]{6}$/', $colorHex) ? strtoupper($colorHex) : null,
        ];
    }

    /**
     * Recherche par photo (client) : décrit l'article en mots-clés + catégorie.
     *
     * @return array{label: string, keywords: array<int, string>, category_id: ?int}
     */
    public function describeForSearch(string $imageBinary, ?string $mimeType): array
    {
        $categories = $this->categoryList();
        $catalog = collect($categories)->map(fn ($c) => "{$c['id']}: {$c['path']}")->implode("\n");

        $prompt = <<<PROMPT
Un client d'une boutique en ligne envoie cette photo pour trouver un article similaire.
Identifie l'article principal visible. Donne :
- "label" : description courte en français (ex : "T-shirt rouge pour enfant", max 50 caractères)
- "keywords" : 3 à 6 mots-clés simples en français, au singulier, sans accents superflus, du plus important au moins important (type d'article d'abord, puis synonymes courants, couleur, motif). Ex : ["t-shirt", "tee-shirt", "rouge", "enfant"]
- "category_id" : la catégorie la plus précise de cette liste (id: chemin), ou null si aucune ne convient :
{$catalog}

Réponds UNIQUEMENT avec un objet JSON valide :
{"label": "...", "keywords": ["..."], "category_id": 12}
PROMPT;

        $data = $this->parseJson($this->generate($prompt, $imageBinary, $mimeType));

        $keywords = collect($data['keywords'] ?? [])
            ->filter(fn ($k) => is_string($k) && mb_strlen(trim($k)) >= 2)
            ->map(fn ($k) => mb_strtolower(mb_substr(trim($k), 0, 40)))
            ->unique()->take(6)->values()->all();

        $categoryId = isset($data['category_id']) && is_numeric($data['category_id']) ? (int) $data['category_id'] : null;
        $valid = $categoryId && collect($categories)->contains('id', $categoryId);

        return [
            'label' => mb_substr(trim((string) ($data['label'] ?? '')), 0, 80),
            'keywords' => $keywords,
            'category_id' => $valid ? $categoryId : null,
        ];
    }

    /**
     * Test simple de la connexion.
     */
    public function ping(): string
    {
        return trim($this->generate('Réponds uniquement par le mot : OK', null, null));
    }

    /**
     * Appel brut au fournisseur.
     */
    public function generate(string $prompt, ?string $imageBinary = null, ?string $mimeType = null): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException("L'IA n'est pas configurée. Ajoutez une clé API dans Paramètres → IA.");
        }

        // Limite pour les requêtes web seulement : en ligne de commande (tests, files d'attente) elle tuerait tout le processus
        if (! app()->runningInConsole()) {
            @set_time_limit(60);
        }

        // Modèle principal, puis modèle de secours si le principal est surchargé
        $models = array_values(array_unique(array_filter([$this->model, $this->fallbackModel])));

        foreach ($models as $i => $model) {
            if ($this->provider === 'anthropic') {
                try {
                    return $this->callAnthropic($model, $prompt, $imageBinary, $mimeType);
                } catch (APIStatusException $e) {
                    $status = (int) $e->status;
                    $message = $e->getMessage();
                } catch (APIConnectionException $e) {
                    throw new RuntimeException('Le service IA ne répond pas. Réessayez dans un instant.', 0, $e);
                }
            } else {
                $response = $this->provider === 'openrouter'
                    ? $this->callOpenRouter($model, $prompt, $imageBinary, $mimeType)
                    : $this->callGemini($model, $prompt, $imageBinary, $mimeType);

                if ($response->successful()) {
                    return $this->provider === 'openrouter'
                        ? (string) $response->json('choices.0.message.content', '')
                        : (string) $response->json('candidates.0.content.parts.0.text', '');
                }

                $status = $response->status();
                $message = $response->json('error.message') ?? $response->body();
            }

            // 529 = surcharge chez Anthropic
            $overloaded = in_array($status, [429, 500, 502, 503, 529]);
            if (! $overloaded || $i === count($models) - 1) {
                $this->fail($status, $message);
            }

            Log::info('AiService: modèle surchargé, bascule sur le modèle de secours', ['model' => $model]);
            usleep(800_000);
        }

        throw new RuntimeException("Le service IA n'a pas répondu.");
    }

    protected function callGemini(string $model, string $prompt, ?string $imageBinary, ?string $mimeType): Response
    {
        $parts = [['text' => $prompt]];
        if ($imageBinary) {
            $parts[] = ['inline_data' => ['mime_type' => $mimeType ?: 'image/jpeg', 'data' => base64_encode($imageBinary)]];
        }

        $response = Http::timeout(12)
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [['parts' => $parts]],
                'generationConfig' => ['temperature' => 0.4],
            ]);

        return $response;
    }

    protected function callOpenRouter(string $model, string $prompt, ?string $imageBinary, ?string $mimeType): Response
    {
        $content = [['type' => 'text', 'text' => $prompt]];
        if ($imageBinary) {
            $content[] = ['type' => 'image_url', 'image_url' => [
                'url' => 'data:'.($mimeType ?: 'image/jpeg').';base64,'.base64_encode($imageBinary),
            ]];
        }

        $response = Http::timeout(12)
            ->withToken($this->apiKey)
            ->withHeaders(['HTTP-Referer' => config('app.url'), 'X-Title' => config('app.name')])
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $content]],
                'temperature' => 0.4,
            ]);

        return $response;
    }

    protected function callAnthropic(string $model, string $prompt, ?string $imageBinary, ?string $mimeType): string
    {
        $content = [];
        if ($imageBinary) {
            $content[] = ['type' => 'image', 'source' => [
                'type' => 'base64',
                'mediaType' => $mimeType ?: 'image/jpeg',
                'data' => base64_encode($imageBinary),
            ]];
        }
        $content[] = ['type' => 'text', 'text' => $prompt];

        // Le SDK ne gère pas lui-même le délai d'attente : c'est le client HTTP qui l'impose
        $client = app()->makeWith(AnthropicClient::class, [
            'apiKey' => $this->apiKey,
            'requestOptions' => ['transporter' => new GuzzleClient(['timeout' => 45]), 'maxRetries' => 0],
        ]);

        $message = $client->beta->messages->create(
            maxTokens: 16000,
            messages: [['role' => 'user', 'content' => $content]],
            model: $model,
            fallbacks: 'default',
            outputConfig: ['effort' => 'low'],
            betas: ['server-side-fallback-2026-07-01'],
        );

        if ($message->stopReason === 'refusal') {
            throw new RuntimeException("L'IA a refusé d'analyser ce contenu. Essayez avec une autre photo.");
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                return (string) $block->text;
            }
        }

        return '';
    }

    protected function fail(int $status, string $message): never
    {
        Log::warning('AiService: erreur fournisseur', ['provider' => $this->provider, 'status' => $status, 'message' => $message]);

        $hint = match (true) {
            $status === 429 => 'Quota gratuit atteint. Patientez une minute puis réessayez.',
            in_array($status, [500, 502, 503, 529]) => 'Le service IA est surchargé en ce moment (problème temporaire chez le fournisseur). Réessayez dans quelques minutes, ou ajoutez un modèle de secours dans Paramètres → IA.',
            in_array($status, [401, 403]) => 'Clé API invalide ou refusée. Vérifiez-la dans Paramètres → IA.',
            $status === 404 => 'Modèle introuvable. Vérifiez le nom du modèle dans Paramètres → IA.',
            default => 'Le service IA a renvoyé une erreur.',
        };

        throw new RuntimeException("{$hint} ({$status} : ".mb_substr($message, 0, 200).')');
    }

    protected function parseJson(string $text): array
    {
        // Retirer d'éventuels blocs ```json … ```
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));

        if (preg_match('/\{.*\}/s', $text, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) {
                return $data;
            }
        }

        throw new RuntimeException("Réponse de l'IA illisible. Réessayez.");
    }

    /**
     * @return array<int, array{id: int, path: string}>
     */
    /**
     * @return array<int, array{id: int, value: string}>
     */
    protected function colorList(): array
    {
        $attribute = Attribute::colorAttribute();

        return $attribute
            ? $attribute->values()->get(['id', 'value'])->map(fn ($v) => ['id' => $v->id, 'value' => $v->value])->all()
            : [];
    }

    protected function categoryList(): array
    {
        return Category::active()->with('parent.parent')->get()
            ->map(fn ($c) => ['id' => $c->id, 'path' => $c->full_path])
            ->values()
            ->all();
    }
}
