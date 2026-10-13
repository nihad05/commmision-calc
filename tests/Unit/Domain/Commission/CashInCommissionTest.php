<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Commission;

use App\Domain\Commission\CashInCommission;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\UserType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\BuildsTransactionData;

final class CashInCommissionTest extends TestCase
{
    use BuildsTransactionData;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function fees(): iterable
    {
        yield 'normal 0.03%' => ['1000.00', '0.30'];
        yield 'rounded up to the next cent' => ['100.33', '0.04'];
        yield 'below the max' => ['10000.00', '3.00'];
        yield 'just below the max rounds up to it' => ['16666.66', '5.00'];
        yield 'just above the max is capped' => ['16666.6667', '5.00'];
        yield 'well above the max is capped' => ['20000.00', '5.00'];
        yield 'large amount is capped' => ['999999999999.9999', '5.00'];
    }

    #[Test]
    #[DataProvider('fees')]
    public function it_charges_0_03_percent_capped_at_5(string $amount, string $expectedFee): void
    {
        $fee = (new CashInCommission)->calculate(self::transaction(OperationType::CashIn, amount: $amount));

        self::assertSame($expectedFee, $fee->format());
        self::assertSame('EUR', $fee->currency()->code);
    }

    #[Test]
    public function it_supports_cash_in_for_every_user_type(): void
    {
        $rule = new CashInCommission;

        self::assertTrue($rule->supports(self::transaction(OperationType::CashIn, UserType::Private)));
        self::assertTrue($rule->supports(self::transaction(OperationType::CashIn, UserType::Business)));
        self::assertFalse($rule->supports(self::transaction(OperationType::CashOut)));
        self::assertFalse($rule->supports(self::transaction(OperationType::LoanRepayment)));
    }

    #[Test]
    public function rate_and_max_are_configurable(): void
    {
        $rule = new CashInCommission(rate: '0.01', max: '2');

        self::assertSame('1.00', $rule->calculate(self::transaction(OperationType::CashIn, amount: '100'))->format());
        self::assertSame('2.00', $rule->calculate(self::transaction(OperationType::CashIn, amount: '200'))->format());
        self::assertSame('2.00', $rule->calculate(self::transaction(OperationType::CashIn, amount: '1000'))->format());
    }
}
