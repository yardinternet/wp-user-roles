<?php

declare(strict_types=1);

return [

    'prefix' => 'yard',
    'roles' => [
        'superuser' => [
            'display_name' => 'Superuser',
            'caps' => [
                'read' => true,
                'edit_dashboard'         => true,
                'edit_files'             => true,
                'unfiltered_html'        => true,
                'upload_files'           => true,
                'manage_categories'      => true,
                'edit_theme_options'     => true,
                'moderate_comments'      => true,
            ],
            'post_type_caps' => [
                'post',
                'page',
            ],
            'cap_groups' => [
                'gravityforms',
                'wpseo',
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
        ],
    ],

];
