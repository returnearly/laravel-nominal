<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ReturnEarly\Nominal\NominalServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            NominalServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('app.name', 'DemoApp');
        $app['config']->set('app.env', 'testing');
        $app['config']->set('nominal', [
            'url_prefix' => 'https://nominal.test/api/heartbeat',
            'headers' => [
                'Authorization' => 'Bearer test-token',
                'X-Custom' => 'yes',
            ],
            'queues' => ['default', 'mail'],
            'auto_register_queues' => false,
            'queue_interval_seconds' => 60,
            'cron_name_pattern' => '{app} - {env} - {cron}',
            'timeout_seconds' => 5,
        ]);
    }
}
