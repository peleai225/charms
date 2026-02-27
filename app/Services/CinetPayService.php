<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
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
        // Priorité aux paramètres admin (Setting) puis fallback sur .env (config)
        $this->siteId = Setting::get('cinetpay_site_id') ?: config('cinetpay.site_id');
        $this->apiKey = Setting::get('cinetpay_api_key') ?: config('cinetpay.api_key');
        $this->secretKey = Setting::get('cinetpay_secret_key') ?: config('cinetpay.secret_key');
        $this->baseUrl = config('cinetpay.api_base_url');
        $this->currency = Setting::get('currency') ?: config('cinetpay.currency');
        $mode = Setting::get('cinetpay_mode') ?: (config('cinetpay.sandbox', true) ? 'sandbox' : 'live');
        $this->sandbox = ($mode ?: 'sandbox') === 'sandbox';
    }

    /**
     * Initialiser un paiement CinetPay
     */
    public function initializePayment(Order $order, array $customerData = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'CinetPay n\'est pas configuré. Renseignez Site ID, API Key et Secret Key dans Paramètres > Paiement.',
                'errors' => 'Missing apikey or site_id',
            ];
        }

        $transactionId = $this->generateTransactionId($order);

        // Montant : doit être un multiple de 5 pour XOF/XAF (sauf USD)
        $amount = (int) $order->total;
        if (!in_array($this->currency, ['USD'])) {
            $amount = (int) (round($amount / 5) * 5);
        }

        // Champs client obligatoires - jamais vides (CinetPay rejette avec MINIMUM_REQUIRED_FIELDS)
        $customerName = trim($customerData['name'] ?? $order->billing_first_name ?? '') ?: 'Client';
        $customerSurname = trim($customerData['surname'] ?? $order->billing_last_name ?? '') ?: 'Commande';
        $customerEmail = trim($customerData['email'] ?? $order->billing_email ?? '') ?: 'contact@' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com');
        $customerPhone = $this->formatPhoneNumber($customerData['phone'] ?? $order->billing_phone ?? '');
        $customerAddress = trim($customerData['address'] ?? $order->billing_address ?? '') ?: 'Non renseigné';
        $customerCity = trim($customerData['city'] ?? $order->billing_city ?? '') ?: 'Abidjan';
        $customerCountry = strtoupper(substr($customerData['country'] ?? $order->billing_country ?? 'CI', 0, 2)) ?: 'CI';
        $customerState = strtoupper(substr($customerData['state'] ?? $order->billing_country ?? $customerCountry, 0, 2)) ?: $customerCountry;
        $customerZipCode = $this->formatZipCode($customerData['zip'] ?? $order->billing_postal_code ?? '');

        // Description : pas de caractères spéciaux (#,/,$,_,&) selon la doc CinetPay
        $description = 'Commande ' . $order->order_number;

        $payload = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $this->currency,
            'alternative_currency' => 'EUR',
            'description' => $description,
            'customer_id' => (string) ($order->customer_id ?? $order->id),
            'customer_name' => $customerName,
            'customer_surname' => $customerSurname,
            'customer_email' => $customerEmail,
            'customer_phone_number' => $customerPhone,
            'customer_address' => $customerAddress,
            'customer_city' => $customerCity,
            'customer_country' => $customerCountry,
            'customer_state' => $customerState,
            'customer_zip_code' => $customerZipCode,
            'notify_url' => url(config('cinetpay.notify_url')),
            'return_url' => url(config('cinetpay.return_url') . '?order=' . $order->id),
            'cancel_url' => url(config('cinetpay.cancel_url') . '?order=' . $order->id),
            'channels' => config('cinetpay.channels', 'ALL'),
            'metadata' => $order->order_number,
            'lang' => 'fr',
        ];

        Log::info('CinetPay: Request payload', [
            'order_id' => $order->id,
            'amount' => $amount,
            'has_apikey' => !empty($this->apiKey),
            'has_site_id' => !empty($this->siteId),
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Chamse-Ecommerce/1.0',
            ])->post($this->baseUrl . '/payment', $payload);

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

            Log::error('CinetPay initialization failed', [
                'response' => $result,
                'code' => $result['code'] ?? null,
                'description' => $result['description'] ?? null,
            ]);

            $message = $result['message'] ?? 'Erreur lors de l\'initialisation du paiement';
            if (isset($result['code']) && (string) $result['code'] === '608') {
                $message = 'Champs requis manquants ou invalides. Vérifiez Site ID, API Key et Secret Key dans Paramètres > Paiement. Détail : ' . ($result['description'] ?? $message);
            }

            return [
                'success' => false,
                'message' => $message,
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
     * Vérifier la signature HMAC du webhook CinetPay (header x-token)
     * @see https://docs.cinetpay.com/api/1.0-en/checkout/hmac
     */
    public function verifyWebhookSignature(array $data, string $receivedToken): bool
    {
        $dataString = ($data['cpm_site_id'] ?? '')
            . ($data['cpm_trans_id'] ?? '')
            . ($data['cpm_trans_date'] ?? '')
            . ($data['cpm_amount'] ?? '')
            . ($data['cpm_currency'] ?? '')
            . ($data['signature'] ?? '')
            . ($data['payment_method'] ?? '')
            . ($data['cel_phone_num'] ?? '')
            . ($data['cpm_phone_prefixe'] ?? '')
            . ($data['cpm_language'] ?? '')
            . ($data['cpm_version'] ?? '')
            . ($data['cpm_payment_config'] ?? '')
            . ($data['cpm_page_action'] ?? '')
            . ($data['cpm_custom'] ?? '')
            . ($data['cpm_designation'] ?? '')
            . ($data['cpm_error_message'] ?? '');

        $expectedToken = hash_hmac('sha256', $dataString, $this->secretKey);

        return hash_equals($expectedToken, $receivedToken);
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
     * Formater le numéro de téléphone pour CinetPay (prefix + number, ex: +2250504315545)
     */
    protected function formatPhoneNumber(?string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone ?? '');
        if (empty($phone)) {
            return '+225000000000';
        }
        if (!str_starts_with($phone, '225') && !str_starts_with($phone, '226') && !str_starts_with($phone, '228')) {
            $phone = '225' . ltrim($phone, '0');
        }
        return '+' . $phone;
    }

    /**
     * Formater le code postal (CinetPay attend 5 caractères, jamais vide)
     */
    protected function formatZipCode(?string $zip): string
    {
        $zip = preg_replace('/\D/', '', $zip ?? '');
        return str_pad(substr($zip, 0, 5), 5, '0', STR_PAD_LEFT) ?: '00000';
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
        return !empty(trim($this->siteId ?? '')) && !empty(trim($this->apiKey ?? ''));
    }
}

