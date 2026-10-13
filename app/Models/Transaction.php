<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AmountCast;
use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\TransactionData;
use App\Domain\Transaction\UserType;
use Carbon\CarbonImmutable;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property CarbonImmutable $date
 * @property UserType $user_type
 * @property OperationType $operation_type
 * @property Amount $amount
 * @property int|null $import_batch_id
 */
final class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = ['date', 'user_type', 'operation_type', 'amount', 'import_batch_id'];

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('date', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('date', '<=', $to))
            ->when($filters['user_type'] ?? null, fn (Builder $query, string $userType) => $query->where('user_type', $userType))
            ->when($filters['operation_type'] ?? null, fn (Builder $query, string $operationType) => $query->where('operation_type', $operationType));
    }

    public function toData(): TransactionData
    {
        return new TransactionData(
            id: $this->id,
            date: $this->date->toDateTimeImmutable(),
            userType: $this->user_type,
            operationType: $this->operation_type,
            amount: $this->amount,
        );
    }

    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
            'user_type' => UserType::class,
            'operation_type' => OperationType::class,
            'amount' => AmountCast::class,
        ];
    }
}
