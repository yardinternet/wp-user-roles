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
		$this->info('Updating user roles...');

		$this->createRolesForSites();

		$this->info('All done!');
	}

	private function createRolesForSites(): void
	{
		if (true === is_multisite()) {
			$this->info('Multisite detected. Let\'s create roles for all sites.');

			foreach (get_sites(['fields' => 'ids']) as $siteId) {
				switch_to_blog($siteId);

				$this->info(PHP_EOL . 'Switched to blog: ' . get_bloginfo('name'));

				UserRoles::createRoles();
			}
		} else {
			UserRoles::createRoles();
		}
	}
}
