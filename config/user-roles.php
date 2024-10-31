<?php

declare(strict_types=1);

return [

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
			'clone' => 'subscriber',
			'caps' => [
				'yard_hide_admin_bar',
				'yard_redirect_home_after_login',
				'yard_prevent_admin_access',
			],
		],
	],
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
			// We cannot use post_type_caps because SEOPress registers post types in admin only
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
