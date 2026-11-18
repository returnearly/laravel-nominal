<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Scheduling;

use Illuminate\Console\Scheduling\Event;
use ReturnEarly\Nominal\Actions\FailScheduledHeartbeat;
use ReturnEarly\Nominal\Actions\FinishScheduledHeartbeat;
use ReturnEarly\Nominal\Actions\StartScheduledHeartbeat;
use ReturnEarly\Nominal\Support\CronInterval;
use Throwable;

final class MonitorsWithNominal
{
    public static function registerMacro(): void
    {
        Event::macro('monitorWithNominal', function (?string $name = null): Event {
            /** @var Event $event */
            $event = $this;

            $label = $name
                ?? (is_string($event->description) && $event->description !== '' ? $event->description : null)
                ?? MonitorsWithNominal::fallbackLabel($event);

            $intervalSeconds = CronInterval::fromEvent($event);

            $event->before(function () use ($label, $intervalSeconds): void {
                try {
                    StartScheduledHeartbeat::make()->handle($label, $intervalSeconds);
                } catch (Throwable) {
                    // Fail-soft: never block the scheduled task.
                }
            });

            $event->onSuccess(function () use ($label): void {
                try {
                    FinishScheduledHeartbeat::make()->handle($label);
                } catch (Throwable) {
                    // Fail-soft.
                }
            });

            $event->onFailure(function () use ($label): void {
                try {
                    FailScheduledHeartbeat::make()->handle($label);
                } catch (Throwable) {
                    // Fail-soft.
                }
            });

            return $event;
        });
    }

    private static function fallbackLabel(Event $event): string
    {
        if (is_string($event->command) && $event->command !== '') {
            return $event->command;
        }

        return 'schedule';
    }
}
