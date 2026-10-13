<?php

declare(strict_types=1);

namespace App\Domain\Money;

use App\Domain\Exceptions\InvalidCurrencyException;

final readonly class Currency
{
    public function __construct(public string $code)
    {
        if (preg_match('/^[A-Z]{3}$/', $code) !== 1) {
            throw InvalidCurrencyException::for($code);
        }
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }
}
