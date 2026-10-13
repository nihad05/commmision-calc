<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Money\Currency;

final class InvalidCurrencyException extends CommissionException
{
    public static function for(string $code): self
    {
        return new self(sprintf('Currency must be a 3-letter uppercase ISO 4217 code, "%s" given.', $code));
    }

    public static function mismatch(Currency $left, Currency $right): self
    {
        return new self(sprintf('Cannot combine amounts in different currencies: %s and %s.', $left->code, $right->code));
    }
}
