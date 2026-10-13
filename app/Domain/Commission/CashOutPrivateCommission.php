<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\TransactionData;
use App\Domain\Transaction\UserType;

final readonly class CashOutPrivateCommission implements CommissionType
{
    /**
     * @param  numeric-string  $rate
     * @param  numeric-string  $min
     */
    public function __construct(
        private string $rate = '0.003',
        private string $min = '0.5',
        private FeeRounder $rounder = new FeeRounder,
    ) {}

    public function supports(TransactionData $transaction): bool
    {
        return $transaction->operationType === OperationType::CashOut
            && $transaction->userType === UserType::Private;
    }

    public function calculate(TransactionData $transaction): Amount
    {
        $amount = $transaction->amount;
        $fee = $amount->multiply($this->rate)->max(new Amount($this->min, $amount->currency()));

        return $this->rounder->round($fee);
    }
}
