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
		'return' => mockWpRoles([]),
	]);
}

function mockRoleCommandOn(WP_Roles $wpRoles): Role_Command
{
	$roleCommand = Mockery::mock(Role_Command::class)->shouldIgnoreMissing();

	$roleCommand->shouldReceive('delete')
		->andReturnUsing(function (array $args) use ($wpRoles): void {
			unset($wpRoles->roles[$args[0]]);
		});

	$roleCommand->shouldReceive('create')
		->andReturnUsing(function (array $args) use ($wpRoles): void {
			$wpRoles->roles[$args[0]] = ['capabilities' => []];
		});

	return $roleCommand;
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
		'yard_superuser' => ['capabilities' => []],
		'my_prefix_visitor' => ['capabilities' => []],
	]);

	WP_Mock::userFunction('wp_roles', [
		'return' => $wpRoles,
	]);
	WP_Mock::userFunction('update_option');

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

it('warns about users that still have a removed role', function () {
	$wpRoles = mockWpRoles([
		'administrator' => ['capabilities' => ['manage_options' => true, 'read' => true]],
		'editor' => ['capabilities' => ['edit_posts' => true, 'read' => true]],
		'subscriber' => ['capabilities' => ['read' => true]],
	]);

	WP_Mock::userFunction('wp_roles', [
		'return' => $wpRoles,
	]);
	WP_Mock::userFunction('update_option');
	WP_Mock::userFunction('get_users', [
		'args' => [['role' => 'editor', 'fields' => 'ID']],
		'return' => [3, 7],
	]);
	WP_Mock::userFunction('get_users', [
		'args' => [['role' => 'subscriber', 'fields' => 'ID']],
		'return' => [],
	]);

	$wpCli = Mockery::mock(WP_CLI::class)->shouldIgnoreMissing();

	$wpCli->shouldReceive('warning')
		->once()
		->with("Role 'editor' was removed, but 2 user(s) still have it. List them with: wp user list --role=editor");

	$wpCli->shouldNotReceive('warning')
		->with(Mockery::pattern("/Role 'subscriber' was removed/"));

	$config = [
		'prefix' => 'yard',
		'core_roles' => [
			'administrator' => true,
			'editor' => false,
			'subscriber' => false,
		],
	];

	(new UserRoles($config, mockRoleCommandOn($wpRoles), $wpCli))
		->createRoles();
});

it('does not warn about users of a custom role that is recreated', function () {
	$wpRoles = mockWpRoles([
		'administrator' => ['capabilities' => ['manage_options' => true, 'read' => true]],
		'yard_superuser' => ['capabilities' => ['read' => true]],
	]);

	WP_Mock::userFunction('wp_roles', [
		'return' => $wpRoles,
	]);
	WP_Mock::userFunction('get_role', [
		'return' => Mockery::mock(WP_Role::class),
	]);
	WP_Mock::userFunction('update_option');
	WP_Mock::userFunction('get_users', [
		'times' => 0,
	]);

	$config = [
		'prefix' => 'yard',
		'roles' => [
			'superuser' => [
				'display_name' => 'Superuser',
			],
		],
	];

	(new UserRoles($config, mockRoleCommandOn($wpRoles), new WP_CLI))
		->createRoles();
});

it('sets the default role to the remaining role with the fewest capabilities', function () {
	$wpRoles = mockWpRoles([
		'administrator' => ['capabilities' => ['manage_options' => true, 'edit_posts' => true, 'read' => true]],
		'editor' => ['capabilities' => ['edit_posts' => true, 'read' => true, 'manage_options' => false, 'delete_posts' => false]],
		'author' => ['capabilities' => ['edit_posts' => true, 'upload_files' => true, 'read' => true]],
		'subscriber' => ['capabilities' => ['read' => true]],
	]);

	WP_Mock::userFunction('wp_roles', [
		'return' => $wpRoles,
	]);
	WP_Mock::userFunction('get_users', [
		'return' => [],
	]);
	WP_Mock::userFunction('update_option', [
		'times' => 1,
		'args' => ['default_role', 'editor'],
	]);

	$config = [
		'prefix' => 'yard',
		'core_roles' => [
			'subscriber' => false,
		],
	];

	(new UserRoles($config, mockRoleCommandOn($wpRoles), new WP_CLI))
		->createRoles();
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

	it('can remove caps from a cloned role', function () {
		// ARRANGE //
		$config = [
			'prefix' => 'yard',
			'roles' => [
				'visitor' => [
					'display_name' => 'Bezoeker',
					'clone' => [
						'from' => 'subscriber',
						'remove' => [
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

		$this->wpRole->shouldReceive('remove_cap')
			->once()
			->with('my_custom_cap');

		// ACT //
		(new UserRoles($config, $this->roleCommand, new WP_CLI))
			->createRoles();
	});
});
