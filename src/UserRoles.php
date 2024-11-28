<?php

declare(strict_types=1);

namespace Yard\UserRoles;

use Role_Command;
use Webmozart\Assert\Assert;
use WP_CLI;
use WP_CLI\ExitException;
use WP_Role;

class UserRoles
{
	private string $prefix;

	/** @var array<int, string> * */
	private array $coreRoles = [
		'administrator',
		'editor',
		'author',
		'contributor',
		'subscriber',
	];

	/** @var array<int, string|WP_Role> * */
	private array $preservedRoles = [];

	/**
	 * @param array<mixed> $config
	 *
	 * @throws ExitException
	 */
	public function __construct(private array $config, private Role_Command $roleCommand, private WP_CLI $wpCli)
	{
		$this->prefix = $this->prefixValidate($config);

		if (! function_exists('populate_roles')) {
			require_once ABSPATH . 'wp-admin/includes/schema.php';
		}
	}

	/**
	 * (Re)creates roles based on the provided configuration.
	 */
	public function createRoles(): void
	{
		$this->deleteCustomRoles();
		//		$this->resetCoreRoles();
		$this->addCustomRoles();
		$this->deleteCoreRoles();
	}

	/**
	 * @param array<mixed> $config
	 *
	 * @throws ExitException
	 */
	private function prefixValidate(array $config): string
	{
		$prefix = $config['prefix'] ?? '';

		if (! is_string($prefix) || '' === $prefix) {
			$this->wpCli::error('No prefix found in configuration file. Aborting role creation.');
		}

		return $prefix . '_';
	}

	private function deleteCustomRoles(): void
	{
		$this->wpCli::log($this->wpCli::colorize('%MDelete custom roles:%n'));

		$customRoles = $this->currentCustomRoles();

		if (0 === count($customRoles)) {
			$this->wpCli::warning("No custom roles with prefix '" . $this->prefix . "' found in database. Skipping custom role deletion.");

			return;
		}

		foreach ($customRoles as $role) {
			$this->roleCommand->delete([$role]);
		}
	}

	/**
	 * @return array<int, string>
	 */
	private function currentCustomRoles(): array
	{
		return array_filter(
			array_keys(wp_roles()->roles),
			fn (string $role): bool => str_starts_with($role, $this->prefix)
		);
	}

	private function addCustomRoles(): void
	{
		$this->wpCli::log($this->wpCli::colorize('%M(Re)create custom roles:%n'));

		if (false === $this->rolesValid()) {
			$this->wpCli::warning('No roles found in config. Skipping custom role creation.');

			return;
		}

		$this->preserveCoreRoles();

		$roles = $this->config['roles'];

		foreach ($roles as $role => $props) {
			if (! isset($props['display_name']) || ! is_string($props['display_name'])) {
				$this->wpCli::warning("No display name configured for role $role. Skipping role creation.");

				continue;
			}

			if ($this->cloneValid($props)) {
				$this->addClone($role, $props);
			} else {
				$this->addRole($role, $props);
			}
		}

		$this->restorePreservedRoles();
	}

	/**
	 * @param array<mixed> $props
	 */
	private function addRole(string $role, array $props): void
	{
		$role = $this->createRole($role, $props);
		$capabilities = $this->capsFromRoleProps($props);

		$this->addCaps($role, $capabilities);
	}

	/**
	 * @param array<mixed> $props
	 */
	private function addClone(string $role, array $props): void
	{
		$role = $this->createRole($role, $props, ['clone' => $props['clone']['from']]);

		if (isset($props['clone']['add']) && is_array($props['clone']['add'])) {
			$caps = $this->capsFromRoleProps($props['clone']['add']);
			$this->addCaps($role, $caps);
		}

		if (isset($props['clone']['remove']) && is_array($props['clone']['remove'])) {
			$removeCaps = $this->capsFromRoleProps($props['clone']['remove']);
			$this->removeCaps($role, $removeCaps);
		}
	}

	/**
	 * @param array<mixed> $caps
	 */
	private function addCaps(WP_Role $role, array $caps): void
	{
		foreach ($caps as $cap => $grant) {
			$role->add_cap((string)$cap, $grant);
		}
	}

