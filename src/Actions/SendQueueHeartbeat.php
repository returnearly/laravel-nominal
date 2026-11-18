<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Actions;

use ReturnEarly\ActionsPattern\Interfaces\ActionsPatternInterface;
use ReturnEarly\ActionsPattern\Traits\ActionsPattern;
use ReturnEarly\Nominal\Support\Slug;

final readonly class SendQueueHeartbeat implements ActionsPatternInterface
{
    use ActionsPattern;

    public function __construct(
        private PingHeartbeat $pingHeartbeat,
    ) {}

    public function handle(string $queue): void
    {
        $this->pingHeartbeat->handle(Slug::sanitize($queue));
    }
}
