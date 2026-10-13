<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Commission;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaction
 */
final class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'user_type' => $this->user_type->value,
            'operation_type' => $this->operation_type->value,
            'amount' => $this->amount->format(4),
            'currency' => $this->amount->currency()->code,
            'import_batch_id' => $this->import_batch_id,
            'commission' => $this->whenLoaded('commission', fn (): ?array => $this->commission instanceof Commission ? [
                'fee' => $this->commission->fee->format(),
                'currency' => $this->commission->fee->currency()->code,
                'calculated_at' => $this->commission->calculated_at->toIso8601String(),
            ] : null),
        ];
    }
}
