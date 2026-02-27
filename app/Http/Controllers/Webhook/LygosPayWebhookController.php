<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\LygosPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LygosPayWebhookController extends Controller
{
    public function handle(Request $request, LygosPayService $lygosPay)
    {
        $data = $request->all();

        Log::info('Lygos Pay webhook received', $data);

        // Lygos ne documente pas de signature webhook ; on vérifie via l'API (checkPaymentStatus)
        // avant de traiter. En cas d'échec, on retourne un code approprié.
        $success = $lygosPay->handleWebhook($data);

        if ($success) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error'], 500);
    }
}

