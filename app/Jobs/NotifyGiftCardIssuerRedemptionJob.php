<?php

declare(strict_types=1);

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotifyGiftCardIssuerRedemptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 5;
    public int $timeout = 30;

    public function __construct(private array $giftCard,
                                private string $emailUser,
                                private string $sentAt
    ) {}

    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }

    public function handle(): void
    {
        $payloadWebhook = [
            'event_id' => $this->giftCard['webhook']['event_id'],
            'type' => 'giftcard.redeemed',
            'data' => [
                'code' => $this->giftCard['code'],
                'email' => $this->emailUser,
                'creator_id' => $this->giftCard['creator_id'],
                'product_id' => $this->giftCard['product_id'],
            ],
            'sent_at' => $this->sentAt,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-GiftFlow-Signature' => $this->generateSignature($payloadWebhook),
        ];

        $response = Http::withHeaders($headers)
            ->post(config('giftflow.webhook_url'), $payloadWebhook);

        if ($response->failed()) {
            throw new Exception("Request failed with status: " . $response->status());
        }

        Log::info('Successfully sent webhook to the issuer.', [
            'event_id' => $this->giftCard['webhook']['event_id'],
            'payload' => $payloadWebhook,
        ]);
    }

    private function generateSignature(array $payload): string
    {
        return hash_hmac(
            'sha256',
            json_encode($payload),
            config('giftflow.webhook_secret')
        );
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('It was not possible to send the webhook to the issuer.', [
            'event_id' => $this->giftCard['webhook']['event_id'] ?? null,
            'payload' => $this->giftCard,
            'error' => $exception?->getMessage(),
        ]);
    }
}
