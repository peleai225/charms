<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\JekoAfricaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JekoAfricaWebhookController extends Controller
{
    public function handle(Request $request, JekoAfricaService $jeko)
    {
        $rawBody  = $request->getContent();
        $signature = $request->header('Jeko-Signature', '');
        $data     = $request->json()->all();

        Log::info('Jeko webhook incoming', [
            'ip'        => $request->ip(),
            'signature' => substr($signature, 0, 16) . '...',
            'event'     => $data['transactionType'] ?? 'unknown',
        ]);

        try {
            $result = $jeko->handleWebhook($rawBody, $signature, $data);

            if (!$result) {
                return response()->json(['error' => 'Webhook processing failed'], 400);
            }

            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            Log::error('Jeko webhook exception', ['error' => $e->getMessage()]);
            // Retourner 200 pour éviter les retries infinis de Jeko
            return response()->json(['ok' => true]);
        }
    }
}
