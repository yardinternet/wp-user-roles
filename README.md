# wp-user-roles

[![Code Style](https://github.com/yardinternet/wp-user-roles/actions/workflows/format-php.yml/badge.svg?no-cache)](https://github.com/yardinternet/wp-user-roles/actions/workflows/format-php.yml)
[![PHPStan](https://github.com/yardinternet/wp-user-roles/actions/workflows/phpstan.yml/badge.svg?no-cache)](https://github.com/yardinternet/wp-user-roles/actions/workflows/phpstan.yml)
[![Tests](https://github.com/yardinternet/wp-user-roles/actions/workflows/run-tests.yml/badge.svg?no-cache)](https://github.com/yardinternet/wp-user-roles/actions/workflows/run-tests.yml)
[![Code Coverage Badge](https://github.com/yardinternet/wp-user-roles/blob/badges/coverage.svg)](https://github.com/yardinternet/wp-user-roles/actions/workflows/badges.yml)
[![Lines of Code Badge](https://github.com/yardinternet/wp-user-roles/blob/badges/lines-of-code.svg)](https://github.com/yardinternet/wp-user-roles/actions/workflows/badges.yml)

An Acorn package for managing user roles in WordPress.

## Features

- [x] Configure: Define custom roles and capabilities with a configuration file.
- [x] (Re)Create: Insert roles into the database with a single wp-cli command.
- [x] Clone roles: Quickly set up new roles based on existing ones.
- [x] Delete roles: Remove any roles that you don’t need.

See [config](./config/user-roles.php) for all configuration options.

## Requirements

- [Sage](https://github.com/roots/sage) >= 10.0
- [Acorn](https://github.com/roots/acorn) >= 4.0

## Installation

1. Install this package with Composer:

    ```sh
    composer require yard/wp-user-roles
    ```

2. Run the Acorn WP-CLI command to discover this package:

    ```shell
    wp acorn package:discover
    ```

## Usage

1. Publish the config file with:

   ```shell
   wp acorn vendor:publish --provider="Yard\UserRoles\UserRolesServiceProvider"
   ```

2. Run WP-CLI command to create roles:

    Single site:

   ```shell
   wp acorn roles:create
   ```

    In a multisite:

    ```shell
    wp site list --field=url | xargs -n1 -I % wp acorn roles:create --url=% 
    ```
