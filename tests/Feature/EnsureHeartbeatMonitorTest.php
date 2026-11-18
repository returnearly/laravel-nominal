<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use ReturnEarly\Nominal\Actions\EnsureHeartbeatMonitor;

it('creates a heartbeat monitor when missing', function (): void {
    Http::fake([
        'https://nominal.test/graphql' => Http::sequence()
            ->push(['data' => ['monitors' => []]], 200)
            ->push([
                'data' => [
                    'createMonitor' => [
                        'id' => '1',
                        'heartbeat_slug' => 'send-reports',
                    ],
                ],
            ], 200),
    ]);

    expect(EnsureHeartbeatMonitor::make()->handle('DemoApp - testing - Send reports', 'send-reports', 3600))
        ->toBeTrue();

    expect(Cache::get('nominal.heartbeat.registered.send-reports'))->toBeTrue();

    Http::assertSentCount(2);
});

it('skips create when the slug already exists', function (): void {
    Http::fake([
        'https://nominal.test/graphql' => Http::response([
            'data' => [
                'monitors' => [
                    [
                        'id' => '9',
                        'heartbeat_slug' => 'default',
                        'heartbeat_slug_enabled' => true,
                    ],
                ],
            ],
        ], 200),
    ]);

    expect(EnsureHeartbeatMonitor::make()->handle('DemoApp - testing - queue:default', 'default', 60))
        ->toBeTrue();

    Http::assertSentCount(1);
});

it('treats slug conflicts as success', function (): void {
    Http::fake([
        'https://nominal.test/graphql' => Http::sequence()
            ->push(['data' => ['monitors' => []]], 200)
            ->push([
                'errors' => [
                    ['message' => 'That heartbeat slug is already in use.'],
                ],
            ], 200),
    ]);

    expect(EnsureHeartbeatMonitor::make()->handle('Demo', 'taken', 60))->toBeTrue();
});

it('never throws when graphql is unreachable', function (): void {
    Http::fake([
        'https://nominal.test/graphql' => function () {
            throw new RuntimeException('boom');
        },
    ]);

    expect(EnsureHeartbeatMonitor::make()->handle('Demo', 'offline', 60))->toBeFalse();
});
