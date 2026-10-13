<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class NegativeAmountException extends CommissionException
{
    public static function for(string $value): self
    {
        return new self(sprintf('Amount must not be negative, "%s" given.', $value));
    }
}
