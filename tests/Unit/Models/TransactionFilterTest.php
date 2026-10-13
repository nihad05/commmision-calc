<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Transaction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TransactionFilterTest extends TestCase
{
    /**
     * @return iterable<string, array{array<string, string|null>, string, list<string>}>
     */
    public static function filters(): iterable
    {
        yield 'no filters' => [[], '', []];
        yield 'empty values are ignored' => [['from' => null, 'user_type' => null], '', []];
        yield 'from date (inclusive)' => [['from' => '2026-01-01'], ' where `date` >= ?', ['2026-01-01']];
        yield 'to date (inclusive)' => [['to' => '2026-01-31'], ' where `date` <= ?', ['2026-01-31']];
        yield 'date range' => [
            ['from' => '2026-01-01', 'to' => '2026-01-31'],
            ' where `date` >= ? and `date` <= ?',
            ['2026-01-01', '2026-01-31'],
        ];
        yield 'user type' => [['user_type' => 'business'], ' where `user_type` = ?', ['business']];
        yield 'operation type' => [['operation_type' => 'cash_out'], ' where `operation_type` = ?', ['cash_out']];
        yield 'all combined' => [
            ['from' => '2026-01-01', 'to' => '2026-01-31', 'user_type' => 'private', 'operation_type' => 'cash_in'],
            ' where `date` >= ? and `date` <= ? and `user_type` = ? and `operation_type` = ?',
            ['2026-01-01', '2026-01-31', 'private', 'cash_in'],
        ];
    }

    /**
     * @param  array{from?: string|null, to?: string|null, user_type?: string|null, operation_type?: string|null}  $filters
     * @param  list<string>  $bindings
     */
    #[Test]
    #[DataProvider('filters')]
    public function it_builds_the_where_clause_from_the_given_filters(array $filters, string $where, array $bindings): void
    {
        $query = Transaction::query()->filter($filters);

        self::assertSame('select * from `transactions`'.$where, $query->toSql());
        self::assertSame($bindings, $query->getBindings());
    }
}
