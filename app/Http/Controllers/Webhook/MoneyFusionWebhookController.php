<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\MoneyFusionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MoneyFusionWebhookController extends Controller
{
    public function handle(Request $request, MoneyFusionService $moneyFusion)
    {
        $data = $request->all();

        Log::info('MoneyFusion webhook received', $data);

        $success = $moneyFusion->handleWebhook($data);

        if ($success) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error'], 400);
    }
}
