<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

enum UserType: string
{
    case Private = 'private';
    case Business = 'business';
}
