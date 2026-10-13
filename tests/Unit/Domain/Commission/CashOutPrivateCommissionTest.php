<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Commission;

use App\Domain\Commission\CashOutPrivateCommission;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\UserType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\BuildsTransactionData;

final class CashOutPrivateCommissionTest extends TestCase
{
    use BuildsTransactionData;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function fees(): iterable
    {
        yield 'normal 0.3%' => ['1000.00', '3.00'];
        yield 'rounded up to the next cent' => ['200.01', '0.61'];
        yield 'below the min is raised' => ['10.00', '0.50'];
        yield 'just below the min is raised' => ['166.00', '0.50'];
        yield 'just above the min rounds up' => ['166.6667', '0.51'];
        yield 'above the min' => ['170.00', '0.51'];
        yield 'large amount' => ['1000000000.00', '3000000.00'];
    }

    #[Test]
    #[DataProvider('fees')]
    public function it_charges_0_3_percent_with_a_min_of_0_5(string $amount, string $expectedFee): void
    {
        $fee = (new CashOutPrivateCommission)->calculate(self::transaction(OperationType::CashOut, UserType::Private, $amount));

        self::assertSame($expectedFee, $fee->format());
    }

    #[Test]
    public function it_supports_only_private_cash_out(): void
    {
        $rule = new CashOutPrivateCommission;

        self::assertTrue($rule->supports(self::transaction(OperationType::CashOut, UserType::Private)));
        self::assertFalse($rule->supports(self::transaction(OperationType::CashOut, UserType::Business)));
        self::assertFalse($rule->supports(self::transaction(OperationType::CashIn, UserType::Private)));
    }

    #[Test]
    public function rate_and_min_are_configurable(): void
    {
        $rule = new CashOutPrivateCommission(rate: '0.1', min: '20');

        self::assertSame('20.00', $rule->calculate(self::transaction(OperationType::CashOut, amount: '100'))->format());
        self::assertSame('20.00', $rule->calculate(self::transaction(OperationType::CashOut, amount: '200'))->format());
        self::assertSame('100.00', $rule->calculate(self::transaction(OperationType::CashOut, amount: '1000'))->format());
    }
}
