<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCsvRowException extends CommissionException
{
    public function __construct(
        public readonly int $lineNumber,
        public readonly string $field,
        public readonly string $value,
        string $reason,
    ) {
        parent::__construct(sprintf('Line %d, field "%s", value "%s": %s', $lineNumber, $field, $value, $reason));
    }

    public static function columnCount(int $line, int $expected, string $rawRow): self
    {
        return new self($line, 'row', $rawRow, sprintf('expected %d columns, got %d.', $expected, substr_count($rawRow, ',') + 1));
    }
}
