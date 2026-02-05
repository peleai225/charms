<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LygosPayService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $currency;

    public function __construct()
    {
        // Récupérer la clé API depuis les settings ou config
        $this->apiKey = \App\Models\Setting::get('lygos_api_key') ?: config('lygos.api_key');
        // S'assurer qu'il n'y a pas de slash à la fin pour éviter les doubles slashes
        $this->baseUrl = rtrim(config('lygos.api_base_url', 'https://api.lygosapp.com/v1'), '/');
        $this->currency = config('lygos.currency', 'XOF');
    }

    /**
     * Initialiser un paiement Lygos Pay
     */
    public function initializePayment(Order $order, array $customerData = []): array
    {
        // Vérifier la configuration
        if (!$this->isConfigured()) {
            Log::error('Lygos Pay not configured', [
                'api_key_set' => !empty($this->apiKey),
                'base_url' => $this->baseUrl,
            ]);
            
            return [
                'success' => false,
                'message' => 'Lygos Pay n\'est pas configuré. Veuillez configurer votre clé API dans les paramètres.',
                'errors' => 'API key missing',
            ];
        }

        $transactionId = $this->generateTransactionId($order);

        // Créer un gateway de paiement
        $payload = [
            'amount' => (int) $order->total,
            'currency' => $this->currency,
            'shop_name' => config('app.name'),
            'message' => "Commande #{$order->order_number}",
            'order_id' => $order->order_number,
            'success_url' => url('/checkout/confirmation?order=' . $order->id),
            'failure_url' => url('/checkout/annulation?order=' . $order->id),
        ];

        $apiUrl = $this->baseUrl . '/gateway';
        
        Log::info('Lygos Pay: Initializing payment', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_amount,
            'shipping' => $order->shipping_amount,
            'tax' => $order->tax_amount,
            'total' => $order->total,
            'amount_sent_to_lygos' => (int) $order->total,
            'api_url' => $apiUrl,
            'api_key_length' => strlen($this->apiKey),
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($apiUrl, $payload);

            $statusCode = $response->status();
            $result = $response->json();
            $body = $response->body();

            Log::info('Lygos Pay API Response', [
                'status_code' => $statusCode,
                'response_body' => $body,
                'response_json' => $result,
            ]);

            if ($response->successful()) {
                // Vérifier différents formats de réponse possibles
                $paymentLink = $result['link'] ?? $result['data']['link'] ?? $result['payment_url'] ?? null;
                $gatewayId = $result['id'] ?? $result['data']['id'] ?? $result['gateway_id'] ?? null;

                if ($paymentLink) {
                    // Créer l'enregistrement de paiement en attente
                    Payment::create([
                        'order_id' => $order->id,
                        'transaction_id' => $gatewayId ?? $transactionId,
                        'method' => 'lygos',
                        'gateway' => 'lygos',
                        'gateway_transaction_id' => $gatewayId,
                        'amount' => $order->total,
                        'currency' => $this->currency,
                        'status' => 'pending',
                        'gateway_response' => $result,
                    ]);

                    // Mettre à jour la commande
                    $order->update([
                        'payment_method' => 'lygos',
                        'payment_status' => 'pending',
                    ]);

                    Log::info('Lygos Pay: Payment initialized successfully', [
                        'order_id' => $order->id,
                        'gateway_id' => $gatewayId,
                        'payment_url' => $paymentLink,
                    ]);

                    return [
                        'success' => true,
                        'payment_url' => $paymentLink,
                        'gateway_id' => $gatewayId,
                        'transaction_id' => $gatewayId ?? $transactionId,
                    ];
                } else {
                    Log::error('Lygos Pay: No payment link in response', [
                        'response' => $result,
                        'status_code' => $statusCode,
                    ]);
                }
            } else {
                Log::error('Lygos Pay API Error', [
                    'status_code' => $statusCode,
                    'response' => $result,
                    'body' => $body,
                ]);
            }

            // Gestion des erreurs détaillée
            $errorMessage = 'Erreur lors de l\'initialisation du paiement';
            if (isset($result['message'])) {
                $errorMessage = $result['message'];
            } elseif (isset($result['error'])) {
                $errorMessage = is_string($result['error']) ? $result['error'] : json_encode($result['error']);
            } elseif ($statusCode === 401) {
                $errorMessage = 'Clé API invalide. Vérifiez votre configuration.';
            } elseif ($statusCode === 404) {
                $errorMessage = 'Endpoint API introuvable. Vérifiez l\'URL de l\'API.';
            } elseif ($statusCode >= 500) {
                $errorMessage = 'Erreur serveur Lygos Pay. Veuillez réessayer plus tard.';
            }

            return [
                'success' => false,
                'message' => $errorMessage,
                'errors' => $result,
                'status_code' => $statusCode,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Lygos Pay: Connection exception', [
                'error' => $e->getMessage(),
                'api_url' => $apiUrl,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Impossible de se connecter à l\'API Lygos Pay. Vérifiez votre connexion Internet et que l\'URL de l\'API est correcte.',
                'errors' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Lygos Pay: General exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'api_url' => $apiUrl,
            ]);

            return [
                'success' => false,
                'message' => 'Erreur de connexion au service de paiement: ' . $e->getMessage(),
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function checkPaymentStatus(string $gatewayId): array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
            ])->get($this->baseUrl . '/gateway/' . $gatewayId);

            $result = $response->json();

            if ($response->successful() && isset($result['id'])) {
                return [
                    'success' => true,
                    'status' => $this->mapStatus($result),
                    'amount' => $result['amount'] ?? 0,
                    'currency' => $result['currency'] ?? $this->currency,
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'status' => 'ERROR',
                'message' => $result['message'] ?? 'Impossible de vérifier le statut',
            ];

        } catch (\Exception $e) {
            Log::error('Lygos Pay check status exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'status' => 'ERROR',
                'message' => 'Erreur de connexion',
            ];
        }
    }

    /**
     * Traiter le webhook de Lygos Pay (si disponible)
     */
    public function handleWebhook(array $data): bool
    {
        $gatewayId = $data['id'] ?? $data['gateway_id'] ?? null;

        if (!$gatewayId) {
            Log::error('Lygos Pay webhook: missing gateway_id', $data);
            return false;
        }

        // Vérifier le statut via l'API
        $status = $this->checkPaymentStatus($gatewayId);

        if (!$status['success']) {
            return false;
        }

        // Récupérer le paiement
        $payment = Payment::where('transaction_id', $gatewayId)
            ->orWhere('metadata', 'like', '%"id":"' . $gatewayId . '"%')
            ->first();

        if (!$payment) {
            Log::error('Lygos Pay webhook: payment not found', ['gateway_id' => $gatewayId]);
            return false;
        }

        $order = $payment->order;

        // Traiter selon le statut
        if ($status['status'] === 'paid') {
            $paidAmount = (float) ($status['amount'] ?? 0);
            $expectedAmount = (float) $order->total;
            
            // Tolérance de 1 unité pour les arrondis
            if (abs($paidAmount - $expectedAmount) > 1) {
                Log::error('Lygos Pay amount mismatch', [
                    'order' => $order->order_number,
                    'expected' => $expectedAmount,
                    'received' => $paidAmount,
                    'gateway_id' => $gatewayId,
                ]);
                
                $payment->update([
                    'status' => 'failed',
                    'notes' => "Montant incorrect: attendu {$expectedAmount}, reçu {$paidAmount}",
                ]);
                
                return false;
            }

            $currentResponse = $payment->gateway_response ?? [];
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'gateway_response' => array_merge(
                    is_array($currentResponse) ? $currentResponse : [],
                    ['verification' => $status['data']]
                ),
            ]);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'paid_at' => now(),
            ]);

            // Déclencher l'événement OrderPaid
            event(new \App\Events\OrderPaid($order, $payment));

            Log::info('Lygos Pay payment accepted', ['order' => $order->order_number]);
            return true;
        }

        return true;
    }

    /**
     * Mapper le statut Lygos vers notre système
     */
    protected function mapStatus(array $data): string
    {
        // À adapter selon la documentation réelle de Lygos
        // Pour l'instant, on suppose qu'il y a un champ status
        $status = $data['status'] ?? 'pending';
        
        return match($status) {
            'paid', 'completed', 'success' => 'paid',
            'failed', 'cancelled' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Générer un ID de transaction unique
     */
    protected function generateTransactionId(Order $order): string
    {
        return 'LYG-' . $order->id . '-' . time() . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Vérifier si Lygos Pay est configuré
     */
    public function isConfigured(): bool
    {
        $configured = !empty($this->apiKey);
        
        if (!$configured) {
            Log::warning('Lygos Pay: Not configured', [
                'api_key_from_settings' => \App\Models\Setting::get('lygos_api_key'),
                'api_key_from_config' => config('lygos.api_key'),
            ]);
        }
        
        return $configured;
    }
    
    /**
     * Tester la connexion à l'API Lygos
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Lygos Pay n\'est pas configuré. Veuillez entrer votre clé API.',
            ];
        }

        try {
            // Tester en listant les gateways (endpoint simple)
            $apiUrl = $this->baseUrl . '/gateway';
            
            Log::info('Lygos Pay: Testing connection', [
                'api_url' => $apiUrl,
                'api_key_length' => strlen($this->apiKey),
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'api-key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($apiUrl);

            $statusCode = $response->status();
            $result = $response->json();
            $body = $response->body();

            Log::info('Lygos Pay: Test connection response', [
                'status_code' => $statusCode,
                'response' => $result,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion à l\'API Lygos Pay réussie ! L\'API est accessible.',
                    'status_code' => $statusCode,
                ];
            }

            $errorMessage = 'Erreur API';
            if (isset($result['message'])) {
                $errorMessage = $result['message'];
            } elseif ($statusCode === 401) {
                $errorMessage = 'Clé API invalide ou expirée';
            } elseif ($statusCode === 404) {
                $errorMessage = 'Endpoint introuvable. Vérifiez l\'URL de l\'API.';
            } else {
                $errorMessage = 'Code HTTP ' . $statusCode;
            }

            return [
                'success' => false,
                'message' => $errorMessage,
                'status_code' => $statusCode,
                'response' => $result,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Lygos Pay: Connection exception during test', [
                'error' => $e->getMessage(),
                'api_url' => $this->baseUrl . '/gateway',
            ]);

            return [
                'success' => false,
                'message' => 'Impossible de se connecter à l\'API Lygos Pay. Vérifiez que l\'URL ' . $this->baseUrl . ' est accessible.',
                'error' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Lygos Pay: Exception during test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du test: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }
}

