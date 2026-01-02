<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class IssuerPlatformWebhookTest extends TestCase
{
    public function test_should_successfully_receive_webhook(): void
    {
        $eventId = hash_hmac('sha256', '123', config('app.key'));
        $payload = [
            'event_id' => $eventId,
            'type' => 'giftcard.redeemed',
            'data' => [
                'code' => 'GFLOW-TEST-0001',
                'email' => 'newuser@example.com',
                'creator_id' => 'creator_123',
                'product_id' => 'product_abc',
            ],
            'sent_at' => '2025-12-29T00:00:00Z',
        ];
        $headers = [
            'X-GiftFlow-Signature' => hash_hmac(
                'sha256',
                json_encode($payload),
                config('giftflow.webhook_secret')
            ),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $this->postJson('webhook/issuer-platform', $payload, $headers)
            ->assertStatus(200)
            ->assertJson(['message' => 'Webhook received']);
    }

    public function test_should_not_allow_invalid_signature(): void
    {
        $payload = [
            'event_id' => hash_hmac('sha256', '123', config('app.key')),
            'type' => 'giftcard.redeemed',
            'data' => [
                'code' => 'GFLOW-TEST-0001',
                'email' => 'newuser@example.com',
                'creator_id' => 'creator_123',
                'product_id' => 'product_abc',
            ],
            'sent_at' => '2025-12-29T00:00:00Z',
        ];
        $headers = [
            'X-GiftFlow-Signature' => hash_hmac(
                'sha256',
                json_encode($payload),
                'invalid_secret_key'
            ),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $this->postJson('webhook/issuer-platform', $payload, $headers)
            ->assertStatus(401);
    }

    public function test_should_not_allow_duplicate_event(): void
    {
        $eventId = hash_hmac('sha256', '123', config('app.key'));

        Cache::shouldReceive('has')
            ->twice()
            ->with("webhook_processed_$eventId")
            ->andReturn(false, true);

        Cache::shouldReceive('put')
            ->once()
            ->with("webhook_processed_$eventId", true, Mockery::any())
            ->andReturn(true);

        $payload = [
            'event_id' => $eventId,
            'type' => 'giftcard.redeemed',
            'data' => [
                'code' => 'GFLOW-TEST-0001',
                'email' => 'newuser@example.com',
                'creator_id' => 'creator_123',
                'product_id' => 'product_abc',
            ],
            'sent_at' => '2025-12-29T00:00:00Z',
        ];
        $headers = [
            'X-GiftFlow-Signature' => hash_hmac(
                'sha256',
                json_encode($payload),
                config('giftflow.webhook_secret')
            ),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response1 = $this->postJson('webhook/issuer-platform', $payload, $headers);
        $response2 = $this->postJson('webhook/issuer-platform', $payload, $headers);
        $response1->assertStatus(200);
        $response2->assertStatus(200);
    }
}
