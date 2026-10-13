<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Money;

use App\Domain\Exceptions\InvalidCurrencyException;
use App\Domain\Money\Currency;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    #[Test]
    public function it_accepts_an_iso_4217_code(): void
    {
        self::assertSame('USD', (new Currency('USD'))->code);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCodes(): iterable
    {
        yield 'lowercase' => ['eur'];
        yield 'too short' => ['EU'];
        yield 'too long' => ['EURO'];
        yield 'digits' => ['E1R'];
        yield 'empty' => [''];
    }

    #[Test]
    #[DataProvider('invalidCodes')]
    public function it_rejects_invalid_codes(string $code): void
    {
        $this->expectException(InvalidCurrencyException::class);

        new Currency($code);
    }
}
