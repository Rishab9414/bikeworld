<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessagingService
{
    public function sendSms(string $phone, string $message, ?string $template = null, array $params = []): void
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10) {
            $phone = '91'.$phone;
        }

        if (config('brevo.api_key') && config('brevo.sms_enabled', false)) {
            $this->sendBrevoSms($phone, $message);

            return;
        }

        Log::info('SMS queued (log mode)', compact('phone', 'message', 'template', 'params'));
    }

    public function sendWhatsApp(string $phone, string $message, ?string $template = null, array $params = []): void
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10) {
            $phone = '91'.$phone;
        }

        if (config('brevo.api_key') && config('brevo.whatsapp_enabled', false)) {
            $this->sendBrevoWhatsApp($phone, $message, $template, $params);

            return;
        }

        Log::info('WhatsApp queued (log mode)', compact('phone', 'message', 'template', 'params'));
    }

    public function notifyOrderChannels(Order $order, string $event, string $smsMessage): void
    {
        $phone = $order->customer?->mobile ?? $order->user?->phone;

        if ($phone) {
            \App\Jobs\SendSMSJob::dispatch($phone, $smsMessage);
            \App\Jobs\SendWhatsAppJob::dispatch($phone, $smsMessage);
        }
    }

    private function sendBrevoSms(string $phone, string $message): void
    {
        Http::withHeaders([
            'api-key' => config('brevo.api_key'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/transactionalSMS/sms', [
            'sender' => config('brevo.sms_sender', config('app.name')),
            'recipient' => $phone,
            'content' => $message,
            'type' => 'transactional',
        ])->throw();
    }

    private function sendBrevoWhatsApp(string $phone, string $message, ?string $template, array $params): void
    {
        Http::withHeaders([
            'api-key' => config('brevo.api_key'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/whatsapp/sendMessage', [
            'contactNumbers' => [$phone],
            'text' => $message,
            'templateId' => $template,
            'params' => $params,
        ])->throw();
    }
}
