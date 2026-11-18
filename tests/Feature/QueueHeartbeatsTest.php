<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use ReturnEarly\Nominal\Actions\DispatchQueueHeartbeats;
use ReturnEarly\Nominal\Jobs\SendQueueHeartbeatJob;

it('dispatches a heartbeat job onto each configured queue', function (): void {
    Queue::fake();

    $dispatched = DispatchQueueHeartbeats::make()->handle();

    expect($dispatched)->toBe(['default', 'mail']);

    Queue::assertPushedOn('default', SendQueueHeartbeatJob::class);
    Queue::assertPushedOn('mail', SendQueueHeartbeatJob::class);
});

it('auto-registers queue monitors when enabled', function (): void {
    config()->set('nominal.auto_register_queues', true);

    Http::fake([
        'https://nominal.test/graphql' => Http::sequence()
            ->push(['data' => ['monitors' => []]], 200)
            ->push(['data' => ['createMonitor' => ['id' => '1', 'heartbeat_slug' => 'default']]], 200)
            ->push(['data' => ['monitors' => []]], 200)
            ->push(['data' => ['createMonitor' => ['id' => '2', 'heartbeat_slug' => 'mail']]], 200),
    ]);

    Queue::fake();

    DispatchQueueHeartbeats::make()->handle();

    Http::assertSentCount(4);
    Queue::assertPushed(SendQueueHeartbeatJob::class, 2);
});

it('runs the artisan command successfully', function (): void {
    Queue::fake();

    $this->artisan('nominal:queue-heartbeats')
        ->expectsOutputToContain('Queued 2 Nominal heartbeat job(s).')
        ->assertSuccessful();
});

it('sends a simple ping from the queue job', function (): void {
    Http::fake([
        'https://nominal.test/api/heartbeat/default' => Http::response(['ok' => true], 200),
    ]);

    (new SendQueueHeartbeatJob('default'))->handle();

    Http::assertSent(fn ($request): bool => $request->url() === 'https://nominal.test/api/heartbeat/default');
});
