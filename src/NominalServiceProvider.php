<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal;

use ReturnEarly\Nominal\Console\QueueHeartbeatsCommand;
use ReturnEarly\Nominal\Scheduling\MonitorsWithNominal;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NominalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('nominal')
            ->hasConfigFile()
            ->hasCommand(QueueHeartbeatsCommand::class);
    }

    public function packageBooted(): void
    {
        MonitorsWithNominal::registerMacro();
    }
}
