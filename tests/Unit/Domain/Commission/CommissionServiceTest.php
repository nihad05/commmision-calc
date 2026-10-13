<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Commission;

use App\Domain\Commission\CashInCommission;
use App\Domain\Commission\CashOutBusinessCommission;
use App\Domain\Commission\CashOutPrivateCommission;
use App\Domain\Commission\CommissionResolver;
use App\Domain\Commission\CommissionService;
use App\Domain\Commission\LoanRepaymentCommission;
use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\UserType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\BuildsTransactionData;

final class CommissionServiceTest extends TestCase
{
    use BuildsTransactionData;

    private CommissionService $service;

    protected function setUp(): void
    {
        $this->service = new CommissionService(new CommissionResolver([
            new CashInCommission,
            new CashOutPrivateCommission,
            new CashOutBusinessCommission,
            new LoanRepaymentCommission,
        ]));
    }

    #[Test]
    public function it_returns_a_fee_per_transaction_and_totals_per_currency(): void
    {
        $summary = $this->service->calculate([
            self::transaction(OperationType::CashIn, amount: '1000', currency: 'EUR'),
            self::transaction(OperationType::CashOut, UserType::Private, '10', 'USD'),
            self::transaction(OperationType::CashOut, UserType::Business, '1000', 'EUR'),
            self::transaction(OperationType::LoanRepayment, UserType::Business, '100', 'USD'),
        ]);

        self::assertSame(
            ['0.30', '0.50', '5.00', '3.00'],
            array_map(static fn ($result): string => $result->fee->format(), $summary->results),
        );
        self::assertSame(
            [CashInCommission::class, CashOutPrivateCommission::class, CashOutBusinessCommission::class, LoanRepaymentCommission::class],
            array_map(static fn ($result): string => $result->commissionType, $summary->results),
        );
        self::assertSame(
            ['EUR' => '5.30', 'USD' => '3.50'],
            array_map(static fn (Amount $total): string => $total->format(), $summary->totalsByCurrency()),
        );
    }

    #[Test]
    public function an_empty_input_gives_an_empty_summary(): void
    {
        $summary = $this->service->calculate([]);

        self::assertSame([], $summary->results);
        self::assertSame([], $summary->totalsByCurrency());
    }

    #[Test]
    public function it_accepts_a_lazy_iterable(): void
    {
        $transactions = (static function () {
            yield self::transaction(OperationType::CashIn, amount: '100');
            yield self::transaction(OperationType::CashIn, amount: '100');
        })();

        self::assertSame('0.06', $this->service->calculate($transactions)->totalsByCurrency()['EUR']->format());
    }
}
