<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Money\Amount;
use App\Domain\Transaction\TransactionData;

final readonly class CommissionResult
{
    /**
     * @param  class-string<CommissionType>  $commissionType
     */
    public function __construct(
        public TransactionData $transaction,
        public Amount $fee,
        public string $commissionType,
    ) {}
}
