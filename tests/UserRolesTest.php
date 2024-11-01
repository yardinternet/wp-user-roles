<?php

declare(strict_types=1);

use Yard\UserRoles\UserRoles;

it('expects all core roles to be marked for removal in config', function () {
	$coreRoles = [
		'administrator' => false,
		'editor' => false,
		'author' => false,
		'contributor' => false,
		'subscriber' => false,
	];

	expect(config('user-roles.core_roles'))->toBeArray()
		->and(config('user-roles.core_roles'))->toBe($coreRoles);
});

it('removes all core roles', function () {
	$roleCommand = Mockery::mock(Role_Command::class);
	$roleCommand->shouldReceive('delete')->times(5);

	$userRoles = new UserRoles($roleCommand);

	$reflection = new ReflectionClass($userRoles);
	$method = $reflection->getMethod('removeCoreRoles');
	$method->setAccessible(true);

	$method->invoke($userRoles);
});
