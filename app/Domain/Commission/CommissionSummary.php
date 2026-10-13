<?php

declare(strict_types=1);

namespace App\Domain\Commission;

use App\Domain\Money\Amount;

final readonly class CommissionSummary
{
    /**
     * @param  list<CommissionResult>  $results
     */
    public function __construct(public array $results) {}

    /**
     * @return array<string, Amount>
     */
    public function totalsByCurrency(): array
    {
        $totals = [];

        foreach ($this->results as $result) {
            $code = $result->fee->currency()->code;
            $totals[$code] = isset($totals[$code]) ? $totals[$code]->add($result->fee) : $result->fee;
        }

        ksort($totals);

        return $totals;
    }
}
