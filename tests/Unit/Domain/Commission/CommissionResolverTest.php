<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Commission;

use App\Domain\Commission\CashInCommission;
use App\Domain\Commission\CashOutBusinessCommission;
use App\Domain\Commission\CashOutPrivateCommission;
use App\Domain\Commission\CommissionResolver;
use App\Domain\Commission\CommissionType;
use App\Domain\Commission\LoanRepaymentCommission;
use App\Domain\Exceptions\CommissionException;
use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\TransactionData;
use App\Domain\Transaction\UserType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\BuildsTransactionData;

final class CommissionResolverTest extends TestCase
{
    use BuildsTransactionData;

    /**
     * @return iterable<string, array{OperationType, UserType, class-string<CommissionType>}>
     */
    public static function combinations(): iterable
    {
        yield 'private cash in' => [OperationType::CashIn, UserType::Private, CashInCommission::class];
        yield 'business cash in' => [OperationType::CashIn, UserType::Business, CashInCommission::class];
        yield 'private cash out' => [OperationType::CashOut, UserType::Private, CashOutPrivateCommission::class];
        yield 'business cash out' => [OperationType::CashOut, UserType::Business, CashOutBusinessCommission::class];
        yield 'private loan repayment' => [OperationType::LoanRepayment, UserType::Private, LoanRepaymentCommission::class];
        yield 'business loan repayment' => [OperationType::LoanRepayment, UserType::Business, LoanRepaymentCommission::class];
    }

    /**
     * @param  class-string<CommissionType>  $expected
     */
    #[Test]
    #[DataProvider('combinations')]
    public function it_picks_the_rule_for_each_combination(OperationType $operation, UserType $user, string $expected): void
    {
        $resolver = new CommissionResolver([
            new CashInCommission,
            new CashOutPrivateCommission,
            new CashOutBusinessCommission,
            new LoanRepaymentCommission,
        ]);

        self::assertInstanceOf($expected, $resolver->resolve(self::transaction($operation, $user)));
    }

    #[Test]
    public function it_throws_when_no_rule_supports_the_transaction(): void
    {
        $resolver = new CommissionResolver([new CashInCommission]);

        $this->expectException(CommissionException::class);
        $this->expectExceptionMessage('cash_out');

        $resolver->resolve(self::transaction(OperationType::CashOut, UserType::Business));
    }

    #[Test]
    public function a_new_rule_plugs_in_without_changing_existing_code(): void
    {
        $vipCashOut = new class implements CommissionType
        {
            public function supports(TransactionData $transaction): bool
            {
                return $transaction->operationType === OperationType::CashOut
                    && $transaction->amount->currency()->code === 'CHF';
            }

            public function calculate(TransactionData $transaction): Amount
            {
                return new Amount('0', $transaction->amount->currency());
            }
        };

        $resolver = new CommissionResolver([$vipCashOut, new CashOutPrivateCommission]);

        self::assertSame($vipCashOut, $resolver->resolve(self::transaction(OperationType::CashOut, currency: 'CHF')));
        self::assertInstanceOf(CashOutPrivateCommission::class, $resolver->resolve(self::transaction(OperationType::CashOut)));
    }
}
