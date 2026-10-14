<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Service IA multi-fournisseurs (Gemini, OpenRouter).
 * Le fournisseur, la clé et le modèle se règlent dans Admin → Paramètres → IA.
 */
class AiService
{
    public const PROVIDERS = [
        'gemini' => ['label' => 'Google Gemini', 'default_model' => 'gemini-3.8-flash'],
        'openrouter' => ['label' => 'OpenRouter (Qwen, DeepSeek…)', 'default_model' => 'qwen/qwen2.5-vl-72b-instruct:free'],
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
     * @return array{name: string, short_description: string, description: string, category_id: ?int, category_name: ?string}
     */
    public function suggestProduct(?string $imageBinary, ?string $mimeType, ?string $currentName = null): array
    {
        $categories = $this->categoryList();

        $catalog = collect($categories)->map(fn ($c) => "{$c['id']}: {$c['path']}")->implode("\n");

        $prompt = <<<PROMPT
Tu es un expert e-commerce en Côte d'Ivoire. Analyse ce produit et rédige sa fiche en français, de façon simple, vendeuse et honnête (n'invente pas de matière, de marque ou de caractéristique invisible).
PROMPT;
        if ($currentName) {
            $prompt .= "\nNom actuel du produit : {$currentName}";
        }
        $prompt .= <<<PROMPT

Choisis la catégorie la plus précise possible dans cette liste (id: chemin). Préfère la catégorie la plus profonde qui correspond. Si aucune ne convient, mets null.
{$catalog}

Réponds UNIQUEMENT avec un objet JSON valide, sans texte autour :
{"name": "nom court et clair (max 60 caractères)", "short_description": "1 phrase d'accroche (max 160 caractères)", "description": "2 à 4 phrases descriptives", "category_id": 12}
PROMPT;

        $text = $this->generate($prompt, $imageBinary, $mimeType);
        $data = $this->parseJson($text);

        $categoryId = isset($data['category_id']) && is_numeric($data['category_id']) ? (int) $data['category_id'] : null;
        $category = $categoryId ? collect($categories)->firstWhere('id', $categoryId) : null;

        return [
            'name' => mb_substr(trim((string) ($data['name'] ?? '')), 0, 255),
            'short_description' => mb_substr(trim((string) ($data['short_description'] ?? '')), 0, 500),
            'description' => trim((string) ($data['description'] ?? '')),
            'category_id' => $category ? $category['id'] : null,
            'category_name' => $category ? $category['path'] : null,
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

        @set_time_limit(60);

        // Modèle principal, puis modèle de secours si le principal est surchargé
        $models = array_values(array_unique(array_filter([$this->model, $this->fallbackModel])));

        foreach ($models as $i => $model) {
            $response = $this->provider === 'openrouter'
                ? $this->callOpenRouter($model, $prompt, $imageBinary, $mimeType)
                : $this->callGemini($model, $prompt, $imageBinary, $mimeType);

            if ($response->successful()) {
                return $this->provider === 'openrouter'
                    ? (string) $response->json('choices.0.message.content', '')
                    : (string) $response->json('candidates.0.content.parts.0.text', '');
            }

            $overloaded = in_array($response->status(), [429, 500, 502, 503]);
            if (! $overloaded || $i === count($models) - 1) {
                $this->fail($response->status(), $response->json('error.message') ?? $response->body());
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

    protected function fail(int $status, string $message): never
    {
        Log::warning('AiService: erreur fournisseur', ['provider' => $this->provider, 'status' => $status, 'message' => $message]);

        $hint = match (true) {
            $status === 429 => 'Quota gratuit atteint. Patientez une minute puis réessayez.',
            in_array($status, [500, 502, 503]) => 'Le service IA est surchargé en ce moment (problème temporaire chez le fournisseur). Réessayez dans quelques minutes, ou ajoutez un modèle de secours dans Paramètres → IA.',
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
    protected function categoryList(): array
    {
        return Category::active()->with('parent.parent')->get()
            ->map(fn ($c) => ['id' => $c->id, 'path' => $c->full_path])
            ->values()
            ->all();
    }
}
