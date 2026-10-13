<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

enum OperationType: string
{
    case CashIn = 'cash_in';
    case CashOut = 'cash_out';
    case LoanRepayment = 'loan_repayment';
}
