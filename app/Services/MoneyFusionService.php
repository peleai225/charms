<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoneyFusionService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $verifyBaseUrl = 'https://www.pay.moneyfusion.net/paiementNotif';

    public function __construct()
    {
        $this->apiUrl = \App\Models\Setting::get('moneyfusion_api_url') ?: config('moneyfusion.api_url', '');
        $this->apiKey = \App\Models\Setting::get('moneyfusion_api_key') ?: config('moneyfusion.api_key', '');
    }

    public function initializePayment(Order $order, array $customerData = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'MoneyFusion n\'est pas configuré. Veuillez renseigner l\'URL API et la clé API dans les paramètres.',
            ];
        }

        $paymentData = [
            'totalPrice' => (int) $order->total,
            'article' => $this->buildArticleList($order),
            'personal_Info' => [
                [
                    'orderId' => $order->id,
                    'orderNumber' => $order->order_number,
                ],
            ],
            'numeroSend' => $this->cleanPhone($order->billing_phone ?? $customerData['phone'] ?? ''),
            'nomclient' => trim(($order->billing_first_name ?? '') . ' ' . ($order->billing_last_name ?? '')),
            'return_url' => url('/checkout/confirmation?order=' . $order->id),
            'webhook_url' => route('webhook.moneyfusion'),
        ];

        Log::info('MoneyFusion: Initializing payment', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'amount' => $paymentData['totalPrice'],
            'api_url' => $this->apiUrl,
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, $paymentData);

            $result = $response->json();

            Log::info('MoneyFusion: API response', [
                'status_code' => $response->status(),
                'response' => $result,
            ]);

            if ($response->successful() && ($result['statut'] ?? false) === true) {
                $token = $result['token'] ?? null;
                $paymentUrl = $result['url'] ?? null;

                if ($token && $paymentUrl) {
                    Payment::create([
                        'order_id' => $order->id,
                        'transaction_id' => $token,
                        'method' => 'moneyfusion',
                        'gateway' => 'moneyfusion',
                        'gateway_transaction_id' => $token,
                        'amount' => $order->total,
                        'currency' => $order->currency ?? 'XOF',
                        'status' => 'pending',
                        'gateway_response' => $result,
                    ]);

                    $order->update([
                        'payment_method' => 'moneyfusion',
                        'payment_status' => 'pending',
                    ]);

                    return [
                        'success' => true,
                        'payment_url' => $paymentUrl,
                        'token' => $token,
                    ];
                }
            }

            $errorMessage = $result['message'] ?? 'Erreur lors de l\'initialisation du paiement MoneyFusion.';

            Log::error('MoneyFusion: Payment initialization failed', [
                'order_id' => $order->id,
                'response' => $result,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'errors' => $result,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('MoneyFusion: Connection error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Impossible de se connecter à MoneyFusion. Vérifiez votre URL API.',
            ];
        } catch (\Exception $e) {
            Log::error('MoneyFusion: Exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Erreur de connexion au service MoneyFusion: ' . $e->getMessage(),
            ];
        }
    }

    public function checkPaymentStatus(string $token): array
    {
        try {
            $response = Http::timeout(15)
                ->get("{$this->verifyBaseUrl}/{$token}");

            $result = $response->json();

            if ($response->successful() && ($result['statut'] ?? false) === true) {
                $data = $result['data'] ?? [];
                $status = $data['statut'] ?? 'pending';

                return [
                    'success' => true,
                    'status' => $this->mapStatus($status),
                    'raw_status' => $status,
                    'amount' => $data['Montant'] ?? 0,
                    'fees' => $data['frais'] ?? 0,
                    'payment_method' => $data['moyen'] ?? null,
                    'transaction_number' => $data['numeroTransaction'] ?? null,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => $result['message'] ?? 'Impossible de vérifier le statut.',
            ];

        } catch (\Exception $e) {
            Log::error('MoneyFusion: Check status error', ['error' => $e->getMessage(), 'token' => $token]);

            return [
                'success' => false,
                'status' => 'error',
                'message' => 'Erreur de connexion lors de la vérification.',
            ];
        }
    }

    public function handleWebhook(array $data): bool
    {
        $event = $data['event'] ?? null;
        $token = $data['tokenPay'] ?? null;

        if (!$token) {
            Log::error('MoneyFusion webhook: missing tokenPay', $data);
            return false;
        }

        Log::info('MoneyFusion webhook received', [
            'event' => $event,
            'token' => $token,
            'amount' => $data['Montant'] ?? null,
        ]);

        $payment = Payment::where('gateway', 'moneyfusion')
            ->where(function ($q) use ($token) {
                $q->where('transaction_id', $token)
                  ->orWhere('gateway_transaction_id', $token);
            })
            ->first();

        if (!$payment) {
            Log::error('MoneyFusion webhook: payment not found', ['token' => $token]);
            return false;
        }

        if ($payment->status === 'completed') {
            Log::info('MoneyFusion webhook: payment already completed, skipping', ['token' => $token]);
            return true;
        }

        $order = $payment->order;

        if ($event === 'payin.session.completed') {
            $paidAmount = (float) ($data['Montant'] ?? 0);
            $expectedAmount = (float) $order->total;

            if ($paidAmount > 0 && abs($paidAmount - $expectedAmount) > 1) {
                Log::error('MoneyFusion: amount mismatch', [
                    'order' => $order->order_number,
                    'expected' => $expectedAmount,
                    'received' => $paidAmount,
                ]);

                $payment->update([
                    'status' => 'failed',
                    'notes' => "Montant incorrect: attendu {$expectedAmount}, reçu {$paidAmount}",
                ]);

                return false;
            }

            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'gateway_response' => array_merge(
                    is_array($payment->gateway_response) ? $payment->gateway_response : [],
                    ['webhook' => $data]
                ),
            ]);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'paid_at' => now(),
            ]);

            event(new \App\Events\OrderPaid($order, $payment));

            Log::info('MoneyFusion: payment completed', ['order' => $order->order_number]);
            return true;
        }

        if ($event === 'payin.session.cancelled') {
            $payment->update([
                'status' => 'failed',
                'gateway_response' => array_merge(
                    is_array($payment->gateway_response) ? $payment->gateway_response : [],
                    ['webhook' => $data]
                ),
            ]);

            $order->update(['payment_status' => 'failed']);

            Log::info('MoneyFusion: payment cancelled', ['order' => $order->order_number]);
            return true;
        }

        // payin.session.pending — nothing to do, just acknowledge
        return true;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiUrl);
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'MoneyFusion n\'est pas configuré. Veuillez renseigner l\'URL API.',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl, [
                    'totalPrice' => 100,
                    'article' => [['test' => 100]],
                    'numeroSend' => '0000000000',
                    'nomclient' => 'Test Connection',
                ]);

            $statusCode = $response->status();

            if ($statusCode < 500) {
                return [
                    'success' => true,
                    'message' => 'Connexion à MoneyFusion réussie ! L\'API est accessible (HTTP ' . $statusCode . ').',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur serveur MoneyFusion (HTTP ' . $statusCode . '). Vérifiez votre URL API.',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Impossible de se connecter à MoneyFusion: ' . $e->getMessage(),
            ];
        }
    }

    protected function mapStatus(string $status): string
    {
        return match ($status) {
            'paid' => 'paid',
            'failure', 'no paid' => 'failed',
            'pending' => 'pending',
            default => 'pending',
        };
    }

    protected function buildArticleList(Order $order): array
    {
        $articles = [];
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $articles[$item->name] = (int) $item->total;
        }

        return [$articles];
    }

    protected function cleanPhone(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', $phone);
    }
}
