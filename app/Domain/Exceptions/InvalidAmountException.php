<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidAmountException extends CommissionException
{
    public static function for(string $value): self
    {
        return new self(sprintf('Amount must be a plain decimal number, "%s" given.', $value));
    }
}
