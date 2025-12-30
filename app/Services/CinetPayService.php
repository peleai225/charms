<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CinetPayService
{
    protected string $siteId;
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;
    protected string $currency;
    protected bool $sandbox;

    public function __construct()
    {
        $this->siteId = config('cinetpay.site_id');
        $this->apiKey = config('cinetpay.api_key');
        $this->secretKey = config('cinetpay.secret_key');
        $this->baseUrl = config('cinetpay.api_base_url');
        $this->currency = config('cinetpay.currency');
        $this->sandbox = config('cinetpay.sandbox');
    }

    /**
     * Initialiser un paiement CinetPay
     */
    public function initializePayment(Order $order, array $customerData = []): array
    {
        $transactionId = $this->generateTransactionId($order);

        $payload = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => (int) $order->total,
            'currency' => $this->currency,
            'alternative_currency' => 'EUR',
            'description' => "Commande #{$order->order_number}",
            'customer_id' => $order->customer_id ?? $order->id,
            'customer_name' => $customerData['name'] ?? $order->billing_first_name,
            'customer_surname' => $customerData['surname'] ?? $order->billing_last_name,
            'customer_email' => $customerData['email'] ?? $order->billing_email,
            'customer_phone_number' => $customerData['phone'] ?? $order->billing_phone,
            'customer_address' => $customerData['address'] ?? $order->billing_address,
            'customer_city' => $customerData['city'] ?? $order->billing_city,
            'customer_country' => $customerData['country'] ?? 'CI',
            'customer_state' => $customerData['state'] ?? '',
            'customer_zip_code' => $customerData['zip'] ?? $order->billing_postal_code,
            'notify_url' => url(config('cinetpay.notify_url')),
            'return_url' => url(config('cinetpay.return_url') . '?order=' . $order->id),
            'cancel_url' => url(config('cinetpay.cancel_url') . '?order=' . $order->id),
            'channels' => config('cinetpay.channels'),
            'metadata' => json_encode([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]),
        ];

        try {
            $response = Http::post($this->baseUrl . '/payment', $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['data']['payment_url'])) {
                // Créer l'enregistrement de paiement en attente
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'method' => 'cinetpay',
                    'amount' => $order->total,
                    'currency' => $this->currency,
                    'status' => 'pending',
                    'metadata' => json_encode($result),
                ]);

                // Mettre à jour la commande
                $order->update([
                    'payment_method' => 'cinetpay',
                    'payment_status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'payment_url' => $result['data']['payment_url'],
                    'payment_token' => $result['data']['payment_token'] ?? null,
                    'transaction_id' => $transactionId,
                ];
            }

            Log::error('CinetPay initialization failed', ['response' => $result]);

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement',
                'errors' => $result,
            ];

        } catch (\Exception $e) {
            Log::error('CinetPay exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Erreur de connexion au service de paiement',
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function checkPaymentStatus(string $transactionId): array
    {
        $payload = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
        ];

        try {
            $response = Http::post($this->baseUrl . '/payment/check', $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['data'])) {
                return [
                    'success' => true,
                    'status' => $result['data']['status'] ?? 'UNKNOWN',
                    'amount' => $result['data']['amount'] ?? 0,
                    'currency' => $result['data']['currency'] ?? $this->currency,
                    'payment_method' => $result['data']['payment_method'] ?? null,
                    'payment_date' => $result['data']['payment_date'] ?? null,
                    'data' => $result['data'],
                ];
            }

            return [
                'success' => false,
                'status' => 'ERROR',
                'message' => $result['message'] ?? 'Impossible de vérifier le statut',
            ];

        } catch (\Exception $e) {
            Log::error('CinetPay check status exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'status' => 'ERROR',
                'message' => 'Erreur de connexion',
            ];
        }
    }

    /**
     * Traiter le webhook de CinetPay
     */
    public function handleWebhook(array $data): bool
    {
        $transactionId = $data['cpm_trans_id'] ?? null;

        if (!$transactionId) {
            Log::error('CinetPay webhook: missing transaction_id', $data);
            return false;
        }

        // Vérifier le statut via l'API
        $status = $this->checkPaymentStatus($transactionId);

        if (!$status['success']) {
            return false;
        }

        // Récupérer le paiement
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            Log::error('CinetPay webhook: payment not found', ['transaction_id' => $transactionId]);
            return false;
        }

        $order = $payment->order;

        // SÉCURITÉ: Vérifier que le montant payé correspond au montant de la commande
        if ($status['status'] === 'ACCEPTED') {
            $paidAmount = (float) ($status['amount'] ?? 0);
            $expectedAmount = (float) $order->total;
            
            // Tolérance de 1 unité pour les arrondis
            if (abs($paidAmount - $expectedAmount) > 1) {
                Log::error('CinetPay amount mismatch', [
                    'order' => $order->order_number,
                    'expected' => $expectedAmount,
                    'received' => $paidAmount,
                    'transaction_id' => $transactionId,
                ]);
                
                $payment->update([
                    'status' => 'failed',
                    'notes' => "Montant incorrect: attendu {$expectedAmount}, reçu {$paidAmount}",
                ]);
                
                $order->update([
                    'payment_status' => 'failed',
                    'notes' => "ALERTE: Tentative de paiement avec montant incorrect",
                ]);
                
                return false;
            }
        }

        // Traiter selon le statut
        switch ($status['status']) {
            case 'ACCEPTED':
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'metadata' => json_encode(array_merge(
                        json_decode($payment->metadata ?? '{}', true),
                        ['verification' => $status['data']]
                    )),
                ]);

                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                    'paid_at' => now(),
                ]);

                // Déclencher l'événement OrderPaid
                event(new \App\Events\OrderPaid($order, $payment));

                Log::info('CinetPay payment accepted', ['order' => $order->order_number]);
                return true;

            case 'REFUSED':
            case 'CANCELLED':
                $payment->update([
                    'status' => 'failed',
                    'metadata' => json_encode(array_merge(
                        json_decode($payment->metadata ?? '{}', true),
                        ['failure' => $status['data']]
                    )),
                ]);

                $order->update([
                    'payment_status' => 'failed',
                ]);

                Log::warning('CinetPay payment refused/cancelled', ['order' => $order->order_number]);
                return true;

            default:
                Log::info('CinetPay payment pending', ['order' => $order->order_number, 'status' => $status['status']]);
                return true;
        }
    }

    /**
     * Générer un ID de transaction unique
     */
    protected function generateTransactionId(Order $order): string
    {
        return 'CPY-' . $order->id . '-' . time() . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Vérifier si CinetPay est configuré
     */
    public function isConfigured(): bool
    {
        return !empty($this->siteId) && !empty($this->apiKey);
    }
}

