<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Heartbeat URL prefix
    |--------------------------------------------------------------------------
    |
    | Base URL for Nominal heartbeat pings. Queue and cron slugs are appended:
    | {url_prefix}/{slug} and optionally /{start|finish|error}.
    |
    */

    'url_prefix' => env('NOMINAL_URL_PREFIX', 'https://nominal.example/api/heartbeat'),

    /*
    |--------------------------------------------------------------------------
    | HTTP headers
    |--------------------------------------------------------------------------
    |
    | Applied to both heartbeat pings and GraphQL auto-registration requests.
    | Put Authorization, Cloudflare Access, or other proxy headers here.
    |
    */

    'headers' => [
        // 'Authorization' => 'Bearer '.env('NOMINAL_TOKEN'),
        // 'CF-Access-Client-Id' => env('CF_ACCESS_CLIENT_ID'),
        // 'CF-Access-Client-Secret' => env('CF_ACCESS_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue heartbeats
    |--------------------------------------------------------------------------
    |
    | Queue names that receive a SendQueueHeartbeatJob when you run
    | `php artisan nominal:queue-heartbeats`. Each job pings
    | {url_prefix}/{sanitized-queue-name}.
    |
    */

    'queues' => [
        // 'default',
        // 'mail',
    ],

    'auto_register_queues' => (bool) env('NOMINAL_AUTO_REGISTER_QUEUES', false),

    'queue_interval_seconds' => (int) env('NOMINAL_QUEUE_INTERVAL_SECONDS', 60),

    /*
    |--------------------------------------------------------------------------
    | Cron monitor naming
    |--------------------------------------------------------------------------
    |
    | Placeholders: {app} = config('app.name'), {env} = app()->environment(),
    | {cron} = monitor name or schedule description.
    |
    */

    'cron_name_pattern' => env('NOMINAL_CRON_NAME_PATTERN', '{app} - {env} - {cron}'),

    'timeout_seconds' => (int) env('NOMINAL_TIMEOUT_SECONDS', 5),

];
