<?php

declare(strict_types=1);

namespace Yard\SkeletonPackage\Facades;

use Illuminate\Support\Facades\Facade;

class Example extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Example';
    }
}
