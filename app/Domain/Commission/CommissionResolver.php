<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Exceptions\CommissionException;
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

        throw self::unsupported($transaction);
    }

    private static function unsupported(TransactionData $transaction): CommissionException
    {
        return new CommissionException(sprintf(
            'No commission rule supports %s transactions of %s users%s.',
            $transaction->operationType->value,
            $transaction->userType->value,
            $transaction->id === null ? '' : sprintf(' (transaction #%d)', $transaction->id),
        ));
    }
}
