<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCurrencyException extends CommissionException
{
    public static function for(string $code): self
    {
        return new self(sprintf('Currency must be a 3-letter uppercase ISO 4217 code, "%s" given.', $code));
    }
}
