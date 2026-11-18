# Laravel Nominal

[![Latest Version on Packagist](https://img.shields.io/packagist/v/returnearly/laravel-nominal.svg?style=flat-square)](https://packagist.org/packages/returnearly/laravel-nominal)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/returnearly/laravel-nominal/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/returnearly/laravel-nominal/actions?query=workflow%3Arun-tests+branch%3Amaster)

Ping [Nominal](https://staynominal.com) heartbeats from Laravel queues and scheduled tasks.

- Dispatch a heartbeat job onto each configured queue (`nominal:queue-heartbeats`)
- Wrap schedule entries with `->monitorWithNominal()` (auto-register + start/finish/error)
- Fail-soft: Nominal outages never fail your jobs or crons

Business logic lives in Actions via [`returnearly/actions-pattern`](https://github.com/returnearly/actions-pattern).

## Requirements

- PHP 8.4 or 8.5
- Laravel 12 or 13

## Installation

```bash
composer require returnearly/laravel-nominal
```

Publish the config:

```bash
php artisan vendor:publish --tag="nominal-config"
```

## Configuration

`config/nominal.php`:

```php
return [
    'url_prefix' => env('NOMINAL_URL_PREFIX', 'https://nominal.example/api/heartbeat'),

    'headers' => [
        // 'Authorization' => 'Bearer '.env('NOMINAL_TOKEN'),
        // 'CF-Access-Client-Id' => env('CF_ACCESS_CLIENT_ID'),
        // 'CF-Access-Client-Secret' => env('CF_ACCESS_CLIENT_SECRET'),
    ],

    'queues' => [
        // 'default',
        // 'mail',
    ],

    'auto_register_queues' => env('NOMINAL_AUTO_REGISTER_QUEUES', false),
    'queue_interval_seconds' => (int) env('NOMINAL_QUEUE_INTERVAL_SECONDS', 60),
    'cron_name_pattern' => env('NOMINAL_CRON_NAME_PATTERN', '{app} - {env} - {cron}'),
    'timeout_seconds' => 5,
];
```

- Heartbeat URLs: `{url_prefix}/{slug}` (and `/start`, `/finish`, `/error` for crons)
- GraphQL base is derived by stripping `/api/heartbeat` from `url_prefix`
- Auth is headers-only (no dedicated API token key)

In Nominal, create heartbeat monitors whose **slug** matches each sanitized queue name (or enable `auto_register_queues`).

## Queue heartbeats

Schedule the command (for example every minute):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('nominal:queue-heartbeats')->everyMinute();
```

Or run it manually:

```bash
php artisan nominal:queue-heartbeats
```

The command dispatches `SendQueueHeartbeatJob` onto each configured queue. When a worker processes that queue, the job POSTs `{url_prefix}/{sanitized-queue-name}`.

## Schedule monitoring

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('reports:send')
    ->hourly()
    ->monitorWithNominal(); // name defaults to description/command

Schedule::call(fn () => null)
    ->dailyAt('02:00')
    ->name('Nightly cleanup')
    ->monitorWithNominal('Nightly cleanup');
```

On each run the package:

1. Ensures a Nominal heartbeat exists (GraphQL `createMonitor`) named from `cron_name_pattern` (default `{app} - {env} - {cron}`)
2. Sets `interval_seconds` from the cron expression
3. Pings `/start`, then `/finish` or `/error`

Failures talking to Nominal are logged and ignored so the scheduled task still runs (and still surfaces its own exceptions).

## Architecture

| Layer | Classes |
| --- | --- |
| Entrypoints | `QueueHeartbeatsCommand`, `SendQueueHeartbeatJob`, `MonitorsWithNominal` |
| Actions | `DispatchQueueHeartbeats`, `SendQueueHeartbeat`, `PingHeartbeat`, `EnsureHeartbeatMonitor`, `StartScheduledHeartbeat`, `FinishScheduledHeartbeat`, `FailScheduledHeartbeat` |
| Collaborators | `NominalClient`, `Slug`, `CronInterval`, `MonitorName` |

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more details.
