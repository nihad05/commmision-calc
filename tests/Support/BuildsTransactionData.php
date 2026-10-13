<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\TransactionData;
use App\Domain\Transaction\UserType;
use DateTimeImmutable;

trait BuildsTransactionData
{
    private static function transaction(
        OperationType $operationType,
        UserType $userType = UserType::Private,
        string $amount = '100',
        string $currency = 'EUR',
    ): TransactionData {
        return new TransactionData(
            id: null,
            date: new DateTimeImmutable('2026-01-15'),
            userType: $userType,
            operationType: $operationType,
            amount: Amount::of($amount, $currency),
        );
    }
}
