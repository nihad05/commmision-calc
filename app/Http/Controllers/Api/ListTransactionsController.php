<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\TransactionFilterRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

final readonly class ListTransactionsController
{
    public function __invoke(TransactionFilterRequest $request): JsonResponse
    {
        $transactions = Transaction::query()
            ->filter($request->filters())
            ->with('commission')
            ->orderBy('date')
            ->orderBy('id')
            ->paginate($request->perPage())
            ->through(fn (Transaction $transaction): array => (new TransactionResource($transaction))->resolve($request));

        return api()->paginated($transactions);
    }
}
