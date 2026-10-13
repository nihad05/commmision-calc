<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Commission;

use App\Domain\Commission\LoanRepaymentCommission;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\UserType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\BuildsTransactionData;

final class LoanRepaymentCommissionTest extends TestCase
{
    use BuildsTransactionData;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function fees(): iterable
    {
        yield 'normal 2% plus 1' => ['100.00', '3.00'];
        yield 'tiny amount is mostly the fixed part' => ['0.01', '1.01'];
        yield 'rounded up to the next cent' => ['100.005', '3.01'];
        yield 'large amount' => ['1000000.00', '20001.00'];
    }

    #[Test]
    #[DataProvider('fees')]
    public function it_charges_2_percent_plus_1(string $amount, string $expectedFee): void
    {
        $fee = (new LoanRepaymentCommission)->calculate(self::transaction(OperationType::LoanRepayment, amount: $amount));

        self::assertSame($expectedFee, $fee->format());
    }

    #[Test]
    public function it_supports_loan_repayment_for_every_user_type(): void
    {
        $rule = new LoanRepaymentCommission;

        self::assertTrue($rule->supports(self::transaction(OperationType::LoanRepayment, UserType::Private)));
        self::assertTrue($rule->supports(self::transaction(OperationType::LoanRepayment, UserType::Business)));
        self::assertFalse($rule->supports(self::transaction(OperationType::CashIn)));
    }

    #[Test]
    public function rate_and_fixed_part_are_configurable(): void
    {
        $rule = new LoanRepaymentCommission(rate: '0.1', fixed: '2.5');

        self::assertSame('12.50', $rule->calculate(self::transaction(OperationType::LoanRepayment, amount: '100'))->format());
    }
}
