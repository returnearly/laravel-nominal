<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Console;

use Illuminate\Console\Command;
use ReturnEarly\Nominal\Actions\DispatchQueueHeartbeats;

final class QueueHeartbeatsCommand extends Command
{
    protected $signature = 'nominal:queue-heartbeats';

    protected $description = 'Dispatch Nominal heartbeat jobs onto each configured queue.';

    public function handle(DispatchQueueHeartbeats $dispatchQueueHeartbeats): int
    {
        $dispatched = $dispatchQueueHeartbeats->handle();

        if ($dispatched === []) {
            $this->warn('No queues configured in config/nominal.php.');

            return self::SUCCESS;
        }

        foreach ($dispatched as $queue) {
            $this->line("Dispatched heartbeat job on [{$queue}].");
        }

        $this->info('Queued '.count($dispatched).' Nominal heartbeat job(s).');

        return self::SUCCESS;
    }
}
