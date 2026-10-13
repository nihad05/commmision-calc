<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Money;

use App\Domain\Exceptions\InvalidAmountException;
use App\Domain\Exceptions\InvalidCurrencyException;
use App\Domain\Money\Amount;
use App\Domain\Money\Currency;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AmountTest extends TestCase
{
    #[Test]
    public function it_keeps_full_precision_without_floats(): void
    {
        $amount = Amount::of('0.1', 'EUR')->add(Amount::of('0.2', 'EUR'));

        self::assertSame('0.3000000000', $amount->value());
        self::assertTrue($amount->equals(Amount::of('0.3', 'EUR')));
    }

    #[Test]
    public function it_is_immutable(): void
    {
        $original = Amount::of('100', 'EUR');

        $original->add(Amount::of('1', 'EUR'));
        $original->multiply('2');
        $original->roundUp(0);

        self::assertSame('100.0000000000', $original->value());
    }

    #[Test]
    public function it_multiplies_by_a_decimal_factor(): void
    {
        self::assertSame('0.0300000000', Amount::of('100', 'EUR')->multiply('0.0003')->value());
    }

    #[Test]
    public function it_returns_the_smaller_and_the_larger_amount(): void
    {
        $small = Amount::of('1.5', 'EUR');
        $large = Amount::of('2', 'EUR');

        self::assertSame($small, $small->min($large));
        self::assertSame($small, $large->min($small));
        self::assertSame($large, $small->max($large));
        self::assertSame($large, $large->max($small));
    }

    /**
     * @return iterable<string, array{string, int, string}>
     */
    public static function roundUpCases(): iterable
    {
        yield 'already rounded' => ['0.03', 2, '0.03'];
        yield 'tiny remainder goes up' => ['0.0301', 2, '0.04'];
        yield 'smallest remainder goes up' => ['1.0000000001', 2, '1.01'];
        yield 'zero stays zero' => ['0', 2, '0.00'];
        yield 'zero precision' => ['4.2', 0, '5'];
    }

    #[Test]
    #[DataProvider('roundUpCases')]
    public function it_rounds_up(string $value, int $precision, string $expected): void
    {
        self::assertSame($expected, Amount::of($value, 'EUR')->roundUp($precision)->format($precision));
    }

    #[Test]
    public function it_rejects_negative_values(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('negative');

        Amount::of('-0.01', 'EUR');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function malformedValues(): iterable
    {
        yield 'empty' => [''];
        yield 'text' => ['abc'];
        yield 'exponent' => ['1e3'];
        yield 'comma separator' => ['1,5'];
        yield 'leading dot' => ['.5'];
    }

    #[Test]
    #[DataProvider('malformedValues')]
    public function it_rejects_malformed_values(string $value): void
    {
        $this->expectException(InvalidAmountException::class);

        Amount::of($value, 'EUR');
    }

    #[Test]
    public function it_refuses_math_across_currencies(): void
    {
        $this->expectException(InvalidCurrencyException::class);
        $this->expectExceptionMessage('EUR and USD');

        Amount::of('1', 'EUR')->add(Amount::of('1', 'USD'));
    }

    #[Test]
    public function it_refuses_comparison_across_currencies(): void
    {
        $this->expectException(InvalidCurrencyException::class);

        Amount::of('1', 'EUR')->min(Amount::of('1', 'USD'));
    }

    #[Test]
    public function equality_includes_currency(): void
    {
        self::assertFalse(Amount::of('1', 'EUR')->equals(Amount::of('1', 'USD')));
        self::assertTrue(Amount::of('1', 'EUR')->currency()->equals(new Currency('EUR')));
    }
}
