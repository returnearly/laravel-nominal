<?php

declare(strict_types=1);

use ReturnEarly\Nominal\Support\MonitorName;

it('formats cron names from the config pattern', function (): void {
    expect(MonitorName::format('Send reports'))->toBe('DemoApp - testing - Send reports');
});
