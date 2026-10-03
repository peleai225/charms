<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Console\Command;

class ImportBulkPricingRules extends Command
{
    protected $signature = 'promotions:import-bulk-rules {--dry-run : Afficher le rapport sans rien écrire}';

    protected $description = 'Convertit les anciennes règles de prix en gros en offres par lot';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode simulation — aucune écriture en base.');
        }

        $created = 0;
        $created += $this->importProductRules($dryRun);
        $created += $this->importCategoryRules($dryRun);

        if ($created === 0) {
            $this->info('Aucune règle à reprendre.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info($dryRun
            ? "{$created} offre(s) seraient créées."
            : "{$created} offre(s) créées, toutes inactives.");

        if (! $dryRun) {
            $this->warn('Relisez chaque offre et son panneau de marge avant de l\'activer.');
        }

        return self::SUCCESS;
    }

    private function importProductRules(bool $dryRun): int
    {
        $products = Product::whereNotNull('bulk_pricing_rules')->get();
        $created = 0;

        foreach ($products as $product) {
            foreach ($this->validRules($product->bulk_pricing_rules) as $rule) {
                $name = "{$rule['min_qty']} × {$product->name}";

                if (Promotion::where('name', $name)->exists()) {
                    continue;
                }

                $lotPrice = $rule['min_qty'] * $rule['unit_price'];

                $this->reportRule($name, (int) $rule['min_qty'], (float) $lotPrice, (float) $product->sale_price);

                if (! $dryRun) {
                    $promotion = Promotion::create([
                        'name' => $name,
                        'lot_qty' => $rule['min_qty'],
                        'lot_price' => $lotPrice,
                        'is_active' => false,
                    ]);

                    $promotion->products()->attach($product->id);
                }

                $created++;
            }
        }

        return $created;
    }

    private function importCategoryRules(bool $dryRun): int
    {
        $categories = Category::whereNotNull('bulk_pricing_rules')->get();
        $created = 0;

        foreach ($categories as $category) {
            // Reproduit le regroupement de l'ancien moteur : catégorie + prix arrondi.
            $bands = Product::where('category_id', $category->id)
                ->where('status', 'active')
                ->get(['sale_price'])
                ->map(fn (Product $p) => (float) round((float) $p->sale_price))
                ->unique()
                ->sort()
                ->values();

            foreach ($this->validRules($category->bulk_pricing_rules) as $rule) {
                foreach ($bands as $band) {
                    $name = "{$rule['min_qty']} × {$category->name} à ".number_format($band, 0, ',', ' ').' F';

                    if (Promotion::where('name', $name)->exists()) {
                        continue;
                    }

                    $lotPrice = $rule['min_qty'] * $rule['unit_price'];

                    $this->reportRule($name, (int) $rule['min_qty'], (float) $lotPrice, $band);

                    if (! $dryRun) {
                        $promotion = Promotion::create([
                            'name' => $name,
                            'lot_qty' => $rule['min_qty'],
                            'lot_price' => $lotPrice,
                            'price_min' => $band,
                            'price_max' => $band + 499,
                            'is_active' => false,
                        ]);

                        $promotion->categories()->attach($category->id);
                    }

                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Comparatif ancien / nouveau aux quantités 1 à 10. C'est le cœur du rapport :
     * la sémantique de lot diffère de celle de palier, et l'exploitant doit voir
     * précisément où les prix changent avant d'activer quoi que ce soit.
     */
    private function reportRule(string $name, int $lotQty, float $lotPrice, float $basePrice): void
    {
        $unitPrice = $lotPrice / $lotQty;

        $this->newLine();
        $this->line("<options=bold>{$name}</>");
        $this->line('Prix de base : '.number_format($basePrice, 0, ',', ' ').' F');

        $rows = [];

        for ($qty = 1; $qty <= 10; $qty++) {
            $before = $qty >= $lotQty ? $qty * $unitPrice : $qty * $basePrice;

            $lots = intdiv($qty, $lotQty);
            $after = $lots * $lotPrice + ($qty - $lots * $lotQty) * $basePrice;

            $rows[] = [
                $qty,
                number_format($before, 0, ',', ' '),
                number_format($after, 0, ',', ' '),
                $before == $after ? '=' : sprintf('%+d', (int) round($after - $before)),
            ];
        }

        $this->table(['Qté', 'Avant (palier)', 'Après (lot)', 'Écart'], $rows);
    }

    /**
     * @return array<int, array{min_qty:int, unit_price:float}>
     */
    private function validRules(mixed $rules): array
    {
        if (! is_array($rules)) {
            return [];
        }

        return collect($rules)
            ->filter(fn ($rule) => isset($rule['min_qty'], $rule['unit_price']) && (int) $rule['min_qty'] >= 2)
            ->map(fn ($rule) => [
                'min_qty' => (int) $rule['min_qty'],
                'unit_price' => (float) $rule['unit_price'],
            ])
            ->values()
            ->all();
    }
}
