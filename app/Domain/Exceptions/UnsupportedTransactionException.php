<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Transaction\TransactionData;

final class UnsupportedTransactionException extends CommissionException
{
    public static function for(TransactionData $transaction): self
    {
        return new self(sprintf(
            'No commission rule supports %s transactions of %s users%s.',
            $transaction->operationType->value,
            $transaction->userType->value,
            $transaction->id === null ? '' : sprintf(' (transaction #%d)', $transaction->id),
        ));
    }
}
