<?php

declare(strict_types=1);

namespace Yard\UserRoles\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setRoles()
 */
class UserRoles extends Facade
{
	/**
	 * Get the registered name of the component.
	 */
	protected static function getFacadeAccessor(): string
	{
		return \Yard\UserRoles\UserRoles::class;
	}
}
