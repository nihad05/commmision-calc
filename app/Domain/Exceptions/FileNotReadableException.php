<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class FileNotReadableException extends CommissionException
{
    public static function at(string $path): self
    {
        return new self(sprintf('File "%s" does not exist or is not readable.', $path));
    }
}
