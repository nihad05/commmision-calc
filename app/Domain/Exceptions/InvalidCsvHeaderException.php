<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCsvHeaderException extends CommissionException
{
    public static function emptyFile(): self
    {
        return new self('The file is empty, a header row is required.');
    }

    /**
     * @param  list<string>  $expected
     * @param  list<string|null>  $actual
     */
    public static function mismatch(array $expected, array $actual): self
    {
        return new self(sprintf(
            'Line 1: expected header "%s", got "%s".',
            implode(',', $expected),
            implode(',', $actual),
        ));
    }
}
