<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TransactionFilterRequest extends FormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'user_type' => ['nullable', Rule::enum(UserType::class)],
            'operation_type' => ['nullable', Rule::enum(OperationType::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array{from?: string|null, to?: string|null, user_type?: string|null, operation_type?: string|null}
     */
    public function filters(): array
    {
        /** @var array{from?: string|null, to?: string|null, user_type?: string|null, operation_type?: string|null} */
        return $this->safe()->only(['from', 'to', 'user_type', 'operation_type']);
    }

    public function perPage(): int
    {
        return $this->integer('per_page', 15);
    }
}
