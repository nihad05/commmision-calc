<?php

declare(strict_types=1);

namespace App\Domain\Money;

use App\Domain\Exceptions\InvalidAmountException;
use App\Domain\Exceptions\InvalidCurrencyException;

final readonly class Amount
{
    public const int SCALE = 10;

    /** @var numeric-string */
    private string $value;

    public function __construct(string $value, private Currency $currency)
    {
        if (! is_numeric($value) || preg_match('/^-?\d+(\.\d+)?$/', $value) !== 1) {
            throw InvalidAmountException::for($value);
        }

        if (bccomp($value, '0', self::SCALE) < 0) {
            throw InvalidAmountException::negative($value);
        }

        $this->value = bcadd($value, '0', self::SCALE);
    }

    public static function of(string $value, string $currency): self
    {
        return new self($value, new Currency($currency));
    }

    /**
     * @return numeric-string
     */
    public function value(): string
    {
        return $this->value;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function format(int $decimals = 2): string
    {
        return bcadd($this->value, '0', $decimals);
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(bcadd($this->value, $other->value, self::SCALE), $this->currency);
    }

    /**
     * @param  numeric-string  $factor
     */
    public function multiply(string $factor): self
    {
        return new self(bcmul($this->value, $factor, self::SCALE), $this->currency);
    }

    public function min(self $other): self
    {
        return $this->compareTo($other) <= 0 ? $this : $other;
    }

    public function max(self $other): self
    {
        return $this->compareTo($other) >= 0 ? $this : $other;
    }

    public function roundUp(int $precision): self
    {
        $truncated = bcadd($this->value, '0', $precision);

        if (bccomp($truncated, $this->value, self::SCALE) === 0) {
            return new self($truncated, $this->currency);
        }

        $step = bcpow('10', (string) -$precision, $precision);

        return new self(bcadd($truncated, $step, $precision), $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->currency->equals($other->currency) && $this->value === $other->value;
    }

    private function compareTo(self $other): int
    {
        $this->assertSameCurrency($other);

        return bccomp($this->value, $other->value, self::SCALE);
    }

    private function assertSameCurrency(self $other): void
    {
        if (! $this->currency->equals($other->currency)) {
            throw InvalidCurrencyException::mismatch($this->currency, $other->currency);
        }
    }
}
