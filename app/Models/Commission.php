<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AmountCast;
use App\Domain\Money\Amount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $transaction_id
 * @property Amount $fee
 * @property string $commission_type
 * @property CarbonImmutable $calculated_at
 */
final class Commission extends Model
{
    public $timestamps = false;

    protected $fillable = ['transaction_id', 'fee', 'commission_type', 'calculated_at'];

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    protected function casts(): array
    {
        return [
            'fee' => AmountCast::class,
            'calculated_at' => 'immutable_datetime',
        ];
    }
}
