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
		'editor' => false,
		'author' => false,
		'contributor' => false,
		'subscriber' => false,
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
				'tribe_events',
				'tribe_organizer',
				'tribe_venue',
			],
			'cap_groups' => [
				'gravityforms',
				'wpseo',
				'users',
			],
		],
		'visitor' => [
			'display_name' => 'Bezoeker',
			'clone' => [
				'from' => 'subscriber',
				'add' => [
					'caps' => [
						'yard_hide_admin_bar',
						'yard_redirect_home_after_login',
						'yard_prevent_admin_access',
					],
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
		'gravityforms' => [
			'gravityforms_create_form',
			'gravityforms_delete_forms',
			'gravityforms_edit_forms',
			'gravityforms_preview_forms',
			'gravityforms_view_entries',
			'gravityforms_edit_entries',
			'gravityforms_delete_entries',
			'gravityforms_view_entry_notes',
			'gravityforms_edit_entry_notes',
		],
		'wpseo' => [
			'wpseo_bulk_edit',
			'wpseo_manage_options',
			'wpseo_bulk_edit',
			'wpseo_manage_options',
			'edit_redirection',
			'edit_redirections',
			'edit_others_redirections',
			'publish_redirections',
			'read_redirection',
			'read_private_redirections',
			'delete_redirection',
			'delete_others_redirections',
			'delete_published_redirections',
		],
	],

];
