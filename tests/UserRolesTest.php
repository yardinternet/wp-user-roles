<?php

declare(strict_types=1);

use Yard\UserRoles\UserRoles;

function mockWpRoles(array $roles): WP_Roles
{
	$wpRoles = Mockery::mock(WP_Roles::class);

	$wpRoles->roles = $roles;

	return $wpRoles;
}

function mockEmptyCurrentRoles(): void
{
	WP_Mock::userFunction('wp_roles', [
		'times' => 1,
		'return' => mockWpRoles([]),
	]);
}

it('aborts if no prefix is found in config', function () {
	$wpCli = Mockery::mock(WP_CLI::class);
	$wpCli->shouldReceive('error')
		->once()
		->with('No prefix found in configuration file. Aborting role creation.')
		->andThrow(new WP_CLI\ExitException('No prefix found in configuration file. Aborting role creation.'));

	new UserRoles([], Mockery::mock(Role_Command::class), $wpCli);
})->throws(WP_CLI\ExitException::class, 'No prefix found in configuration file. Aborting role creation.');

it('does not remove any custom roles when they are not prefixed', function () {
	mockEmptyCurrentRoles();

	$roleCommand = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();
	$wpCli = Mockery::mock(WP_CLI::class)->shouldIgnoreMissing();

	$roleCommand->shouldNotReceive('delete');
	$wpCli->shouldReceive('warning')
		->once()
		->with("No custom roles with prefix 'yard_' found in database. Skipping custom role deletion.");

	$userRoles = new UserRoles(['prefix' => 'yard'], $roleCommand, $wpCli);

	$userRoles->createRoles();
});

it('removes custom roles with the right prefix', function () {
	$wpRoles = mockWpRoles([
		'yard_superuser' => [],
		'my_prefix_visitor' => [],
	]);

	WP_Mock::userFunction('wp_roles', [
		'times' => 1,
		'return' => $wpRoles,
	]);

	$roleCommand = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$roleCommand->shouldReceive('delete')
		->once()
		->with(['yard_superuser']);

	$roleCommand->shouldNotReceive('delete')
		->with(['my_prefix_visitor']);

	$userRoles = new UserRoles(['prefix' => 'yard'], $roleCommand, new WP_CLI);

	$userRoles->createRoles();
});

it('removes core roles marked for deletion', function () {
	mockEmptyCurrentRoles();

	$roleCommand = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$config = [
		'prefix' => 'yard',
		'core_roles' => [
			'administrator' => true,
			'editor' => false,
			'author' => false,
		],
	];

	$roleCommand->shouldNotReceive('delete')
		->with(['administrator']);

	$roleCommand->shouldReceive('delete')
		->once()
		->with(['editor']);

	$roleCommand->shouldReceive('delete')
		->once()
		->with(['author']);

	$userRoles = new UserRoles($config, $roleCommand, new WP_CLI);

	$userRoles->createRoles();
});

it('creates custom role from config', function () {
	mockEmptyCurrentRoles();

	$roleCommand = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$roleCommand->shouldReceive('create')
		->once()
		->with(['yard_superuser', 'Superuser'], []);

	$config = [
		'prefix' => 'yard',
		'roles' => [
			'superuser' => [
				'display_name' => 'Superuser',
				'caps' => [
					'my_custom_cap',
				],
			],
		],
	];

	$userRoles = new UserRoles($config, $roleCommand, new WP_CLI);

	$wpRole = Mockery::mock(WP_Role::class);

	$wpRole->shouldReceive('add_cap')
		->once()
		->with('my_custom_cap', true);

	WP_Mock::userFunction('get_role', [
		'times' => 1,
		'return' => $wpRole,
	]);

	$userRoles->createRoles();
});

it('skips roles without display name', function () {
	mockEmptyCurrentRoles();

	$roleCommand = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$roleCommand->shouldNotReceive('create');

	$config = [
		'prefix' => 'yard',
		'roles' => [
			'superuser' => [],
		],
	];

	$wpCli = Mockery::mock(WP_CLI::class)->shouldIgnoreMissing();

	$wpCli->shouldReceive('warning')
		->once()
		->with('No display name configured for role superuser. Skipping role creation.');

	$userRoles = new UserRoles($config, $roleCommand, $wpCli);

	$userRoles->createRoles();
});
