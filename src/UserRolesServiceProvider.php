<?php

declare(strict_types=1);

namespace Yard\UserRoles;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Yard\UserRoles\Console\UserRolesCommand;

class UserRolesServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		$package
			->name('wp-user-roles')
			->hasConfigFile('user-roles')
			->hasCommand(UserRolesCommand::class);
	}

	public function packageRegistered(): void
	{
		$this->app->singleton(UserRoles::class, fn () => new UserRoles(new \Role_Command));
	}
}
