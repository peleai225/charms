<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeController extends Controller
{
    /**
     * Page principale codes-barres
     */
    public function index(Request $request)
    {
        $query = Product::active()->with('variants');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(30)->withQueryString();

        return view('admin.barcodes.index', compact('products'));
    }

    /**
     * Générer un code-barres pour un produit
     */
    public function generateBarcode(Product $product)
    {
        // Générer un code EAN-13 si non existant
        if (empty($product->barcode)) {
            $product->barcode = $this->generateEAN13();
            $product->save();
        }

        return response()->json([
            'barcode' => $product->barcode,
            'barcode_svg' => $this->getBarcodeImage($product->barcode, 'svg'),
        ]);
    }

    /**
     * Générer un QR code pour un produit
     */
    public function generateQrCode(Product $product)
    {
        $url = route('shop.product', $product->slug);
        
        // Générer le QR code en SVG
        $qrCodeSvg = QrCode::size(200)
            ->format('svg')
            ->generate($url);
        
        // Convertir en base64 pour affichage inline
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);

        return response()->json([
            'success' => true,
            'qr_code' => $qrCodeBase64,
            'qr_url' => $url,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->sale_price,
            ],
        ]);
    }

    /**
     * Afficher le QR code en image
     */
    public function showQrCode(Product $product)
    {
        $url = route('shop.product', $product->slug);
        
        return response(QrCode::size(300)->format('svg')->generate($url))
            ->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Page d'impression d'étiquettes
     */
    public function printLabels(Request $request)
    {
        $productIds = $request->get('products', []);
        
        // Gérer le cas où products est une chaîne (paramètre URL)
        if (is_string($productIds)) {
            $productIds = array_filter(explode(',', $productIds));
        }
        
        $labelFormat = $request->get('format', 'standard'); // standard, small, price_tag
        $quantity = (int) $request->get('quantity', 1);

        // Si aucun produit sélectionné, rediriger vers la liste
        if (empty($productIds)) {
            return redirect()->route('admin.barcodes.index')
                ->with('info', 'Veuillez sélectionner des produits à imprimer.');
        }

        $products = Product::whereIn('id', $productIds)->get();

        // Générer les codes-barres pour chaque produit
        foreach ($products as $product) {
            if (empty($product->barcode)) {
                $product->barcode = $this->generateEAN13();
                $product->save();
            }
            $product->barcode_svg = $this->getBarcodeImage($product->barcode, 'svg');
        }

        return view('admin.barcodes.print-labels', compact('products', 'labelFormat', 'quantity'));
    }

    /**
     * Scanner de codes-barres (API)
     */
    public function scan(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->code;

        // Chercher dans les produits
        $product = Product::where('barcode', $code)
            ->orWhere('sku', $code)
            ->first();

        if ($product) {
            return response()->json([
                'found' => true,
                'type' => 'product',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->sale_price,
                    'stock' => $product->stock_quantity,
                    'image' => $product->primary_image_url,
                ],
            ]);
        }

        // Chercher dans les variantes
        $variant = ProductVariant::where('sku', $code)->with('product')->first();

        if ($variant) {
            return response()->json([
                'found' => true,
                'type' => 'variant',
                'data' => [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => $variant->product->name,
                    'sku' => $variant->sku,
                    'price' => $variant->sale_price ?? $variant->product->sale_price,
                    'stock' => $variant->stock_quantity,
                ],
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Produit non trouvé',
        ], 404);
    }

    /**
     * Générer une image de code-barres
     */
    protected function getBarcodeImage(string $barcode, string $format = 'png'): string
    {
        try {
            if ($format === 'svg') {
                $generator = new BarcodeGeneratorSVG();
                return 'data:image/svg+xml;base64,' . base64_encode($generator->getBarcode($barcode, $generator::TYPE_EAN_13, 2, 60));
            } else {
                $generator = new BarcodeGeneratorPNG();
                return 'data:image/png;base64,' . base64_encode($generator->getBarcode($barcode, $generator::TYPE_EAN_13, 2, 60));
            }
        } catch (\Exception $e) {
            // Fallback avec code 128 si EAN-13 échoue
            try {
                if ($format === 'svg') {
                    $generator = new BarcodeGeneratorSVG();
                    return 'data:image/svg+xml;base64,' . base64_encode($generator->getBarcode($barcode, $generator::TYPE_CODE_128, 2, 60));
                } else {
                    $generator = new BarcodeGeneratorPNG();
                    return 'data:image/png;base64,' . base64_encode($generator->getBarcode($barcode, $generator::TYPE_CODE_128, 2, 60));
                }
            } catch (\Exception $e2) {
                return '';
            }
        }
    }

    /**
     * Générer un code EAN-13 unique
     */
    protected function generateEAN13(): string
    {
        $prefix = '200'; // Préfixe pour codes internes
        $number = str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        $code = $prefix . $number;

        // Calculer le chiffre de contrôle
        $checkDigit = $this->calculateEAN13CheckDigit($code);

        $ean13 = $code . $checkDigit;

        // Vérifier unicité
        if (Product::where('barcode', $ean13)->exists()) {
            return $this->generateEAN13();
        }

        return $ean13;
    }

    /**
     * Calculer le chiffre de contrôle EAN-13
     */
    protected function calculateEAN13CheckDigit(string $code): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Mise à jour en masse des codes-barres
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $count = 0;
        foreach ($request->product_ids as $id) {
            $product = Product::find($id);
            if ($product && empty($product->barcode)) {
                $product->barcode = $this->generateEAN13();
                $product->save();
                $count++;
            }
        }

        return back()->with('success', "{$count} code(s)-barres généré(s).");
    }
}

