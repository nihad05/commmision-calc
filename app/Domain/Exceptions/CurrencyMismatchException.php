<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Money\Currency;

final class CurrencyMismatchException extends CommissionException
{
    public static function between(Currency $left, Currency $right): self
    {
        return new self(sprintf('Cannot combine amounts in different currencies: %s and %s.', $left->code, $right->code));
    }
}
