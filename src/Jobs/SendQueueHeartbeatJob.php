<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use ReturnEarly\Nominal\Actions\SendQueueHeartbeat;

final class SendQueueHeartbeatJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public string $queueName,
    ) {}

    public function handle(): void
    {
        SendQueueHeartbeat::make()->handle($this->queueName);
    }
}
