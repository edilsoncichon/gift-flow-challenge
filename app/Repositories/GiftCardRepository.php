<?php

namespace App\Repositories;

interface GiftCardRepository
{
    public function findAll(): array;

    public function findByCode(string $code): array|false;

    public function redeem(string $code, string $eventId): array;
}