	/**
	 * @param array<mixed> $caps
	 */
	private function removeCaps(WP_Role $role, array $caps): void
	{
		foreach ($caps as $cap => $grant) {
			$role->remove_cap((string)$cap);
		}
	}

	/**
	 * @param array<mixed> $props
	 */
	private function cloneValid(array $props): bool
	{
		return isset($props['clone']['from'])
			&& is_string($props['clone']['from']);
	}

	/**
	 * @param array<mixed> $props
	 * @param array<mixed> $assocArgs
	 */
	private function createRole(string $role, array $props, array $assocArgs = []): WP_Role
	{
		$this->roleCommand->create([
			$this->prefix . $role,
			$props['display_name'],
		], $assocArgs);

		$wpRole = get_role($this->prefix . $role);

		Assert::notNull($wpRole);

		return $wpRole;
	}

	private function rolesValid(): bool
	{
		return isset($this->config['roles'])
			&& is_array($this->config['roles'])
			&& 0 !== count($this->config['roles']);
	}

	//	private function resetCoreRoles(): void
	//	{
	//		$this->wpCli::log($this->wpCli::colorize('%MReset core roles:%n'));
	//
	//		$this->roleCommand->reset([], ['all' => true]);
	//	}

	private function deleteCoreRoles(): void
	{
		$this->wpCli::log($this->wpCli::colorize('%MDelete core roles:%n'));

		if (! $this->coreRolesValid()) {
			$this->wpCli::warning('No core roles found in config. Skipping core role deletion.');

			return;
		}

		$coreRoles = $this->config['core_roles'];

		$rolesDeleted = false;
		foreach ($coreRoles as $role => $shouldStay) {
			if (false === $shouldStay) {
				$this->roleCommand->delete([$role]);
				$rolesDeleted = true;
			}
		}

		if (! $rolesDeleted) {
			$this->wpCli::warning('No core roles were marked for deletion.');
		}
	}

	private function coreRolesValid(): bool
	{
		return isset($this->config['core_roles'])
			&& is_array($this->config['core_roles'])
			&& 0 !== count($this->config['core_roles']);
	}

	/**
	 * @param array<mixed> $props
	 *
	 * @return array<mixed>
	 */
	public function capsFromRoleProps(array $props): array
	{
		$capabilities = [];
		if (! empty($props['cap_groups'])) {
			$capGroups = $this->config['cap_groups'];
			Assert::isArray($capGroups);
			Assert::isArray($props['cap_groups']);
			foreach ($props['cap_groups'] as $group) {
				$groupCaps = $capGroups[$group];
				Assert::isArray($groupCaps);

				foreach ($groupCaps as $cap) {
					$capabilities[$cap] = true;
				}
			}
		}

		if (! empty($props['post_type_caps'])) {
			Assert::isArray($props['post_type_caps']);
			foreach ($props['post_type_caps'] as $postType) {
				$postTypeCaps = get_post_type_object($postType)?->cap;
				if (null === $postTypeCaps) {
					$this->wpCli::warning("Post type '$postType' does not exist. Skipping post type caps.");

					continue;
				}
				Assert::object($postTypeCaps);
				foreach ((array)$postTypeCaps as $cap) {
					$capabilities[$cap] = true;
				}
			}
		}

		if (! empty($props['caps'])) {
			foreach ($props['caps'] as $cap) {
				$capabilities[$cap] = true;
			}
		}

		return $capabilities;
	}

	private function preserveCoreRoles(): void
	{
		foreach ($this->coreRoles as $role) {
			$roleObject = get_role($role);
			// If role exists, preserve it
			if (null !== $roleObject) {
				$this->wpCli::log('Preserve and temporary delete core role `' . $role . '`');
				$this->preservedRoles[] = $roleObject;
				remove_role($role);
			}
		}

		// Put back all default roles and capabilities.
		$this->wpCli::log('Recreate and populate default roles: `administrator`, `editor`, `author`, `contributor`, `subscriber`.');
		populate_roles();
	}

	private function restorePreservedRoles(): void
	{
		foreach ($this->preservedRoles as $roleObject) {
			// Replace populated role with preserved role
			$this->wpCli::log('Restored preserved role `' . $roleObject->name . '`.');
			remove_role($roleObject->name);
			add_role($roleObject->name, ucwords($roleObject->name), $roleObject->capabilities);
		}
	}
}
