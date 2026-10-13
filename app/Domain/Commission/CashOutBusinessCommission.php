<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\TransactionData;
use App\Domain\Transaction\UserType;

final readonly class CashOutBusinessCommission implements CommissionType
{
    /**
     * @param  numeric-string  $rate
     */
    public function __construct(
        private string $rate = '0.005',
        private FeeRounder $rounder = new FeeRounder,
    ) {}

    public function supports(TransactionData $transaction): bool
    {
        return $transaction->operationType === OperationType::CashOut
            && $transaction->userType === UserType::Business;
    }

    public function calculate(TransactionData $transaction): Amount
    {
        return $this->rounder->round($transaction->amount->multiply($this->rate));
    }
}
