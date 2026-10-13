<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\UserType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
final class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('2026-01-01', '2026-12-31')->format('Y-m-d'),
            'user_type' => fake()->randomElement(UserType::cases()),
            'operation_type' => fake()->randomElement(OperationType::cases()),
            'amount' => Amount::of(bcdiv((string) fake()->numberBetween(100, 1_000_000), '100', 2), fake()->randomElement(['EUR', 'USD'])),
        ];
    }

    public function cashIn(): self
    {
        return $this->state(['operation_type' => OperationType::CashIn]);
    }

    public function cashOut(): self
    {
        return $this->state(['operation_type' => OperationType::CashOut]);
    }

    public function loanRepayment(): self
    {
        return $this->state(['operation_type' => OperationType::LoanRepayment]);
    }

    public function private(): self
    {
        return $this->state(['user_type' => UserType::Private]);
    }

    public function business(): self
    {
        return $this->state(['user_type' => UserType::Business]);
    }

    public function on(string $date): self
    {
        return $this->state(['date' => $date]);
    }

    public function amount(string $value, string $currency = 'EUR'): self
    {
        return $this->state(['amount' => Amount::of($value, $currency)]);
    }
}
