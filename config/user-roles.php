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
	| Define custom roles here. Each role should at least have a `display_name`.
	| Capabilities can be assigned to a role using the properties `caps`, `cap_groups`
	| and`post_type_caps`.
	|
	| If you want to clone an existing role, you can specify the role to clone from
	| and both add and remove `caps`, `cap_groups and `post_type_caps`.
	|
	| `post_type_caps` are configured when registering a post type. These are
	| capabilities like 'edit_{post_type}', 'publish_{post_type}',
	| 'delete_{post_type}', etc.
	|
	*/

	'prefix' => 'yard',
	'roles' => [
		'superuser' => [
			'display_name' => 'Superuser',
			'caps' => [
				'read',
				'edit_dashboard',
				'edit_files',
				'unfiltered_html',
				'upload_files',
				'manage_categories',
				'edit_theme_options',
				'moderate_comments',
				'copy_posts',
			],
			'post_type_caps' => [
				'post',
				'page',
			],
			'cap_groups' => [
				'users',
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
	| Create groups of related capabilities and assign all of them at once.
	| These groups can be used in the 'cap_groups' property of a role.
	|
	*/

	'cap_groups' => [
		'plugins' => [
			'activate_plugins',
			'delete_plugins',
			'edit_plugins',
			'install_plugins',
			'update_plugins',
		],
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
