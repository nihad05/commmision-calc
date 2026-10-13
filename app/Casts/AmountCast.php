<?php

declare(strict_types=1);

namespace App\Casts;

use App\Domain\Money\Amount;
use App\Domain\Money\Currency;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<Amount, Amount>
 */
final class AmountCast implements CastsAttributes
{
    public function __construct(private readonly string $currencyColumn = 'currency') {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Amount
    {
        if ($value === null) {
            return null;
        }

        return new Amount((string) $value, new Currency((string) $attributes[$this->currencyColumn]));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (! $value instanceof Amount) {
            throw new InvalidArgumentException(sprintf('The "%s" attribute must be set with an %s instance.', $key, Amount::class));
        }

        return [
            $key => $value->value(),
            $this->currencyColumn => $value->currency()->code,
        ];
    }
}
