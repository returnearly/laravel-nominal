<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Support;

final class MonitorName
{
    public static function format(string $cronLabel): string
    {
        /** @var string $pattern */
        $pattern = config('nominal.cron_name_pattern', '{app} - {env} - {cron}');

        return str_replace(
            ['{app}', '{env}', '{cron}'],
            [
                (string) config('app.name', 'Laravel'),
                app()->environment(),
                $cronLabel,
            ],
            $pattern,
        );
    }
}
