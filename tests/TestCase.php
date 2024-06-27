<?php

declare(strict_types=1);

namespace Yard\SkeletonPackage\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        \WP_Mock::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

       \WP_Mock::tearDown();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            'Yard\SkeletonPackage\SkeletonPackageServiceProvider',
        ];
    }
}
