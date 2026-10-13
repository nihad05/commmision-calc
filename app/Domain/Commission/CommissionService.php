<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Transaction\TransactionData;

final readonly class CommissionService
{
    public function __construct(private CommissionResolver $resolver) {}

    /**
     * @param  iterable<TransactionData>  $transactions
     */
    public function calculate(iterable $transactions): CommissionSummary
    {
        $results = [];

        foreach ($transactions as $transaction) {
            $type = $this->resolver->resolve($transaction);
            $results[] = new CommissionResult($transaction, $type->calculate($transaction), $type::class);
        }

        return new CommissionSummary($results);
    }
}
