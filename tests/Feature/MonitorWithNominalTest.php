<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;

it('registers start finish and error hooks via monitorWithNominal', function (): void {
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
        'https://nominal.test/api/heartbeat/send-reports/start' => Http::response(['ok' => true, 'started' => true], 200),
        'https://nominal.test/api/heartbeat/send-reports/finish' => Http::response(['ok' => true, 'success' => true], 200),
        'https://nominal.test/api/heartbeat/send-reports/error' => Http::response(['ok' => true, 'success' => false], 200),
    ]);

    /** @var Schedule $schedule */
    $schedule = app(Schedule::class);

    $event = $schedule->call(fn () => null)
        ->hourly()
        ->monitorWithNominal('Send reports');

    $event->callBeforeCallbacks(app());

    setEventExitCode($event, 0);
    $event->callAfterCallbacks(app());

    setEventExitCode($event, 1);
    $event->callAfterCallbacks(app());

    Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/send-reports/start'));
    Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/send-reports/finish'));
    Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/send-reports/error'));
});

it('does not throw when nominal is down during schedule hooks', function (): void {
    Http::fake([
        'https://nominal.test/*' => function () {
            throw new RuntimeException('offline');
        },
    ]);

    /** @var Schedule $schedule */
    $schedule = app(Schedule::class);

    $event = $schedule->call(fn () => null)
        ->everyMinute()
        ->monitorWithNominal('Fragile task');

    $event->callBeforeCallbacks(app());

    setEventExitCode($event, 0);
    $event->callAfterCallbacks(app());

    expect(true)->toBeTrue();
});

function setEventExitCode(Event $event, int $exitCode): void
{
    $reflection = new ReflectionProperty(Event::class, 'exitCode');
    $reflection->setValue($event, $exitCode);
}
