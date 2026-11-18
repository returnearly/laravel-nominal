<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Actions;

use ReturnEarly\ActionsPattern\Interfaces\ActionsPatternInterface;
use ReturnEarly\ActionsPattern\Traits\ActionsPattern;
use ReturnEarly\Nominal\Jobs\SendQueueHeartbeatJob;
use ReturnEarly\Nominal\Support\MonitorName;
use ReturnEarly\Nominal\Support\Slug;

final readonly class DispatchQueueHeartbeats implements ActionsPatternInterface
{
    use ActionsPattern;

    public function __construct(
        private EnsureHeartbeatMonitor $ensureHeartbeatMonitor,
    ) {}

    /**
     * @return list<string>
     */
    public function handle(): array
    {
        /** @var list<mixed> $queues */
        $queues = config('nominal.queues', []);
        $dispatched = [];
        $autoRegister = (bool) config('nominal.auto_register_queues', false);
        $interval = max(60, (int) config('nominal.queue_interval_seconds', 60));

        foreach ($queues as $queue) {
            if (! is_string($queue) || trim($queue) === '') {
                continue;
            }

            $slug = Slug::sanitize($queue);

            if ($autoRegister) {
                $this->ensureHeartbeatMonitor->handle(
                    MonitorName::format('queue:'.$queue),
                    $slug,
                    $interval,
                );
            }

            SendQueueHeartbeatJob::dispatch($queue)->onQueue($queue);
            $dispatched[] = $queue;
        }

        return $dispatched;
    }
}
