<?php

namespace App\Services\Sku;

use App\Models\Category;
use App\Models\Product;
use App\Models\SkuCounter;
use App\Services\Sku\Exceptions\SkuGenerationException;
use Illuminate\Support\Facades\DB;

class SkuGeneratorService
{
    private const MAX_ATTEMPTS = 5;

    public function generateForProduct(?Category $category): string
    {
        $prefix = $category ? $this->resolveCategoryPrefix($category) : 'DIV';
        $scopeKey = "prefix:{$prefix}";

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $sequence = $this->reserveSequence($scopeKey);
            $candidate = sprintf('LGB-%s-%06d', $prefix, $sequence);

            if (! Product::where('sku', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new SkuGenerationException(
            'Impossible de générer un SKU unique après plusieurs tentatives. Veuillez réessayer ou saisir un SKU manuellement.'
        );
    }

    private function resolveCategoryPrefix(Category $category): string
    {
        if (! empty($category->code)) {
            return $category->code;
        }

        $letters = preg_replace('/[^A-Z]/', '', strtoupper($category->slug ?? ''));
        $prefix = substr($letters, 0, 4);

        return $prefix !== '' ? $prefix : 'GEN';
    }

    private function reserveSequence(string $scopeKey): int
    {
        return DB::transaction(function () use ($scopeKey) {
            $counter = SkuCounter::where('scope_key', $scopeKey)->lockForUpdate()->first();

            if (! $counter) {
                try {
                    SkuCounter::create(['scope_key' => $scopeKey, 'next_value' => 1]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // On ne récupère que le cas précis d'une ligne déjà créée par une requête concurrente
                    // (code erreur MySQL 1062, doublon sur l'index unique de scope_key) : toute autre
                    // erreur base de données (table manquante, connexion perdue, etc.) doit remonter telle quelle.
                    if (($e->errorInfo[1] ?? null) !== 1062) {
                        throw $e;
                    }
                }
                $counter = SkuCounter::where('scope_key', $scopeKey)->lockForUpdate()->first();
            }

            $value = $counter->next_value;
            $counter->increment('next_value');

            return $value;
        });
    }
}
