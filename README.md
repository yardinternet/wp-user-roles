# Yard :package_name

[![Code Style](https://github.com/yardinternet/skeleton-package/actions/workflows/format-php.yml/badge.svg?no-cache)](https://github.com/yardinternet/skeleton-package/actions/workflows/format-php.yml)
[![PHPStan](https://github.com/yardinternet/skeleton-package/actions/workflows/phpstan.yml/badge.svg?no-cache)](https://github.com/yardinternet/skeleton-package/actions/workflows/phpstan.yml)
[![Tests](https://github.com/yardinternet/skeleton-package/actions/workflows/run-tests.yml/badge.svg?no-cache)](https://github.com/yardinternet/skeleton-package/actions/workflows/run-tests.yml)
[![Code Coverage Badge](https://github.com/yardinternet/skeleton-package/blob/badges/coverage.svg)](https://github.com/yardinternet/skeleton-package/actions/workflows/badges.yml)
[![Lines of Code Badge](https://github.com/yardinternet/skeleton-package/blob/badges/lines-of-code.svg)](https://github.com/yardinternet/skeleton-package/actions/workflows/badges.yml)

<!--delete-->
---
This repository provides a scaffold for creating an Acorn package. For more detailed information, please refer to the [Acorn Package Development](https://roots.io/acorn/docs/package-development/) documentation.

Follow these steps to get started:

1. Press the "Use this template" button at the top of this repo to create a new repo with the contents of this skeleton.
2. Run "php ./configure.php" to run a script that will replace all placeholders throughout all the files.
3. Have fun creating your package.

---
<!--/delete-->

## Requirements

- [Sage](https://github.com/roots/sage) >= 10.0
- [Acorn](https://github.com/roots/acorn) >= 4.0

## Installation

To install this package using Composer, follow these steps:

1. Add the following to the `repositories` section of your `composer.json`:

    ```json
    {
      "type": "vcs",
      "url": "git@github.com:yardinternet/skeleton-package.git"
    }
    ```

2. Install this package with Composer:

    ```sh
    composer require yard/skeleton-package
    ```

3. Run the Acorn WP-CLI command to discover this package:

    ```shell
    wp acorn package:discover
    ```

You can publish the config file with:

```shell
wp acorn vendor:publish --provider="Yard\SkeletonPackage\SkeletonPackageServiceProvider"
```

## Usage

From a Blade template:

```blade
@include('skeleton-package::example')
```

From WP-CLI:

```shell
wp acorn example
```
