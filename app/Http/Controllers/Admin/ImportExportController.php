<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportExportController extends Controller
{
    /**
     * Page d'import/export
     */
    public function index()
    {
        $stats = [
            'products_count' => Product::count(),
            'categories_count' => Category::count(),
        ];

        return view('admin.import-export.index', compact('stats'));
    }

    /**
     * Export des produits en CSV
     */
    public function exportProducts(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $products = Product::with(['category', 'images'])
            ->orderBy('id')
            ->get();

        $filename = 'produits_export_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // En-têtes
            fputcsv($file, [
                'ID',
                'SKU',
                'Nom',
                'Slug',
                'Description courte',
                'Description',
                'Catégorie',
                'Prix de vente',
                'Prix barré',
                'Prix d\'achat',
                'Stock',
                'Seuil alerte stock',
                'Poids (g)',
                'Statut',
                'En vedette',
                'Nouveau',
                'Image principale',
            ], ';');

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->sku,
                    $product->name,
                    $product->slug,
                    $product->short_description,
                    strip_tags($product->description),
                    $product->category?->name,
                    $product->sale_price,
                    $product->compare_price,
                    $product->cost_price,
                    $product->stock_quantity,
                    $product->stock_alert_threshold,
                    $product->weight,
                    $product->status,
                    $product->is_featured ? 'Oui' : 'Non',
                    $product->is_new ? 'Oui' : 'Non',
                    $product->primary_image_url,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Template d'import
     */
    public function downloadTemplate()
    {
        $filename = 'modele_import_produits.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // En-têtes
            fputcsv($file, [
                'sku',
                'name',
                'short_description',
                'description',
                'category_name',
                'sale_price',
                'compare_price',
                'cost_price',
                'stock_quantity',
                'stock_alert_threshold',
                'weight',
                'status',
                'is_featured',
                'is_new',
            ], ';');

            // Ligne exemple
            fputcsv($file, [
                'PROD-001',
                'Nom du produit',
                'Description courte du produit',
                'Description longue du produit',
                'Mode',
                '15000',
                '20000',
                '8000',
                '100',
                '10',
                '500',
                'active',
                '0',
                '1',
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import des produits
     */
    public function importProducts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'update_existing' => 'boolean',
        ]);

        $file = $request->file('file');
        $updateExisting = $request->boolean('update_existing', false);
        
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        try {
            $handle = fopen($file->getPathname(), 'r');
            
            // Ignorer le BOM UTF-8 si présent
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                rewind($handle);
            }
            
            // Lire les en-têtes
            $headers = fgetcsv($handle, 0, ';');
            if (!$headers) {
                throw new \Exception('Fichier vide ou format invalide.');
            }

            // Normaliser les en-têtes
            $headers = array_map(fn($h) => trim(strtolower($h)), $headers);
            
            $rowNumber = 1;
            
            DB::beginTransaction();

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $rowNumber++;
                
                if (count($row) < count($headers)) {
                    $results['errors'][] = "Ligne {$rowNumber}: Nombre de colonnes incorrect.";
                    continue;
                }

                $data = array_combine($headers, $row);
                
                try {
                    $this->processProductRow($data, $updateExisting, $results, $rowNumber);
                } catch (\Exception $e) {
                    $results['errors'][] = "Ligne {$rowNumber}: " . $e->getMessage();
                }
            }

            fclose($handle);
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }

        $message = "Import terminé: {$results['created']} créé(s), {$results['updated']} mis à jour.";
        if (count($results['errors']) > 0) {
            $message .= ' ' . count($results['errors']) . ' erreur(s).';
        }

        return back()
            ->with('success', $message)
            ->with('import_errors', $results['errors']);
    }

    /**
     * Traiter une ligne de produit
     */
    protected function processProductRow(array $data, bool $updateExisting, array &$results, int $rowNumber): void
    {
        $name = trim($data['name'] ?? '');
        $sku = trim($data['sku'] ?? '');

        if (empty($name)) {
            throw new \Exception('Le nom est obligatoire.');
        }

        // Chercher le produit existant
        $product = null;
        if (!empty($sku)) {
            $product = Product::where('sku', $sku)->first();
        }

        if ($product && !$updateExisting) {
            $results['errors'][] = "Ligne {$rowNumber}: SKU '{$sku}' existe déjà.";
            return;
        }

        // Trouver ou créer la catégorie
        $categoryId = null;
        $categoryName = trim($data['category_name'] ?? '');
        if (!empty($categoryName)) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName), 'is_active' => true]
            );
            $categoryId = $category->id;
        }

        // Préparer les données du produit
        $productData = [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'sku' => $sku ?: null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'category_id' => $categoryId,
            'sale_price' => floatval($data['sale_price'] ?? 0),
            'compare_price' => !empty($data['compare_price']) ? floatval($data['compare_price']) : null,
            'cost_price' => !empty($data['cost_price']) ? floatval($data['cost_price']) : null,
            'stock_quantity' => intval($data['stock_quantity'] ?? 0),
            'stock_alert_threshold' => intval($data['stock_alert_threshold'] ?? 5),
            'weight' => !empty($data['weight']) ? floatval($data['weight']) : null,
            'status' => in_array($data['status'] ?? '', ['active', 'draft', 'archived']) ? $data['status'] : 'draft',
            'is_featured' => in_array(strtolower($data['is_featured'] ?? ''), ['1', 'oui', 'yes', 'true']),
            'is_new' => in_array(strtolower($data['is_new'] ?? ''), ['1', 'oui', 'yes', 'true']),
        ];

        if ($product) {
            // Mise à jour
            unset($productData['slug']); // Ne pas modifier le slug
            $product->update($productData);
            $results['updated']++;
        } else {
            // Création
            Product::create($productData);
            $results['created']++;
        }
    }

    /**
     * Export des catégories
     */
    public function exportCategories()
    {
        $categories = Category::with('parent')->orderBy('id')->get();

        $filename = 'categories_export_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($categories) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($file, ['ID', 'Nom', 'Slug', 'Parent', 'Description', 'Actif', 'Ordre'], ';');

            foreach ($categories as $category) {
                fputcsv($file, [
                    $category->id,
                    $category->name,
                    $category->slug,
                    $category->parent?->name,
                    $category->description,
                    $category->is_active ? 'Oui' : 'Non',
                    $category->order,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

