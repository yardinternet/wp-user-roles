<?php

declare(strict_types=1);

use Yard\UserRoles\UserRoles;

function mock_WP_Roles(array $roles): WP_Roles
{
	$WP_Roles = Mockery::mock(WP_Roles::class);

	$WP_Roles->roles = $roles;

	return $WP_Roles;
}

function mock_empty_WP_Roles(): void
{
	WP_Mock::userFunction('wp_roles', [
		'times' => 1,
		'return' => mock_WP_Roles([]),
	]);
}

it('aborts if no prefix is found in config', function () {
	$WP_CLI = Mockery::mock(WP_CLI::class);
	$WP_CLI->shouldReceive('error')
		->once()
		->with('No prefix found in configuration file. Aborting role creation.')
		->andThrow(new WP_CLI\ExitException('No prefix found in configuration file. Aborting role creation.'));

	new UserRoles([], Mockery::mock(Role_Command::class), $WP_CLI);
})->throws(WP_CLI\ExitException::class, 'No prefix found in configuration file. Aborting role creation.');

it('does not remove any custom roles when they are not prefixed', function () {
	mock_empty_WP_Roles();

	$Role_Command = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();
	$WP_CLI = Mockery::mock(WP_CLI::class)->shouldIgnoreMissing();

	$Role_Command->shouldNotReceive('delete');
	$WP_CLI->shouldReceive('warning')
		->once()
		->with("No custom roles with prefix 'yard_' found in database. Skipping custom role deletion.");

	$userRoles = new UserRoles(['prefix' => 'yard'], $Role_Command, $WP_CLI);

	$userRoles->createRoles();
});

it('removes custom roles with the right prefix', function () {
	$WP_Roles = mock_WP_Roles([
		'yard_superuser' => [],
		'my_prefix_visitor' => [],
	]);

	WP_Mock::userFunction('wp_roles', [
		'times' => 1,
		'return' => $WP_Roles,
	]);

	$Role_Command = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$Role_Command->shouldReceive('delete')
		->once()
		->with(['yard_superuser']);

	$Role_Command->shouldNotReceive('delete')
		->with(['my_prefix_visitor']);

	$userRoles = new UserRoles(['prefix' => 'yard'], $Role_Command, new WP_CLI);

	$userRoles->createRoles();
});

it('removes core roles marked for deletion', function () {
	mock_empty_WP_Roles();

	$Role_Command = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$config = [
		'prefix' => 'yard',
		'core_roles' => [
			'administrator' => true,
			'editor' => false,
			'author' => false,
		],
	];

	$Role_Command->shouldNotReceive('delete')
		->with(['administrator']);

	$Role_Command->shouldReceive('delete')
		->once()
		->with(['editor']);

	$Role_Command->shouldReceive('delete')
		->once()
		->with(['author']);

	$userRoles = new UserRoles($config, $Role_Command, new WP_CLI);

	$userRoles->createRoles();
});

it('creates custom role from config', function () {
	mock_empty_WP_Roles();

	$Role_Command = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$Role_Command->shouldReceive('create')
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

	$userRoles = new UserRoles($config, $Role_Command, new WP_CLI);

	$WP_Role = Mockery::mock(WP_Role::class);

	$WP_Role->shouldReceive('add_cap')
		->once()
		->with('my_custom_cap', true);

	WP_Mock::userFunction('get_role', [
		'times' => 1,
		'return' => $WP_Role,
	]);

	$userRoles->createRoles();
});

it('skips roles without display name', function () {
	mock_empty_WP_Roles();

	$Role_Command = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$Role_Command->shouldNotReceive('create');

	$config = [
		'prefix' => 'yard',
		'roles' => [
			'superuser' => [],
		],
	];

	$WP_CLI = Mockery::mock(WP_CLI::class)->shouldIgnoreMissing();

	$WP_CLI->shouldReceive('warning')
		->once()
		->with('No display name configured for role superuser. Skipping role creation.');

	$userRoles = new UserRoles($config, $Role_Command, $WP_CLI);

	$userRoles->createRoles();
});
