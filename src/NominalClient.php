<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ReturnEarly\Nominal\Enums\HeartbeatSignal;
use Throwable;

final class NominalClient
{
    /**
     * @return array<string, mixed>|null
     */
    public function ping(string $slug, ?HeartbeatSignal $signal = null): ?array
    {
        $url = $this->heartbeatUrl($slug, $signal);

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->post($url);

            if ($response->failed()) {
                Log::warning('Nominal heartbeat ping failed.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            /** @var array<string, mixed> $json */
            $json = $response->json() ?? [];

            return $json;
        } catch (Throwable $exception) {
            Log::warning('Nominal heartbeat ping errored.', [
                'url' => $url,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>|null
     */
    public function graphql(string $query, array $variables = []): ?array
    {
        $url = $this->graphqlUrl();

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'query' => $query,
                    'variables' => $variables,
                ]);

            if ($response->failed()) {
                Log::warning('Nominal GraphQL request failed.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            /** @var array<string, mixed> $payload */
            $payload = $response->json() ?? [];

            if (isset($payload['errors']) && is_array($payload['errors']) && $payload['errors'] !== []) {
                Log::warning('Nominal GraphQL returned errors.', [
                    'url' => $url,
                    'errors' => $payload['errors'],
                ]);
            }

            return $payload;
        } catch (Throwable $exception) {
            Log::warning('Nominal GraphQL request errored.', [
                'url' => $url,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function heartbeatUrl(string $slug, ?HeartbeatSignal $signal = null): string
    {
        $prefix = rtrim((string) config('nominal.url_prefix'), '/');
        $url = $prefix.'/'.ltrim($slug, '/');

        if ($signal instanceof HeartbeatSignal) {
            $url .= '/'.$signal->value;
        }

        return $url;
    }

    public function graphqlUrl(): string
    {
        $prefix = rtrim((string) config('nominal.url_prefix'), '/');
        $base = preg_replace('#/api/heartbeat$#', '', $prefix) ?? $prefix;

        return rtrim($base, '/').'/graphql';
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        /** @var array<array-key, mixed> $headers */
        $headers = config('nominal.headers', []);

        $normalized = [];

        foreach ($headers as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (is_string($value) || is_int($value) || is_float($value)) {
                $normalized[$key] = (string) $value;
            }
        }

        return $normalized;
    }

    private function timeout(): int
    {
        return max(1, (int) config('nominal.timeout_seconds', 5));
    }
}
