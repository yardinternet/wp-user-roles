<?php

declare(strict_types=1);

namespace Yard\UserRoles\Console;

use Illuminate\Console\Command;
use Yard\UserRoles\Facades\UserRoles;

class UserRolesCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'roles:create';

	/**
	 * The console command description.
	 */
	protected $description = '(Re-)Create, Update and Delete user roles';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$this->info('Updating roles...');

		$this->createRolesForSites();

		$this->info('All done!');
	}

	private function createRolesForSites(): void
	{
		UserRoles::createRoles();
	}
}
