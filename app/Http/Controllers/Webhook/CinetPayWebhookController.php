<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\CinetPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CinetPayWebhookController extends Controller
{
    protected CinetPayService $cinetPay;

    public function __construct(CinetPayService $cinetPay)
    {
        $this->cinetPay = $cinetPay;
    }

    /**
     * Gérer les notifications CinetPay (IPN)
     */
    public function handle(Request $request)
    {
        Log::info('CinetPay webhook received', $request->all());

        $data = $request->all();

        if (empty($data['cpm_trans_id'])) {
            Log::error('CinetPay webhook: missing cpm_trans_id');
            return response()->json(['status' => 'error', 'message' => 'Missing transaction ID'], 400);
        }

        // Vérification de la signature HMAC (sécurité)
        $receivedToken = $request->header('x-token', '');
        if ($receivedToken && !$this->cinetPay->verifyWebhookSignature($data, $receivedToken)) {
            Log::warning('CinetPay webhook: invalid signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $success = $this->cinetPay->handleWebhook($data);

        if ($success) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error'], 500);
    }
}

