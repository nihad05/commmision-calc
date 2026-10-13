<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $filename
 * @property int $rows_count
 * @property ImportStatus $status
 * @property string|null $error
 */
final class ImportBatch extends Model
{
    protected $fillable = ['filename', 'rows_count', 'status', 'error'];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    protected function casts(): array
    {
        return [
            'rows_count' => 'integer',
            'status' => ImportStatus::class,
        ];
    }
}
