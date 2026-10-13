<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Exceptions\UnsupportedTransactionException;
use App\Domain\Transaction\TransactionData;

final readonly class CommissionResolver
{
    /**
     * @param  iterable<CommissionType>  $types
     */
    public function __construct(private iterable $types) {}

    public function resolve(TransactionData $transaction): CommissionType
    {
        foreach ($this->types as $type) {
            if ($type->supports($transaction)) {
                return $type;
            }
        }

        throw UnsupportedTransactionException::for($transaction);
    }
}
