<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Commission;

use App\Domain\Commission\CashOutBusinessCommission;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\UserType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\BuildsTransactionData;

final class CashOutBusinessCommissionTest extends TestCase
{
    use BuildsTransactionData;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function fees(): iterable
    {
        yield 'normal 0.5%' => ['1000.00', '5.00'];
        yield 'small amount has no minimum' => ['1.00', '0.01'];
        yield 'rounded up to the next cent' => ['6.02', '0.04'];
        yield 'large amount has no maximum' => ['1000000000.00', '5000000.00'];
    }

    #[Test]
    #[DataProvider('fees')]
    public function it_charges_a_flat_0_5_percent(string $amount, string $expectedFee): void
    {
        $fee = (new CashOutBusinessCommission)->calculate(self::transaction(OperationType::CashOut, UserType::Business, $amount));

        self::assertSame($expectedFee, $fee->format());
    }

    #[Test]
    public function it_supports_only_business_cash_out(): void
    {
        $rule = new CashOutBusinessCommission;

        self::assertTrue($rule->supports(self::transaction(OperationType::CashOut, UserType::Business)));
        self::assertFalse($rule->supports(self::transaction(OperationType::CashOut, UserType::Private)));
        self::assertFalse($rule->supports(self::transaction(OperationType::LoanRepayment, UserType::Business)));
    }

    #[Test]
    public function rate_is_configurable(): void
    {
        $rule = new CashOutBusinessCommission(rate: '0.1');

        self::assertSame('10.00', $rule->calculate(self::transaction(OperationType::CashOut, UserType::Business, '100'))->format());
    }
}
