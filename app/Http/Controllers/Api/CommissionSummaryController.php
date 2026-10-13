<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Commission\CommissionService;
use App\Domain\Money\Amount;
use App\Http\Requests\TransactionFilterRequest;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

final readonly class CommissionSummaryController
{
    public function __construct(private CommissionService $service) {}

    public function __invoke(TransactionFilterRequest $request): JsonResponse
    {
        $transactions = Transaction::query()
            ->filter($request->filters())
            ->orderBy('id')
            ->lazy()
            ->map(fn (Transaction $transaction) => $transaction->toData());

        $summary = $this->service->calculate($transactions);

        return api()->success([
            'transactions_count' => count($summary->results),
            'totals' => (object) array_map(static fn (Amount $total): string => $total->format(), $summary->totalsByCurrency()),
        ]);
    }
}
