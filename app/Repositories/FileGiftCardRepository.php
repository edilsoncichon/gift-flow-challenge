<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\GiftCardStatus;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;

class FileGiftCardRepository implements GiftCardRepository
{
    private Filesystem $disk;

    public function __construct(Factory $storage)
    {
        $this->disk = $storage->disk('local');
    }

    public function findAll(): array
    {
        $giftCardsFile = $this->disk->get(config('giftflow.gift_cards_file'));

        return json_decode($giftCardsFile, true);
    }

    public function findByCode(string $code): array|false
    {
        $giftCards = $this->findAll();

        foreach ($giftCards as $giftCard) {
            if ($giftCard['code'] === $code) {
                return $giftCard;
            }
        }

        return false;
    }

    public function redeem(string $code, string $eventId): array
    {
        $giftCards = $this->findAll();

        $cardRedeemed = null;

        foreach ($giftCards as &$giftCard) {
            if ($giftCard['code'] === $code) {
                $giftCard['status'] = GiftCardStatus::REDEEMED->value;
                $giftCard['webhook'] = [
                    'status' => 'queued',
                    'event_id' => $eventId,
                ];
                $cardRedeemed = $giftCard;
                break;
            }
        }

        $this->disk->put(
            config('giftflow.gift_cards_file'),
            json_encode($giftCards, JSON_PRETTY_PRINT)
        );

        return $cardRedeemed;
    }
}
