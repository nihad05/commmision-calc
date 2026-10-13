<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Money\Amount;
use App\Domain\Transaction\TransactionData;

interface CommissionType
{
    public function supports(TransactionData $transaction): bool;

    public function calculate(TransactionData $transaction): Amount;
}
