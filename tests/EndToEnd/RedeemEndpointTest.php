<?php

namespace Tests\EndToEnd;

use App\Jobs\NotifyGiftCardIssuerRedemptionJob;
use App\Repositories\FileGiftCardRepository;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RedeemEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('giftflow:seed');
    }

    public function test_should_successfully_redeem_code(): void
    {
        $queue = Queue::fake();
        $body = [
            'code' => 'GFLOW-TEST-0001',
            'user' => ['email' => 'newuser@example.com'],
        ];
        $eventId = hash_hmac(
            algo: 'sha256',
            data: $body['code'].$body['user']['email'],
            key: config('app.key')
        );
        $response = [
            'code' => $body['code'],
            'status' => 'redeemed',
            'product_id' => 'product_abc',
            'creator_id' => 'creator_123',
            'webhook' => [
                'status' => 'queued',
                'event_id' => $eventId,
            ],
        ];

        $this->post('api/redeem', $body, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson($response);

        $queue->assertPushed(NotifyGiftCardIssuerRedemptionJob::class);

        $repository = $this->app->make(FileGiftCardRepository::class);
        $redeemedCard = $repository->findByCode($body['code']);

        $this->assertEquals($response, $redeemedCard);
    }

    public function test_should_validate_input(): void
    {
        $body = [
            'code' => 999,
            'user' => ['email' => 'invalid-email'],
        ];

        $this->post('api/redeem', $body, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson(
                [
                    'message' => 'The code field must be a string. (and 1 more error)',
                    'errors' => [
                        'code' => [
                            'The code field must be a string.',
                        ],
                        'user.email' => [
                            'The user.email field must be a valid email address.',
                        ],
                    ],
                ]
            );
    }

    public function test_should_validate_if_code_exists(): void
    {
        $body = [
            'code' => 'GFLOW-INEXISTENT-404',
            'user' => ['email' => 'newuser@example.com'],
        ];
        $this->post('api/redeem', $body, ['Accept' => 'application/json'])
            ->assertStatus(404)
            ->assertJson(['message' => 'Gift card not found']);
    }

    public function test_should_validate_if_code_already_redeemed(): void
    {
        $body = [
            'code' => 'GFLOW-USED-0003',
            'user' => ['email' => 'user@example.com'],
        ];
        $this->post('api/redeem', $body, ['Accept' => 'application/json'])
            ->assertStatus(409)
            ->assertJson(['message' => 'Gift card already redeemed']);
    }

    public function test_should_not_allow_redeeming_same_code_twice(): void
    {
        $queue = Queue::fake();
        $body = [
            'code' => 'GFLOW-TEST-0001',
            'user' => ['email' => 'user@example.com'],
        ];
        $eventId = hash_hmac(
            algo: 'sha256',
            data: $body['code'].$body['user']['email'],
            key: config('app.key')
        );
        $response = [
            'code' => $body['code'],
            'status' => 'redeemed',
            'product_id' => 'product_abc',
            'creator_id' => 'creator_123',
            'webhook' => [
                'status' => 'queued',
                'event_id' => $eventId,
            ],
        ];

        $this->post('api/redeem', $body, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson($response);

        $this->post('api/redeem', $body, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson($response);

        $body['user']['email'] = 'another@email.com';

        $this->post('api/redeem', $body, ['Accept' => 'application/json'])
            ->assertStatus(409)
            ->assertJson(['message' => 'Gift card already redeemed']);

        $queue->assertPushed(NotifyGiftCardIssuerRedemptionJob::class, 1);
    }
}
