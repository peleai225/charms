<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Crée un produit à variantes à partir de plusieurs photos d'un même article :
 * une couleur par photo, éventuellement croisée avec une déclinaison (taille, pointure…).
 */
class VariantProductFromPhotos
{
    /**
     * @param  array<string, mixed>  $data  données validées par le contrôleur
     * @param  array<int, UploadedFile>  $images
     */
    public function create(array $data, array $images): Product
    {
        $images = array_values($images);
        $colorAttribute = Attribute::colorAttribute();
        $optionAttribute = ! empty($data['option_attribute_id']) ? Attribute::find($data['option_attribute_id']) : null;

        $colors = $this->readColors($data['colors'] ?? [], count($images), $colorAttribute);
        $variants = $this->checkVariants($data['variants'], $colors, count($images), $optionAttribute, $colorAttribute);

        $product = DB::transaction(function () use ($data, $images, $colors, $variants, $colorAttribute, $optionAttribute) {
            $product = Product::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'sku' => $this->uniqueProductSku(),
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'sale_price' => $data['sale_price'],
                'purchase_price' => $data['purchase_price'] ?? 0,
                'tax_rate' => 0,
                'stock_quantity' => array_sum(array_column($variants, 'stock')),
                'stock_alert_threshold' => 5,
                'track_stock' => true,
                'has_variants' => true,
                'status' => $data['status'],
            ]);

            $paths = [];
            foreach ($images as $i => $file) {
                $paths[$i] = $file->store('products/'.$product->id, 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $paths[$i],
                    'is_primary' => $i === 0,
                    'position' => $i,
                ]);
            }

            $colorValueIds = $this->colorValueIds($colors, $colorAttribute);
            $colorAttributeId = $colorAttribute?->id ?? Attribute::colorAttribute()?->id;
            $labels = AttributeValue::whereIn('id', array_merge(
                array_values(array_filter($colorValueIds)),
                array_values(array_filter(array_column($variants, 'value_id')))
            ))->pluck('value', 'id');

