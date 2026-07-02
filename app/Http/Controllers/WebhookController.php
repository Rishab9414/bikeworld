<?php

namespace App\Http\Controllers;

use App\Services\DelhiveryService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class WebhookController extends Controller
{
    public function delhivery(Request $request, DelhiveryService $delhivery): JsonResponse
    {
        Log::info('Delhivery webhook received', $request->all());

        $delhivery->handleWebhook($request->all());

        return response()->json(['success' => true]);
    }

    public function razorpay(Request $request, RazorpayService $razorpay): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $webhookSecret = config('razorpay.webhook_secret');

        if ($webhookSecret && $signature) {
            try {
                (new Api(config('razorpay.key_id'), config('razorpay.key_secret')))
                    ->utility
                    ->verifyWebhookSignature($payload, $signature, $webhookSecret);
            } catch (SignatureVerificationError $e) {
                Log::warning('Razorpay webhook signature invalid', ['message' => $e->getMessage()]);

                return response()->json(['success' => false], 400);
            }
        }

        $data = $request->all();
        Log::info('Razorpay webhook received', ['event' => $data['event'] ?? null]);

        $razorpay->handleWebhook($data);

        return response()->json(['success' => true]);
    }
}
