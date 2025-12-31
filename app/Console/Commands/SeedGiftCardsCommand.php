<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SeedGiftCardsCommand extends Command
{
    protected $signature = 'giftflow:seed';

    protected $description = 'Seed gift cards into local storage for testing purposes.';

    public function handle()
    {
        $data = config('giftflow.sample_gift_cards', []);

        Storage::disk('local')->put(config('giftflow.gift_cards_file'), json_encode($data, JSON_PRETTY_PRINT));

        $this->info('Gift cards seeded successfully in storage/app/private/giftcards.json');
    }
}
