<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\TransactionData;

final readonly class LoanRepaymentCommission implements CommissionType
{
    /**
     * @param  numeric-string  $rate
     * @param  numeric-string  $fixed
     */
    public function __construct(
        private string $rate = '0.02',
        private string $fixed = '1',
        private FeeRounder $rounder = new FeeRounder,
    ) {}

    public function supports(TransactionData $transaction): bool
    {
        return $transaction->operationType === OperationType::LoanRepayment;
    }

    public function calculate(TransactionData $transaction): Amount
    {
        $amount = $transaction->amount;
        $fee = $amount->multiply($this->rate)->add(new Amount($this->fixed, $amount->currency()));

        return $this->rounder->round($fee);
    }
}
