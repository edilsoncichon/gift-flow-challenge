<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\GiftCardStatus;
use App\Exceptions\DomainException;
use App\Exceptions\NotFoundException;
use App\Http\Requests\RedeemRequest;
use App\Jobs\NotifyGiftCardIssuerRedemptionJob;
use App\Repositories\GiftCardRepository;

final class RedeemGiftCardAction
{
    public function __construct(private GiftCardRepository $repository) {}

    /**
     * @throws NotFoundException
     * @throws DomainException
     */
    public function execute(RedeemRequest $request): array
    {
        $card = $this->repository->findByCode($request->input('code'));

        if (! $card) {
            throw new NotFoundException('Gift card not found');
        }

        $eventId = hash_hmac(
            algo: 'sha256',
            data: $card['code'].$request->input('user.email'),
            key: config('app.key')
        );

        if ($card['status'] == GiftCardStatus::REDEEMED->value) {
            if ($card['webhook']['event_id'] === $eventId) {
                return $card;
            }
            throw new DomainException('Gift card already redeemed');
        }

        $cardRedeemed = $this->repository->redeem($card['code'], $eventId);

        NotifyGiftCardIssuerRedemptionJob::dispatch($cardRedeemed);

        return $cardRedeemed;
    }
}
