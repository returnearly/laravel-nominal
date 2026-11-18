<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Actions;

use ReturnEarly\ActionsPattern\Interfaces\ActionsPatternInterface;
use ReturnEarly\ActionsPattern\Traits\ActionsPattern;
use ReturnEarly\Nominal\Enums\HeartbeatSignal;
use ReturnEarly\Nominal\Support\Slug;

final readonly class FailScheduledHeartbeat implements ActionsPatternInterface
{
    use ActionsPattern;

    public function __construct(
        private PingHeartbeat $pingHeartbeat,
    ) {}

    public function handle(string $label): void
    {
        $this->pingHeartbeat->handle(Slug::sanitize($label), HeartbeatSignal::Error);
    }
}
