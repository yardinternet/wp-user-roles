<?php

declare(strict_types=1);

use Yard\UserRoles\Facades\UserRoles;

it('calls setRoles on UserRoles and outputs messages', function () {
	UserRoles::shouldReceive('setRoles')->once();

	$this->artisan('roles:create')
		->expectsOutput('Updating roles...')
		->expectsOutput('All done!')
		->assertExitCode(0);
});
