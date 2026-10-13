<?php

declare(strict_types=1);

return [
    'cash_in' => [
        'rate' => env('COMMISSION_CASH_IN_RATE', '0.0003'),
        'max' => env('COMMISSION_CASH_IN_MAX', '5'),
    ],
    'cash_out_private' => [
        'rate' => env('COMMISSION_CASH_OUT_PRIVATE_RATE', '0.003'),
        'min' => env('COMMISSION_CASH_OUT_PRIVATE_MIN', '0.5'),
    ],
    'cash_out_business' => [
        'rate' => env('COMMISSION_CASH_OUT_BUSINESS_RATE', '0.005'),
    ],
    'loan_repayment' => [
        'rate' => env('COMMISSION_LOAN_REPAYMENT_RATE', '0.02'),
        'fixed' => env('COMMISSION_LOAN_REPAYMENT_FIXED', '1'),
    ],
];
