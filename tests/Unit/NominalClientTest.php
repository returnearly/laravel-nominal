<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use ReturnEarly\Nominal\Enums\HeartbeatSignal;
use ReturnEarly\Nominal\NominalClient;

it('builds heartbeat and graphql urls from the prefix', function (): void {
    $client = new NominalClient;

    expect($client->heartbeatUrl('default'))->toBe('https://nominal.test/api/heartbeat/default')
        ->and($client->heartbeatUrl('default', HeartbeatSignal::Start))->toBe('https://nominal.test/api/heartbeat/default/start')
        ->and($client->graphqlUrl())->toBe('https://nominal.test/graphql');
});

it('pings with configured headers and swallows failures', function (): void {
    Http::fake([
        'https://nominal.test/api/heartbeat/default' => Http::response(['ok' => true], 200),
        'https://nominal.test/api/heartbeat/broken' => Http::response('nope', 500),
        'https://nominal.test/api/heartbeat/down' => function () {
            throw new RuntimeException('network down');
        },
    ]);

    $client = new NominalClient;

    expect($client->ping('default'))->toMatchArray(['ok' => true])
        ->and($client->ping('broken'))->toBeNull()
        ->and($client->ping('down'))->toBeNull();

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://nominal.test/api/heartbeat/default'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request->hasHeader('X-Custom', 'yes');
    });
});
