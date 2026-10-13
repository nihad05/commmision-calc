<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Commission\CashInCommission;
use App\Domain\Commission\CashOutBusinessCommission;
use App\Domain\Commission\CashOutPrivateCommission;
use App\Domain\Commission\CommissionResolver;
use App\Domain\Commission\CommissionType;
use App\Domain\Commission\LoanRepaymentCommission;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class CommissionServiceProvider extends ServiceProvider
{
    public const string TAG = 'commission.types';

    /**
     * @var array<class-string<CommissionType>, string>
     */
    private const array TYPES = [
        CashInCommission::class => 'cash_in',
        CashOutPrivateCommission::class => 'cash_out_private',
        CashOutBusinessCommission::class => 'cash_out_business',
        LoanRepaymentCommission::class => 'loan_repayment',
    ];

    public function register(): void
    {
        foreach (self::TYPES as $type => $configKey) {
            $this->app->bind($type, fn (): CommissionType => new $type(...$this->settings($configKey)));
        }

        $this->app->tag(array_keys(self::TYPES), self::TAG);

        $this->app->bind(
            CommissionResolver::class,
            fn (Application $app): CommissionResolver => new CommissionResolver($app->tagged(self::TAG)),
        );
    }

    /**
     * @return array<string, numeric-string>
     */
    private function settings(string $configKey): array
    {
        $settings = config()->array("commission.{$configKey}");

        foreach ($settings as $name => $value) {
            if (! is_string($name) || ! is_numeric($value)) {
                throw new InvalidArgumentException(sprintf('Config "commission.%s.%s" must be numeric.', $configKey, $name));
            }

            $settings[$name] = (string) $value;
        }

        /** @var array<string, numeric-string> $settings */
        return $settings;
    }
}
