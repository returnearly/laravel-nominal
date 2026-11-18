<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Actions;

use ReturnEarly\ActionsPattern\Interfaces\ActionsPatternInterface;
use ReturnEarly\ActionsPattern\Traits\ActionsPattern;
use ReturnEarly\Nominal\Enums\HeartbeatSignal;
use ReturnEarly\Nominal\Support\MonitorName;
use ReturnEarly\Nominal\Support\Slug;

final readonly class StartScheduledHeartbeat implements ActionsPatternInterface
{
    use ActionsPattern;

    public function __construct(
        private EnsureHeartbeatMonitor $ensureHeartbeatMonitor,
        private PingHeartbeat $pingHeartbeat,
    ) {}

    public function handle(string $label, int $intervalSeconds): void
    {
        $slug = Slug::sanitize($label);
        $name = MonitorName::format($label);

        $this->ensureHeartbeatMonitor->handle($name, $slug, $intervalSeconds);
        $this->pingHeartbeat->handle($slug, HeartbeatSignal::Start);
    }
}
