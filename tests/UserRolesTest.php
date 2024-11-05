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

describe('create roles', function () {
	beforeEach(function () {
		// mock WP_CLI
		$this->wpCli = Mockery::mock(WP_CLI::class)->shouldIgnoreMissing();
		
		// delete custom roles
		mockEmptyCurrentRoles();

		// reset core roles
		$roleCommand = Mockery::mock(Role_Command::class);
		$roleCommand->shouldReceive('reset')
			->once()
			->with([], ['all' => true]);
		$this->roleCommand = $roleCommand;

		// get created role
		$this->wpRole = Mockery::mock(WP_Role::class);
		WP_Mock::userFunction('get_role', [
			'return' => $this->wpRole,
		]);
	});

	it('skips creating roles without display name', function () {
		// ARRANGE //
		$config = [
			'prefix' => 'yard',
			'roles' => [
				'superuser' => [],
			],
		];

		// EXPECT //
		$this->roleCommand->shouldNotReceive('create');
		$this->wpCli->shouldReceive('warning')
			->once()
			->with('No display name configured for role superuser. Skipping role creation.');

		// ACT //
		(new UserRoles($config, $this->roleCommand, $this->wpCli))
			->createRoles();
	});

	it('can create a custom role from config', function () {
		// ARRANGE //
		$config = [
			'prefix' => 'yard',
			'roles' => [
				'superuser' => [
					'display_name' => 'Superuser',
				],
			],
		];

		// EXPECT //
		$this->roleCommand->shouldReceive('create')
			->once()
			->with(['yard_superuser', 'Superuser'], []);

		// ACT //
		(new UserRoles($config, $this->roleCommand, new WP_CLI))
			->createRoles();
	});

	it('can add capabilities to custom role', function () {
		// ARRANGE //
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

		// EXPECT //
		$this->roleCommand->shouldReceive('create')
			->once()
			->with(['yard_superuser', 'Superuser'], []);

		$this->wpRole->shouldReceive('add_cap')
			->once()
			->with('my_custom_cap', true);

		// ACT //
		(new UserRoles($config, $this->roleCommand, new WP_CLI))
			->createRoles();
	});

	it('can clone a new role from an existing role', function () {
		// ARRANGE //
		$config = [
			'prefix' => 'yard',
			'roles' => [
				'visitor' => [
					'display_name' => 'Bezoeker',
					'clone' => [
						'from' => 'subscriber',
					],
				],
			],
		];

		// EXPECT //
		$this->roleCommand->shouldReceive('create')
			->once()
			->with(['yard_visitor', 'Bezoeker'], ['clone' => 'subscriber']);

		// ACT //
		(new UserRoles($config, $this->roleCommand, new WP_CLI))
			->createRoles();
	});

	it('can add capabilities to a cloned role', function () {
		// ARRANGE //
		$config = [
			'prefix' => 'yard',
			'roles' => [
				'visitor' => [
					'display_name' => 'Bezoeker',
					'clone' => [
						'from' => 'subscriber',
						'add' => [
							'caps' => [
								'my_custom_cap',
							],
						],
					],
				],
			],
		];

		// EXPECT //
		$this->roleCommand->shouldReceive('create')
			->once()
			->with(['yard_visitor', 'Bezoeker'], ['clone' => 'subscriber']);

		$this->wpRole->shouldReceive('add_cap')
			->once()
			->with('my_custom_cap', true);

		// ACT //
		(new UserRoles($config, $this->roleCommand, new WP_CLI))
			->createRoles();
	});

	it('can add cap groups to a custom role', function () {
		// ARRANGE //
		$config = [
			'prefix' => 'yard',
			'roles' => [
				'visitor' => [
					'display_name' => 'Bezoeker',
					'cap_groups' => [
						'plugins',
					],
				],
			],
			'cap_groups' => [
				'plugins' => [
					'activate_plugins',
					'delete_plugins',
				],
			],
		];

		// EXPECT //
		$this->roleCommand->shouldReceive('create')
			->once()
			->with(['yard_visitor', 'Bezoeker'], []);

		$this->wpRole->shouldReceive('add_cap')
			->once()
			->with('activate_plugins', true);

		$this->wpRole->shouldReceive('add_cap')
			->once()
			->with('delete_plugins', true);

		// ACT //
		(new UserRoles($config, $this->roleCommand, new WP_CLI))
			->createRoles();
	});

	it('can add cap groups to a custom cloned role', function () {
		// ARRANGE //
		$config = [
			'prefix' => 'yard',
			'roles' => [
				'visitor' => [
					'display_name' => 'Bezoeker',
					'clone' => [
						'from' => 'subscriber',
						'add' => [
							'cap_groups' => [
								'plugins',
							],
						],
					],
				],
			],
			'cap_groups' => [
				'plugins' => [
					'activate_plugins',
					'delete_plugins',
				],
			],
		];

		// EXPECT //
		$this->roleCommand->shouldReceive('create')
			->once()
			->with(['yard_visitor', 'Bezoeker'], ['clone' => 'subscriber']);

		$this->wpRole->shouldReceive('add_cap')
			->once()
			->with('activate_plugins', true);

		$this->wpRole->shouldReceive('add_cap')
			->once()
			->with('delete_plugins', true);

		// ACT //
		(new UserRoles($config, $this->roleCommand, new WP_CLI))
			->createRoles();
	});
});
