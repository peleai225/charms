<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Électronique',
                'slug' => 'electronique',
                'description' => 'Smartphones, ordinateurs, accessoires et plus',
                'is_featured' => true,
                'order' => 1,
                'children' => [
                    ['name' => 'Smartphones', 'slug' => 'smartphones', 'order' => 1],
                    ['name' => 'Ordinateurs', 'slug' => 'ordinateurs', 'order' => 2],
                    ['name' => 'Tablettes', 'slug' => 'tablettes', 'order' => 3],
                    ['name' => 'Accessoires', 'slug' => 'accessoires-electronique', 'order' => 4],
                    ['name' => 'Audio', 'slug' => 'audio', 'order' => 5],
                    ['name' => 'Photo & Vidéo', 'slug' => 'photo-video', 'order' => 6],
                ],
            ],
            [
                'name' => 'Mode',
                'slug' => 'mode',
                'description' => 'Vêtements, chaussures et accessoires',
                'is_featured' => true,
                'order' => 2,
                'children' => [
                    ['name' => 'Homme', 'slug' => 'mode-homme', 'order' => 1],
                    ['name' => 'Femme', 'slug' => 'mode-femme', 'order' => 2],
                    ['name' => 'Enfant', 'slug' => 'mode-enfant', 'order' => 3],
                    ['name' => 'Chaussures', 'slug' => 'chaussures', 'order' => 4],
                    ['name' => 'Accessoires', 'slug' => 'accessoires-mode', 'order' => 5],
                ],
            ],
            [
                'name' => 'Maison & Jardin',
                'slug' => 'maison-jardin',
                'description' => 'Décoration, mobilier et jardinage',
                'is_featured' => true,
                'order' => 3,
                'children' => [
                    ['name' => 'Décoration', 'slug' => 'decoration', 'order' => 1],
                    ['name' => 'Mobilier', 'slug' => 'mobilier', 'order' => 2],
                    ['name' => 'Cuisine', 'slug' => 'cuisine', 'order' => 3],
                    ['name' => 'Jardin', 'slug' => 'jardin', 'order' => 4],
                    ['name' => 'Bricolage', 'slug' => 'bricolage', 'order' => 5],
                ],
            ],
            [
                'name' => 'Sports & Loisirs',
                'slug' => 'sports-loisirs',
                'description' => 'Équipements sportifs et activités de loisirs',
                'is_featured' => true,
                'order' => 4,
                'children' => [
                    ['name' => 'Fitness', 'slug' => 'fitness', 'order' => 1],
                    ['name' => 'Running', 'slug' => 'running', 'order' => 2],
                    ['name' => 'Sports collectifs', 'slug' => 'sports-collectifs', 'order' => 3],
                    ['name' => 'Outdoor', 'slug' => 'outdoor', 'order' => 4],
                    ['name' => 'Gaming', 'slug' => 'gaming', 'order' => 5],
                ],
            ],
            [
                'name' => 'Beauté & Santé',
                'slug' => 'beaute-sante',
                'description' => 'Cosmétiques, soins et bien-être',
                'is_featured' => true,
                'order' => 5,
                'children' => [
                    ['name' => 'Soins visage', 'slug' => 'soins-visage', 'order' => 1],
                    ['name' => 'Soins corps', 'slug' => 'soins-corps', 'order' => 2],
                    ['name' => 'Maquillage', 'slug' => 'maquillage', 'order' => 3],
                    ['name' => 'Parfums', 'slug' => 'parfums', 'order' => 4],
                    ['name' => 'Bien-être', 'slug' => 'bien-etre', 'order' => 5],
                ],
            ],
            [
                'name' => 'Alimentation',
                'slug' => 'alimentation',
                'description' => 'Épicerie fine et produits gourmands',
                'is_featured' => false,
                'order' => 6,
                'children' => [
                    ['name' => 'Épicerie', 'slug' => 'epicerie', 'order' => 1],
                    ['name' => 'Boissons', 'slug' => 'boissons', 'order' => 2],
                    ['name' => 'Bio', 'slug' => 'bio', 'order' => 3],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = Category::create($categoryData);

            foreach ($children as $childData) {
                $childData['parent_id'] = $category->id;
                Category::create($childData);
            }
        }
    }
}

