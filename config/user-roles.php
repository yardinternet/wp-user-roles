<?php

declare(strict_types=1);

return [

	/*
	|--------------------------------------------------------------------------
	| WordPress core roles
	|--------------------------------------------------------------------------
	|
	| These are the default WordPress roles. You can disable them by setting the
	| value to false. This will prevent the role from being created or updated.
	|
	*/

	'core_roles' => [
		'administrator' => true,
		'editor' => true,
		'author' => true,
		'contributor' => true,
		'subscriber' => true,
	],

	/*
	|--------------------------------------------------------------------------
	| Custom roles
	|--------------------------------------------------------------------------
	|
	| Define custom roles here. Each role should have a display name. Capabilities,
	| Post Type capabilities and capability groups can be assigned to a role.
	| If you want to clone an existing role, you can specify the role to clone from
	| and add or remove capabilities, Post Type capabilities and cap groups.
	|
	| Post Type capabilities are configured when registering a post type. These are
	| capabilities like 'edit_{$post->post_type}', 'publish_{$post->post_type}',
	| 'delete_{$post->post_type}', etc.
	|
	| If a post type is not registered in cli context you can add the post type capabilities
	| manually by manually adding the capabilities to the Cap Groups.
	|
	*/

	'prefix' => 'yard',
	'roles' => [
		'superuser' => [
			'display_name' => 'Superuser',
			'caps' => [
				'my_custom_cap',
			],
			'post_type_caps' => [
				'post',
			],
			'cap_groups' => [
				'users',
				'themes',
			],
		],
		'visitor' => [
			'display_name' => 'Visitor',
			'clone' => [
				'from' => 'subscriber',
				'add' => [
					'caps' => [],
					'post_type_caps' => [],
					'cap_groups' => [],
				],
				'remove' => [
					'caps' => [],
					'post_type_caps' => [],
					'cap_groups' => [],
				],
			],
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Capability groups
	|--------------------------------------------------------------------------
	|
	| Define groups of related capabilities to easily assign multiple capabilities
	| to a role. These groups can be used in the 'cap_groups' property of a role.
	|
	*/

	'cap_groups' => [
		'users' => [
			'create_users',
			'delete_users',
			'edit_users',
			'list_users',
			'promote_users',
			'remove_users',
		],
		'themes' => [
			'delete_themes',
			'edit_themes',
			'install_themes',
			'switch_themes',
			'update_themes',
		],
	],

];
