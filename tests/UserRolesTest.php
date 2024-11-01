<?php

declare(strict_types=1);

use Yard\UserRoles\UserRoles;

it('expects to find all core roles in config', function () {
	$coreRoles = [
		'administrator' => true,
		'editor' => false,
		'author' => false,
		'contributor' => false,
		'subscriber' => false,
	];

	expect(config('user-roles.core_roles'))->toBeArray()
		->and(config('user-roles.core_roles'))->toBe($coreRoles);
});

it('removes core roles marked for deletion', function () {
	$roleCommand = Mockery::mock(Role_Command::class);
	$roleCommand->shouldReceive('delete')->times(4);

	$userRoles = new UserRoles($roleCommand);

	$reflection = new ReflectionClass($userRoles);
	$method = $reflection->getMethod('removeCoreRoles');
	$method->setAccessible(true);

	$method->invoke($userRoles);
});
