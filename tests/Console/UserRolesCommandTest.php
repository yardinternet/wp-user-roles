<?php

declare(strict_types=1);

use Facades\Yard\UserRoles\UserRoles;

it('creates roles once ', function () {
	UserRoles::shouldReceive('createRoles')->once();

	$this->artisan('roles:create')
		->expectsOutput('Updating roles...')
		->expectsOutput('All done!')
		->assertExitCode(0);
});
