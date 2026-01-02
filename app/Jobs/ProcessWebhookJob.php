<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(private array $payload) {}

    public function handle(): void
    {
        $key = "webhook_processed_{$this->payload['event_id']}";
        if (Cache::has($key)) {
            Log::info(
                "Webhook event {$this->payload['event_id']} already processed.",
                $this->payload
            );

            return;
        }

        // TODO Implementation suggestion:
        // Here I would validate the input to ensure the received data is correct;
        // Then, I would process the event according to its type (for example, giftcard.redeemed);
        // Then, I would register the event in the database or execute other necessary actions.
        // Finally, I would emit a WebhookProcessed event, in case another part of the system needs to know.

        Cache::put($key, true, now()->addDays(2));

        Log::info(
            "Webhook event {$this->payload['event_id']} processed successfully.",
            $this->payload
        );
    }
}
