<?php

declare(strict_types=1);

namespace Yard\SkeletonPackage;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Yard\SkeletonPackage\Console\ExampleCommand;

class SkeletonPackageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('skeleton-package')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(ExampleCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('Example', fn () => new Example($this->app));
    }

    public function packageBooted(): void
    {
        $this->app->make('Example');
    }
}
