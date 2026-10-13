<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\ImportTransactionsRequest;
use App\Infrastructure\Import\TransactionImporter;
use Illuminate\Http\JsonResponse;

final readonly class ImportTransactionsController
{
    public function __construct(private TransactionImporter $importer) {}

    public function __invoke(ImportTransactionsRequest $request): JsonResponse
    {
        $file = $request->csv();
        $batch = $this->importer->import($file->getRealPath(), $file->getClientOriginalName());

        return api()->success([
            'batch_id' => $batch->id,
            'filename' => $batch->filename,
            'rows_count' => $batch->rows_count,
            'status' => $batch->status->value,
        ], status: 201);
    }
}
