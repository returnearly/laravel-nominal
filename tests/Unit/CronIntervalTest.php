<?php

declare(strict_types=1);

use ReturnEarly\Nominal\Support\CronInterval;

it('maps hourly cron to about one hour', function (): void {
    expect(CronInterval::fromExpression('0 * * * *'))->toBe(3600);
});

it('maps every-minute cron to at least sixty seconds', function (): void {
    expect(CronInterval::fromExpression('* * * * *'))->toBe(60);
});

it('falls back to sixty seconds for invalid expressions', function (): void {
    expect(CronInterval::fromExpression('not-a-cron'))->toBe(60);
});
