<?php

declare(strict_types=1);

namespace App\Infrastructure\Import;

use App\Domain\Import\CsvTransactionParser;
use App\Domain\Transaction\TransactionData;
use App\Enums\ImportStatus;
use App\Models\ImportBatch;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Throwable;

final readonly class TransactionImporter
{
    private const int CHUNK_SIZE = 500;

    public function __construct(
        private CsvTransactionParser $parser,
        private ConnectionInterface $db,
    ) {}

    public function import(string $path, ?string $filename = null): ImportBatch
    {
        $batch = ImportBatch::query()->create([
            'filename' => $filename ?? basename($path),
            'status' => ImportStatus::Processing,
        ]);

        try {
            $count = $this->db->transaction(fn (): int => $this->store($path, $batch->id));
        } catch (Throwable $exception) {
            $batch->update(['status' => ImportStatus::Failed, 'error' => $exception->getMessage()]);

            throw $exception;
        }

        $batch->update(['status' => ImportStatus::Completed, 'rows_count' => $count]);

        return $batch;
    }

    private function store(string $path, int $batchId): int
    {
        $now = Carbon::now();
        $chunk = [];
        $count = 0;

        foreach ($this->parser->parse($path) as $transaction) {
            $chunk[] = $this->toRow($transaction, $batchId, $now);
            $count++;

            if (count($chunk) === self::CHUNK_SIZE) {
                $this->db->table('transactions')->insert($chunk);
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            $this->db->table('transactions')->insert($chunk);
        }

        return $count;
    }

    private function toRow(TransactionData $transaction, int $batchId, Carbon $now): array
    {
        return [
            'date' => $transaction->date->format('Y-m-d'),
            'user_type' => $transaction->userType->value,
            'operation_type' => $transaction->operationType->value,
            'amount' => $transaction->amount->value(),
            'currency' => $transaction->amount->currency()->code,
            'import_batch_id' => $batchId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
