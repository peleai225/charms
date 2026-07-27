<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JekoAfricaService
{
    protected string $apiBase = 'https://api.jeko.africa/partner_api';
    protected string $apiKey;
    protected string $apiKeyId;
    protected string $storeId;
    protected string $webhookSecret;

    public function __construct()
    {
        $this->apiKey        = \App\Models\Setting::get('jeko_api_key', '');
        $this->apiKeyId      = \App\Models\Setting::get('jeko_api_key_id', '');
        $this->storeId       = \App\Models\Setting::get('jeko_store_id', '');
        $this->webhookSecret = \App\Models\Setting::get('jeko_webhook_secret', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiKeyId) && !empty($this->storeId);
    }

    /**
     * Créer un lien de paiement Jeko et rediriger le client
     * Retourne ['success', 'payment_url', 'link_id'] ou ['success' => false, 'message']
     */
    public function initializePayment(Order $order): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Jeko Africa n\'est pas configuré. Renseignez les clés API dans les paramètres.',
            ];
        }

        // Jeko travaille en centimes : 1 XOF = 100 centimes
        $amountCents = (int) round($order->total * 100);

        $body = [
            'storeId'               => $this->storeId,
            'title'                 => 'Commande ' . $order->order_number,
            'amountCents'           => $amountCents,
            'currency'              => $order->currency ?? 'XOF',
            'allowMultiplePayments' => false,
        ];

        Log::info('Jeko: Creating payment link', [
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'amountCents'  => $amountCents,
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY'    => $this->apiKey,
                    'X-API-KEY-ID' => $this->apiKeyId,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->post("{$this->apiBase}/payment_links", $body);

            $result = $response->json();

            Log::info('Jeko: API response', [
                'status' => $response->status(),
                'body'   => $result,
            ]);

            if ($response->successful() && !empty($result['id']) && !empty($result['link'])) {
                $linkId     = $result['id'];
                $paymentUrl = $result['link'];

                Payment::create([
                    'order_id'               => $order->id,
                    'transaction_id'         => $linkId,
                    'method'                 => 'jeko',
                    'gateway'                => 'jeko',
                    'gateway_transaction_id' => $linkId,
                    'amount'                 => $order->total,
                    'currency'               => $order->currency ?? 'XOF',
                    'status'                 => 'pending',
                    'gateway_response'       => $result,
                ]);

                $order->update([
                    'payment_method' => 'jeko',
                    'payment_status' => 'pending',
                ]);

                return [
                    'success'     => true,
                    'payment_url' => $paymentUrl,
                    'link_id'     => $linkId,
                ];
            }

            $message = $result['message'] ?? $result['error'] ?? 'Erreur Jeko Africa lors de la création du lien.';
            Log::error('Jeko: Payment link creation failed', ['order_id' => $order->id, 'response' => $result]);

            return ['success' => false, 'message' => $message];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Jeko: Connection error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Impossible de joindre Jeko Africa. Vérifiez votre connexion.'];
        } catch (\Exception $e) {
            Log::error('Jeko: Exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur Jeko Africa : ' . $e->getMessage()];
        }
    }

    /**
     * Vérifier si un lien de paiement a été payé
     * canReceivePayments: false = payé (lien usage unique désactivé après paiement)
     */
    public function checkPaymentStatus(string $linkId): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-API-KEY'    => $this->apiKey,
                    'X-API-KEY-ID' => $this->apiKeyId,
                    'Accept'       => 'application/json',
                ])
                ->get("{$this->apiBase}/payment_links/{$linkId}");

            $result = $response->json();

            if ($response->successful() && isset($result['canReceivePayments'])) {
                // Lien usage unique : false = paiement reçu
                $paid = $result['canReceivePayments'] === false;

                return [
                    'success'              => true,
                    'status'               => $paid ? 'paid' : 'pending',
                    'canReceivePayments'   => $result['canReceivePayments'],
                    'data'                 => $result,
                ];
            }

            return [
                'success' => false,
                'status'  => 'unknown',
                'message' => $result['message'] ?? 'Impossible de vérifier le statut du lien.',
            ];

        } catch (\Exception $e) {
            Log::error('Jeko: Check status error', ['link_id' => $linkId, 'error' => $e->getMessage()]);
            return ['success' => false, 'status' => 'error', 'message' => 'Erreur de vérification.'];
        }
    }

    /**
     * Traiter un webhook Jeko — event: transaction.completed
     * Vérifie la signature HMAC-SHA256 avant tout traitement
     */
    public function handleWebhook(string $rawBody, string $signature, array $data): bool
    {
        // Vérifier la signature si un secret est configuré
        if (!empty($this->webhookSecret)) {
            $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);
            if (!hash_equals($expected, $signature)) {
                Log::error('Jeko webhook: invalid signature');
                return false;
            }
        }

        $event   = $data['transactionType'] ?? null;
        $status  = $data['status'] ?? null;
        $linkId  = $data['transactionDetails']['paymentLinkId'] ?? null;

        Log::info('Jeko webhook received', [
            'event'   => $event,
            'status'  => $status,
            'link_id' => $linkId,
            'amount'  => $data['amount']['amount'] ?? null,
        ]);

        if (!$linkId) {
            Log::error('Jeko webhook: missing paymentLinkId', $data);
            return false;
        }

        $payment = Payment::where('gateway', 'jeko')
            ->where(function ($q) use ($linkId) {
                $q->where('transaction_id', $linkId)
                  ->orWhere('gateway_transaction_id', $linkId);
            })
            ->first();

        if (!$payment) {
            Log::error('Jeko webhook: payment not found', ['link_id' => $linkId]);
            return false;
        }

        // Idempotence
        if ($payment->status === 'completed') {
            return true;
        }

        $order = $payment->order;

        if ($status === 'success') {
            // Vérifier le montant (Jeko envoie en centimes)
            $receivedCents = (int) ($data['amount']['amount'] ?? 0);
            $expectedCents = (int) round($order->total * 100);

            if ($receivedCents > 0 && abs($receivedCents - $expectedCents) > 100) {
                Log::error('Jeko: amount mismatch', [
                    'order'    => $order->order_number,
                    'expected' => $expectedCents,
                    'received' => $receivedCents,
                ]);

                $payment->update([
                    'status' => 'failed',
                    'notes'  => "Montant incorrect: attendu {$expectedCents} centimes, reçu {$receivedCents}",
                ]);

                return false;
            }

            $payment->update([
                'status'           => 'completed',
                'paid_at'          => now(),
                'gateway_response' => array_merge(
                    is_array($payment->gateway_response) ? $payment->gateway_response : [],
                    ['webhook' => $data]
                ),
            ]);

            $order->update([
                'payment_status' => 'paid',
                'status'         => 'processing',
                'paid_at'        => now(),
            ]);

            event(new \App\Events\OrderPaid($order, $payment));

            Log::info('Jeko: payment completed', ['order' => $order->order_number]);
            return true;
        }

        Log::info('Jeko webhook: unhandled status', ['status' => $status, 'event' => $event]);
        return true;
    }

    /**
     * Test de connexion — vérifie que les clés fonctionnent
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Jeko non configuré (clés API ou Store ID manquants).'];
        }

        try {
            // On tente de lire un lien inexistant — une 404 authentifiée prouve que les clés sont valides
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-KEY'    => $this->apiKey,
                    'X-API-KEY-ID' => $this->apiKeyId,
                    'Accept'       => 'application/json',
                ])
                ->get("{$this->apiBase}/payment_links/test-connection-ping");

            // 401/403 = mauvaises clés. Autre code = clés OK (même 404)
            if (in_array($response->status(), [401, 403])) {
                return ['success' => false, 'message' => 'Clés API invalides (HTTP ' . $response->status() . ').'];
            }

            return ['success' => true, 'message' => 'Connexion à Jeko Africa réussie (HTTP ' . $response->status() . ').'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Impossible de joindre Jeko Africa : ' . $e->getMessage()];
        }
    }
}
