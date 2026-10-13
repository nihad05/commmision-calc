<?php

declare(strict_types=1);

namespace App\Domain\Import;

use App\Domain\Exceptions\FileNotReadableException;
use App\Domain\Exceptions\InvalidCsvHeaderException;
use App\Domain\Exceptions\InvalidCsvRowException;
use App\Domain\Money\Amount;
use App\Domain\Transaction\OperationType;
use App\Domain\Transaction\TransactionData;
use App\Domain\Transaction\UserType;
use BackedEnum;
use DateTimeImmutable;
use Generator;
use SplFileObject;

final readonly class CsvTransactionParser
{
    public const array HEADER = ['date', 'user_type', 'operation_type', 'amount', 'currency'];

    private const string AMOUNT_PATTERN = '/^\d{1,12}(\.\d{1,4})?$/';

    private const string CURRENCY_PATTERN = '/^[A-Z]{3}$/';

    /**
     * @return Generator<int, TransactionData>
     */
    public function parse(string $path): Generator
    {
        $file = $this->open($path);
        $headerSeen = false;

        foreach ($file as $index => $row) {
            /** @var list<string|null> $row */
            if (! $headerSeen) {
                $this->assertHeader($row);
                $headerSeen = true;

                continue;
            }

            $line = (int) $index + 1;

            yield $line => $this->toTransaction($line, $row);
        }

        if (! $headerSeen) {
            throw InvalidCsvHeaderException::emptyFile();
        }
    }

    private function open(string $path): SplFileObject
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw FileNotReadableException::at($path);
        }

        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '');

        return $file;
    }

    /**
     * @param  list<string|null>  $row
     */
    private function assertHeader(array $row): void
    {
        $header = array_map(static fn (?string $cell): string => trim((string) $cell, " \t\u{FEFF}"), $row);

        if ($header !== self::HEADER) {
            throw InvalidCsvHeaderException::mismatch(self::HEADER, $row);
        }
    }

    /**
     * @param  list<string|null>  $row
     */
    private function toTransaction(int $line, array $row): TransactionData
    {
        $cells = array_map(static fn (?string $cell): string => trim((string) $cell), $row);

        if (count($cells) !== count(self::HEADER)) {
            throw InvalidCsvRowException::columnCount($line, count(self::HEADER), implode(',', $cells));
        }

        [$date, $userType, $operationType, $amount, $currency] = $cells;

        return new TransactionData(
            id: null,
            date: $this->date($line, $date),
            userType: $this->enum($line, 'user_type', $userType, UserType::class),
            operationType: $this->enum($line, 'operation_type', $operationType, OperationType::class),
            amount: $this->amount($line, $amount, $currency),
        );
    }

    private function date(int $line, string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidCsvRowException($line, 'date', $value, 'must be a valid date in Y-m-d format.');
        }

        return $date;
    }

    /**
     * @template T of BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return T
     */
    private function enum(int $line, string $field, string $value, string $enum): BackedEnum
    {
        return $enum::tryFrom($value) ?? throw new InvalidCsvRowException(
            $line,
            $field,
            $value,
            sprintf('must be one of: %s.', implode(', ', array_map(static fn (BackedEnum $case): string => (string) $case->value, $enum::cases()))),
        );
    }

    private function amount(int $line, string $value, string $currency): Amount
    {
        if (! is_numeric($value) || preg_match(self::AMOUNT_PATTERN, $value) !== 1 || bccomp($value, '0', Amount::SCALE) <= 0) {
            throw new InvalidCsvRowException($line, 'amount', $value, 'must be a positive decimal with at most 12 integer and 4 fractional digits.');
        }

        if (preg_match(self::CURRENCY_PATTERN, $currency) !== 1) {
            throw new InvalidCsvRowException($line, 'currency', $currency, 'must be a 3-letter uppercase ISO 4217 code.');
        }

        return Amount::of($value, $currency);
    }
}