            foreach ($variants as $position => $v) {
                $pairs = [];
                if ($v['color_index'] !== null) {
                    $pairs[$colorAttributeId] = $colorValueIds[$v['color_index']];
                }
                if ($v['value_id'] !== null) {
                    $pairs[$optionAttribute->id] = $v['value_id'];
                }

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $this->uniqueVariantSku($product->sku, array_map(fn ($id) => $labels[$id] ?? (string) $id, $pairs)),
                    'stock_quantity' => $v['stock'],
                    'image' => $v['color_index'] !== null ? $paths[$v['color_index']] : null,
                    'is_active' => true,
                    'position' => $position,
                ]);

                foreach ($pairs as $attributeId => $valueId) {
                    DB::table('product_variant_values')->insert([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $attributeId,
                        'attribute_value_id' => $valueId,
                    ]);
                }

                $variant->generateName();
            }

            return $product;
        });

        ActivityLog::logCreated($product, "Produit {$product->name} créé par photos avec ".count($variants).' variante(s) (IA)');

        return $product;
    }

    /**
     * Pour chaque photo : couleur existante (id), nouvelle couleur (name/hex), ou aucune.
     * Un nom qui correspond à une couleur existante la réutilise.
     *
     * @return array<int, array{id: ?int, name: ?string, hex: ?string, key: ?string}>
     */
    protected function readColors(array $input, int $imageCount, ?Attribute $colorAttribute): array
    {
        $existing = $colorAttribute ? $colorAttribute->values()->get(['id', 'value', 'slug']) : collect();
        $errors = [];
        $colors = [];

        for ($i = 0; $i < $imageCount; $i++) {
            $id = isset($input[$i]['id']) ? (int) $input[$i]['id'] : null;
            $name = trim((string) ($input[$i]['name'] ?? ''));
            $hex = $input[$i]['hex'] ?? null;

            if ($id) {
                if (! $existing->contains('id', $id)) {
                    $errors["colors.{$i}.id"] = "Cette couleur n'existe pas.";
                }
                $colors[$i] = ['id' => $id, 'name' => null, 'hex' => null, 'key' => "id:{$id}"];
            } elseif ($name !== '') {
                $slug = Str::slug($name);
                $match = $existing->first(fn ($v) => $v->slug === $slug || mb_strtolower($v->value) === mb_strtolower($name));
                $colors[$i] = $match
                    ? ['id' => $match->id, 'name' => null, 'hex' => null, 'key' => "id:{$match->id}"]
                    : ['id' => null, 'name' => Str::ucfirst($name), 'hex' => $hex ? strtoupper($hex) : null, 'key' => "new:{$slug}"];
            } else {
                $colors[$i] = ['id' => null, 'name' => null, 'hex' => null, 'key' => null];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $colors;
    }

    /**
     * @return array<int, array{color_index: ?int, value_id: ?int, stock: int}>
     */
    protected function checkVariants(array $input, array $colors, int $imageCount, ?Attribute $optionAttribute, ?Attribute $colorAttribute): array
    {
        $errors = [];
        $seen = [];
        $variants = [];

        if ($optionAttribute && $colorAttribute && $optionAttribute->id === $colorAttribute->id) {
            $errors['option_attribute_id'] = 'Choisissez une déclinaison autre que la couleur.';
        }

        $optionValueIds = $optionAttribute ? $optionAttribute->values()->pluck('id')->all() : [];

        foreach (array_values($input) as $i => $row) {
            $colorIndex = isset($row['color_index']) && $row['color_index'] !== '' ? (int) $row['color_index'] : null;
            $valueId = isset($row['value_id']) && $row['value_id'] !== '' ? (int) $row['value_id'] : null;

            if ($colorIndex === null && $valueId === null) {
                $errors["variants.{$i}"] = 'Chaque variante doit avoir une couleur ou une taille.';

                continue;
            }

            if ($colorIndex !== null) {
                if ($colorIndex >= $imageCount) {
                    $errors["variants.{$i}.color_index"] = 'Photo introuvable pour cette variante.';
                } elseif ($colors[$colorIndex]['key'] === null) {
                    $errors["colors.{$colorIndex}"] = 'Choisissez la couleur de cette photo.';
                }
            }

            if ($valueId !== null) {
                if (! $optionAttribute) {
                    $errors['option_attribute_id'] = 'Choisissez la déclinaison (taille, pointure…).';
                } elseif (! in_array($valueId, $optionValueIds, true)) {
                    $errors["variants.{$i}.value_id"] = 'Cette valeur ne correspond pas à la déclinaison choisie.';
                }
            }

            $combo = ($colorIndex !== null ? ($colors[$colorIndex]['key'] ?? "photo:{$colorIndex}") : '-').'|'.($valueId ?? '-');
            if (isset($seen[$combo])) {
                $errors['variants'] = 'Deux variantes sont identiques (même couleur et même taille).';
            }
            $seen[$combo] = true;

            $variants[] = ['color_index' => $colorIndex, 'value_id' => $valueId, 'stock' => (int) $row['stock']];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $variants;
    }

    /**
     * Crée les nouvelles couleurs (une seule fois chacune) et renvoie l'id de couleur de chaque photo.
     *
     * @return array<int, ?int>
     */
    protected function colorValueIds(array $colors, ?Attribute $colorAttribute): array
    {
        $created = [];
        $ids = [];

        foreach ($colors as $i => $color) {
            if ($color['id']) {
                $ids[$i] = $color['id'];
            } elseif ($color['name']) {
                $colorAttribute ??= Attribute::create(['name' => 'Couleur', 'slug' => 'couleur', 'type' => 'color', 'is_filterable' => true, 'is_visible' => true]);

                $created[$color['key']] ??= AttributeValue::create([
                    'attribute_id' => $colorAttribute->id,
                    'value' => $color['name'],
                    'color_code' => $color['hex'],
                    'order' => (int) $colorAttribute->values()->max('order') + 1,
                ])->id;
                $ids[$i] = $created[$color['key']];
            } else {
                $ids[$i] = null;
            }
        }

        return $ids;
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'produit';
        $slug = $base;
        $i = 1;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected function uniqueProductSku(): string
    {
        do {
            $sku = 'PRD-'.strtoupper(Str::random(8));
        } while (Product::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * @param  array<int|string, string>  $labels
     */
    protected function uniqueVariantSku(string $productSku, array $labels): string
    {
        $suffix = collect($labels)
            ->map(fn ($label) => mb_substr(strtoupper(Str::slug($label, '')), 0, 6) ?: 'X')
            ->implode('-');
        $base = $productSku.'-'.$suffix;
        $sku = $base;
        $i = 2;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $base.'-'.$i++;
        }

        return $sku;
    }
}
