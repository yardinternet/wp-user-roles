<?php

declare(strict_types=1);

use Mockery\MockInterface;

it('can call createRoles statically on UserRoles using the facade', function () {
	$this->mock(\Yard\UserRoles\UserRoles::class, function (MockInterface $mock) {
		$mock->shouldReceive('createRoles')->once();
	});

	Yard\UserRoles\Facades\UserRoles::createRoles();
});
