<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\TransactionData;

final readonly class CashInCommission implements CommissionType
{
    /**
     * @param  numeric-string  $rate
     * @param  numeric-string  $max
     */
    public function __construct(
        private string $rate = '0.0003',
        private string $max = '5',
        private FeeRounder $rounder = new FeeRounder,
    ) {}

    public function supports(TransactionData $transaction): bool
    {
        return $transaction->operationType === OperationType::CashIn;
    }

    public function calculate(TransactionData $transaction): Amount
    {
        $amount = $transaction->amount;
        $fee = $amount->multiply($this->rate)->min(new Amount($this->max, $amount->currency()));

        return $this->rounder->round($fee);
    }
}
