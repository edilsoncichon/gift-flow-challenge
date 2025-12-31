<?php

namespace App\Enums;

enum GiftCardStatus: string
{
    case AVAILABLE = 'available';
    case REDEEMED = 'redeemed';
}
