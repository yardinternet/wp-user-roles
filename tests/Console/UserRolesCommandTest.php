<?php

declare(strict_types=1);

use Facades\Yard\UserRoles\UserRoles;

it('creates roles once when not in multisite', function () {
	WP_Mock::userFunction('is_multisite', [
		'return' => false,
	]);

	UserRoles::shouldReceive('createRoles')->once();

	$this->artisan('roles:create')
		->expectsOutput('Updating roles...')
		->expectsOutput('All done!')
		->assertExitCode(0);
});

it('creates roles for each site when in multisite', function () {
	WP_Mock::userFunction('is_multisite', [
		'return' => true,
	]);

	WP_Mock::userFunction('get_sites', [
		'return' => [1, 2, 3],
	]);

	WP_Mock::userFunction('switch_to_blog', [
		'times' => 3,
	]);

	UserRoles::shouldReceive('createRoles')->times(3);

	$this->artisan('roles:create')
		->expectsOutput('Updating roles...')
		->expectsOutput('All done!')
		->assertExitCode(0);
});
