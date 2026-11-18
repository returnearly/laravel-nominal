<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Support;

use Cron\CronExpression;
use DateTimeImmutable;
use Illuminate\Console\Scheduling\Event;
use Throwable;

final class CronInterval
{
    /**
     * Estimate Nominal interval_seconds from a Schedule event's cron expression.
     */
    public static function fromEvent(Event $event): int
    {
        return self::fromExpression((string) $event->expression);
    }

    public static function fromExpression(string $expression): int
    {
        $expression = trim($expression);

        if ($expression === '') {
            return 60;
        }

        try {
            $cron = new CronExpression($expression);
            $now = new DateTimeImmutable('now');
            $previous = DateTimeImmutable::createFromMutable($cron->getPreviousRunDate($now, 0, true));
            $next = DateTimeImmutable::createFromMutable($cron->getNextRunDate($now, 0, true));
            $seconds = $next->getTimestamp() - $previous->getTimestamp();

            return max(60, $seconds);
        } catch (Throwable) {
            return 60;
        }
    }
}
