<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Actions;

use Illuminate\Support\Facades\Cache;
use ReturnEarly\ActionsPattern\Interfaces\ActionsPatternInterface;
use ReturnEarly\ActionsPattern\Traits\ActionsPattern;
use ReturnEarly\Nominal\NominalClient;
use ReturnEarly\Nominal\Support\Slug;
use Throwable;

final readonly class EnsureHeartbeatMonitor implements ActionsPatternInterface
{
    use ActionsPattern;

    private const string TAG = 'laravel-nominal';

    private const string CACHE_PREFIX = 'nominal.heartbeat.registered.';

    public function __construct(
        private NominalClient $client,
    ) {}

    public function handle(string $name, string $slug, int $intervalSeconds): bool
    {
        $slug = Slug::sanitize($slug);
        $cacheKey = self::CACHE_PREFIX.$slug;

        try {
            if (Cache::get($cacheKey) === true) {
                return true;
            }

            if ($this->monitorExists($slug)) {
                Cache::put($cacheKey, true, now()->addDay());

                return true;
            }

            if (! $this->createMonitor($name, $slug, $intervalSeconds)) {
                return false;
            }

            Cache::put($cacheKey, true, now()->addDay());

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function monitorExists(string $slug): bool
    {
        $payload = $this->client->graphql(
            <<<'GRAPHQL'
            query MonitorsByTag($tag: String) {
              monitors(tag: $tag) {
                id
                heartbeat_slug
                heartbeat_slug_enabled
              }
            }
            GRAPHQL,
            ['tag' => self::TAG],
        );

        if ($payload === null) {
            return false;
        }

        /** @var list<array<string, mixed>>|null $monitors */
        $monitors = data_get($payload, 'data.monitors');

        if (! is_array($monitors)) {
            return false;
        }

        foreach ($monitors as $monitor) {
            if (($monitor['heartbeat_slug'] ?? null) === $slug) {
                return true;
            }
        }

        return false;
    }

    private function createMonitor(string $name, string $slug, int $intervalSeconds): bool
    {
        $payload = $this->client->graphql(
            <<<'GRAPHQL'
            mutation CreateHeartbeat($input: CreateMonitorInput!) {
              createMonitor(input: $input) {
                id
                heartbeat_slug
              }
            }
            GRAPHQL,
            [
                'input' => [
                    'name' => $name,
                    'type' => 'Heartbeat',
                    'target' => 'heartbeat',
                    'intervalSeconds' => max(60, $intervalSeconds),
                    'heartbeatSlug' => $slug,
                    'heartbeatSlugEnabled' => true,
                    'tags' => [self::TAG],
                ],
            ],
        );

        if ($payload === null) {
            return false;
        }

        if (data_get($payload, 'data.createMonitor.id')) {
            return true;
        }

        $errors = data_get($payload, 'errors', []);

        if (! is_array($errors)) {
            return false;
        }

        foreach ($errors as $error) {
            $message = is_array($error) ? (string) ($error['message'] ?? '') : (string) $error;

            if (str_contains(strtolower($message), 'already in use')
                || str_contains(strtolower($message), 'slug')) {
                return true;
            }
        }

        return false;
    }
}
