<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use ReturnEarly\Nominal\Actions\PingHeartbeat;
use ReturnEarly\Nominal\Enums\HeartbeatSignal;

it('sanitizes the slug and pings nominal', function (): void {
    Http::fake([
        'https://nominal.test/api/heartbeat/checks-local/start' => Http::response(['ok' => true, 'started' => true], 200),
    ]);

    $result = PingHeartbeat::make()->handle('checks.local', HeartbeatSignal::Start);

    expect($result)->toMatchArray(['ok' => true, 'started' => true]);
});
