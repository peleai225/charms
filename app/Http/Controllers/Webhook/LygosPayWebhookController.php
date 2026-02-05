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
        
        $lygosPay->handleWebhook($data);
        
        return response()->json(['status' => 'ok']);
    }
}

