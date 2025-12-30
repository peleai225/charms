<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Couleur',
                'slug' => 'couleur',
                'type' => 'color',
                'is_filterable' => true,
                'order' => 1,
                'values' => [
                    ['value' => 'Noir', 'slug' => 'noir', 'color_code' => '#000000', 'order' => 1],
                    ['value' => 'Blanc', 'slug' => 'blanc', 'color_code' => '#FFFFFF', 'order' => 2],
                    ['value' => 'Rouge', 'slug' => 'rouge', 'color_code' => '#EF4444', 'order' => 3],
                    ['value' => 'Bleu', 'slug' => 'bleu', 'color_code' => '#3B82F6', 'order' => 4],
                    ['value' => 'Vert', 'slug' => 'vert', 'color_code' => '#10B981', 'order' => 5],
                    ['value' => 'Jaune', 'slug' => 'jaune', 'color_code' => '#F59E0B', 'order' => 6],
                    ['value' => 'Rose', 'slug' => 'rose', 'color_code' => '#EC4899', 'order' => 7],
                    ['value' => 'Violet', 'slug' => 'violet', 'color_code' => '#8B5CF6', 'order' => 8],
                    ['value' => 'Gris', 'slug' => 'gris', 'color_code' => '#6B7280', 'order' => 9],
                    ['value' => 'Beige', 'slug' => 'beige', 'color_code' => '#D4A574', 'order' => 10],
                ],
            ],
            [
                'name' => 'Taille',
                'slug' => 'taille',
                'type' => 'select',
                'is_filterable' => true,
                'order' => 2,
                'values' => [
                    ['value' => 'XS', 'slug' => 'xs', 'order' => 1],
                    ['value' => 'S', 'slug' => 's', 'order' => 2],
                    ['value' => 'M', 'slug' => 'm', 'order' => 3],
                    ['value' => 'L', 'slug' => 'l', 'order' => 4],
                    ['value' => 'XL', 'slug' => 'xl', 'order' => 5],
                    ['value' => 'XXL', 'slug' => 'xxl', 'order' => 6],
                ],
            ],
            [
                'name' => 'Pointure',
                'slug' => 'pointure',
                'type' => 'select',
                'is_filterable' => true,
                'order' => 3,
                'values' => [
                    ['value' => '36', 'slug' => '36', 'order' => 1],
                    ['value' => '37', 'slug' => '37', 'order' => 2],
                    ['value' => '38', 'slug' => '38', 'order' => 3],
                    ['value' => '39', 'slug' => '39', 'order' => 4],
                    ['value' => '40', 'slug' => '40', 'order' => 5],
                    ['value' => '41', 'slug' => '41', 'order' => 6],
                    ['value' => '42', 'slug' => '42', 'order' => 7],
                    ['value' => '43', 'slug' => '43', 'order' => 8],
                    ['value' => '44', 'slug' => '44', 'order' => 9],
                    ['value' => '45', 'slug' => '45', 'order' => 10],
                    ['value' => '46', 'slug' => '46', 'order' => 11],
                ],
            ],
            [
                'name' => 'Capacité',
                'slug' => 'capacite',
                'type' => 'select',
                'is_filterable' => true,
                'order' => 4,
                'values' => [
                    ['value' => '64 Go', 'slug' => '64go', 'order' => 1],
                    ['value' => '128 Go', 'slug' => '128go', 'order' => 2],
                    ['value' => '256 Go', 'slug' => '256go', 'order' => 3],
                    ['value' => '512 Go', 'slug' => '512go', 'order' => 4],
                    ['value' => '1 To', 'slug' => '1to', 'order' => 5],
                    ['value' => '2 To', 'slug' => '2to', 'order' => 6],
                ],
            ],
            [
                'name' => 'Matière',
                'slug' => 'matiere',
                'type' => 'select',
                'is_filterable' => true,
                'order' => 5,
                'values' => [
                    ['value' => 'Coton', 'slug' => 'coton', 'order' => 1],
                    ['value' => 'Lin', 'slug' => 'lin', 'order' => 2],
                    ['value' => 'Laine', 'slug' => 'laine', 'order' => 3],
                    ['value' => 'Soie', 'slug' => 'soie', 'order' => 4],
                    ['value' => 'Polyester', 'slug' => 'polyester', 'order' => 5],
                    ['value' => 'Cuir', 'slug' => 'cuir', 'order' => 6],
                    ['value' => 'Daim', 'slug' => 'daim', 'order' => 7],
                ],
            ],
        ];

        foreach ($attributes as $attributeData) {
            $values = $attributeData['values'] ?? [];
            unset($attributeData['values']);

            $attribute = Attribute::create($attributeData);

            foreach ($values as $valueData) {
                $valueData['attribute_id'] = $attribute->id;
                AttributeValue::create($valueData);
            }
        }
    }
}

