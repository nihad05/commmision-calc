<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Money\Amount;

final readonly class FeeRounder
{
    public const int PRECISION = 2;

    public function round(Amount $fee): Amount
    {
        return $fee->roundUp(self::PRECISION);
    }
}
