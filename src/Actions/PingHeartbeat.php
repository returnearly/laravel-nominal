<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Actions;

use ReturnEarly\ActionsPattern\Interfaces\ActionsPatternInterface;
use ReturnEarly\ActionsPattern\Traits\ActionsPattern;
use ReturnEarly\Nominal\Enums\HeartbeatSignal;
use ReturnEarly\Nominal\NominalClient;
use ReturnEarly\Nominal\Support\Slug;

final readonly class PingHeartbeat implements ActionsPatternInterface
{
    use ActionsPattern;

    public function __construct(
        private NominalClient $client,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function handle(string $slug, ?HeartbeatSignal $signal = null): ?array
    {
        return $this->client->ping(Slug::sanitize($slug), $signal);
    }
}
