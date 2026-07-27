<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PosService;
use App\Services\ThermalPrinterService;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScannerController extends Controller
{
    public function __construct(private readonly PosService $pos) {}

    /**
     * Page principale du scanner (mode POS/Caisse)
     */
    public function index()
    {
        Inertia::setRootView('layouts.admin-inertia');

        $receiptAutoPrint = Setting::get('pos_receipt_auto_print', '0') === '1';

        return Inertia::render('Admin/Scanner/Index', [
            'receiptAutoPrint' => $receiptAutoPrint,
        ]);
    }

    /**
     * Scanner un code et retourner les infos produit
     */
    public function scan(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $result = $this->pos->scan(trim($request->code));

        if (!$result['found']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * Ajouter un produit au panier POS
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'integer|min:1',
        ]);

        $cart = $this->pos->addToCart(
            $request->product_id,
            $request->variant_id,
            $request->quantity ?? 1
        );

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    /**
     * Récupérer le panier POS
     */
    public function getCart()
    {
        return response()->json($this->pos->getCartSummary());
    }

    /**
     * Mettre à jour la quantité d'un article du panier POS
     */
    public function updateCartItem(Request $request, string $key)
    {
        $cart = $this->pos->updateCartItem($key, (int) $request->quantity);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    /**
     * Supprimer un article du panier POS
     */
    public function removeCartItem(string $key)
    {
        $cart = $this->pos->removeCartItem($key);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    /**
     * Vider le panier POS
     */
    public function clearCart()
    {
        $cart = $this->pos->clearCart();

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    /**
     * Finaliser la vente POS
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,mobile_money',
            'amount_received' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        try {
            $result = $this->pos->checkout($request->only('payment_method', 'amount_received', 'customer_id'));

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $result['order']->id,
                    'order_number' => $result['order']->order_number,
                    'total' => $result['order']->total,
                    'total_formatted' => format_price($result['order']->total),
                ],
                'change' => $result['change'],
                'change_formatted' => $result['change_formatted'],
                'receipt_url' => $result['receipt_url'],
            ]);

        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la finalisation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Afficher le reçu d'une vente POS pour impression
     */
    public function receipt(Order $order)
    {
        $order->loadMissing(['items.product']);

        $change = (float) request('change', 0);
        $amountReceived = (float) request('amount_received', $order->total);

        return view('admin.scanner.receipt', compact('order', 'change', 'amountReceived'));
    }

    /**
     * Retourne le reçu au format JSON ESC/POS (pour imprimante thermique)
     */
    public function thermalReceipt(Order $order)
    {
        $order->loadMissing(['items.product']);

        $change = (float) request('change', 0);
        $amountReceived = (float) request('amount_received', $order->total);

        $service = new ThermalPrinterService();
        $receipt = $service->generateReceipt($order, [
            'change'          => $change,
            'amount_received' => $amountReceived,
        ]);

        return response()->json($receipt)
            ->header('Content-Disposition', 'attachment; filename="receipt-' . $order->order_number . '.json"');
    }

    /**
     * Retourne le reçu en texte brut (debug / imprimante série)
     */
    public function textReceipt(Order $order)
    {
        $order->loadMissing(['items.product']);

        $change = (float) request('change', 0);
        $amountReceived = (float) request('amount_received', $order->total);

        $service = new ThermalPrinterService();
        $receipt = $service->generateReceipt($order, [
            'change'          => $change,
            'amount_received' => $amountReceived,
        ]);

        return response($service->toPlainText($receipt), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="receipt-' . $order->order_number . '.txt"');
    }

    /**
     * Envoie le reçu directement à l'imprimante réseau (ESC/POS TCP)
     */
    public function printDirect(Order $order, Request $request)
    {
        $printerEnabled = Setting::get('pos_printer_enabled', '0') === '1';
        if (!$printerEnabled) {
            return response()->json(['success' => false, 'message' => "L'impression directe est désactivée dans les paramètres."]);
        }

        $ip   = Setting::get('pos_printer_ip', '');
        $port = (int) Setting::get('pos_printer_port', 9100);

        if (!$ip) {
            return response()->json(['success' => false, 'message' => "Adresse IP de l'imprimante non configurée."]);
        }

        $order->loadMissing(['items.product']);

        $change         = (float) $request->get('change', 0);
        $amountReceived = (float) $request->get('amount_received', $order->total);

        $service = new ThermalPrinterService();
        $receipt = $service->generateReceipt($order, [
            'change'          => $change,
            'amount_received' => $amountReceived,
        ]);

        config(['pos.printer_port' => $port, 'pos.cash_drawer' => Setting::get('pos_cash_drawer', '0') === '1']);

        $error = $service->printDirectly($receipt, $ip);

        if ($error !== null) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        return response()->json(['success' => true, 'message' => 'Reçu envoyé à l\'imprimante.']);
    }

    /**
     * Mouvement de stock rapide via scanner
     */
    public function stockMovement(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $movement = $this->pos->stockMovement(
            $request->product_id,
            $request->variant_id,
            $request->type,
            $request->quantity,
            $request->notes
        );

        $productId = $request->product_id;
        $variantId = $request->variant_id;

        $newStock = $variantId
            ? $movement->variant?->fresh()->stock_quantity
            : $movement->product?->fresh()->stock_quantity;

        return response()->json([
            'success' => true,
            'movement' => [
                'id' => $movement->id,
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'stock_before' => $movement->stock_before,
                'stock_after' => $movement->stock_after,
            ],
            'new_stock' => $newStock,
        ]);
    }
}
