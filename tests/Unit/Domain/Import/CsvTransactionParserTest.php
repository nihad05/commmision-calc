<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Import;

use App\Domain\Exceptions\FileNotReadableException;
use App\Domain\Exceptions\InvalidCsvHeaderException;
use App\Domain\Exceptions\InvalidCsvRowException;
use App\Domain\Import\CsvTransactionParser;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\UserType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsvTransactionParserTest extends TestCase
{
    #[Test]
    public function it_parses_every_row_keyed_by_its_line_number(): void
    {
        $rows = iterator_to_array($this->parser()->parse(self::path('tests/fixtures/valid.csv')));

        self::assertSame([2, 3, 5], array_keys($rows));

        $loan = $rows[5];
        self::assertSame('2026-01-07', $loan->date->format('Y-m-d'));
        self::assertSame(UserType::Private, $loan->userType);
        self::assertSame(OperationType::LoanRepayment, $loan->operationType);
        self::assertSame('100.50', $loan->amount->format());
        self::assertSame('EUR', $loan->amount->currency()->code);
        self::assertNull($loan->id);
    }

    #[Test]
    public function it_parses_the_bundled_sample_file(): void
    {
        self::assertCount(14, iterator_to_array($this->parser()->parse(self::path('storage/app/sample/transactions.csv'))));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function invalidRows(): iterable
    {
        yield 'bad date' => ['invalid_date.csv', 'date', '2026-13-01'];
        yield 'negative amount' => ['invalid_negative_amount.csv', 'amount', '-100.00'];
        yield 'non-numeric amount' => ['invalid_non_numeric_amount.csv', 'amount', 'abc'];
        yield 'too many decimals' => ['invalid_amount_precision.csv', 'amount', '100.12345'];
        yield 'unknown user type' => ['invalid_user_type.csv', 'user_type', 'vip'];
        yield 'unknown operation type' => ['invalid_operation_type.csv', 'operation_type', 'transfer'];
        yield 'bad currency' => ['invalid_currency.csv', 'currency', 'eu'];
        yield 'missing column' => ['invalid_missing_column.csv', 'row', '2026-01-06,private,cash_in,100.00'];
    }

    #[Test]
    #[DataProvider('invalidRows')]
    public function it_reports_the_line_field_and_value_of_an_invalid_row(string $fixture, string $field, string $value): void
    {
        try {
            iterator_to_array($this->parser()->parse(self::path("tests/fixtures/{$fixture}")));
            self::fail('Expected an InvalidCsvRowException.');
        } catch (InvalidCsvRowException $exception) {
            self::assertSame(3, $exception->lineNumber);
            self::assertSame($field, $exception->field);
            self::assertSame($value, $exception->value);
            self::assertStringContainsString('Line 3', $exception->getMessage());
            self::assertStringContainsString($field, $exception->getMessage());
        }
    }

    /**
     * @return iterable<string, array{string, class-string<\Throwable>, string}>
     */
    public static function invalidFiles(): iterable
    {
        yield 'wrong header' => ['tests/fixtures/invalid_header.csv', InvalidCsvHeaderException::class, 'Line 1'];
        yield 'empty file' => ['tests/fixtures/invalid_empty.csv', InvalidCsvHeaderException::class, 'empty'];
        yield 'missing file' => ['tests/fixtures/does_not_exist.csv', FileNotReadableException::class, 'does_not_exist.csv'];
    }

    /**
     * @param  class-string<\Throwable>  $exception
     */
    #[Test]
    #[DataProvider('invalidFiles')]
    public function it_rejects_unusable_files(string $path, string $exception, string $message): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        iterator_to_array($this->parser()->parse(self::path($path)));
    }

    private function parser(): CsvTransactionParser
    {
        return new CsvTransactionParser;
    }

    private static function path(string $relative): string
    {
        return dirname(__DIR__, 4).'/'.$relative;
    }
}
