<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

use App\Domain\Money\Amount;
use DateTimeImmutable;

final readonly class TransactionData
{
    public function __construct(
        public ?int $id,
        public DateTimeImmutable $date,
        public UserType $userType,
        public OperationType $operationType,
        public Amount $amount,
    ) {}
}
