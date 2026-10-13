<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Commission\CommissionResult;
use App\Domain\Commission\CommissionSummary;
use App\Models\Commission;
use Illuminate\Support\Carbon;
use LogicException;

final readonly class CommissionRecorder
{
    private const int CHUNK_SIZE = 500;

    public function record(CommissionSummary $summary): void
    {
        $now = Carbon::now();
        $rows = array_map(fn (CommissionResult $result): array => $this->toRow($result, $now), $summary->results);

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            Commission::query()->upsert($chunk, ['transaction_id'], ['fee', 'currency', 'commission_type', 'calculated_at']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(CommissionResult $result, Carbon $now): array
    {
        $transactionId = $result->transaction->id
            ?? throw new LogicException('Only stored transactions can have their commission recorded.');

        return [
            'transaction_id' => $transactionId,
            'fee' => $result->fee->value(),
            'currency' => $result->fee->currency()->code,
            'commission_type' => $result->commissionType,
            'calculated_at' => $now,
        ];
    }
}
