<?php

declare(strict_types=1);

namespace Yard\UserRoles;

use Role_Command;
use Webmozart\Assert\Assert;
use WP_CLI;
use WP_CLI\ExitException;

class UserRoles
{
	private string $prefix;

	/**
	 * @param array<mixed> $config
	 *
	 * @throws ExitException
	 */
	public function __construct(private array $config, private Role_Command $roleCommand, private WP_CLI $wpCli)
	{
		$this->prefix = $this->prefixValidate($config);
	}

	/**
	 * (Re)creates roles based on the provided configuration.
	 */
	public function createRoles(): void
	{
		$this->deleteCustomRoles();
		$this->resetCoreRoles();
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

		$customRoles = $this->getCurrentCustomRoles();

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
	private function getCurrentCustomRoles(): array
	{
		return array_filter(
			array_keys(wp_roles()->roles),
			fn (string $role): bool => str_starts_with($role, $this->prefix)
		);
	}

	private function addCustomRoles(): void
	{
		$this->wpCli::log($this->wpCli::colorize('%MCreate custom roles:%n'));

		if (false === $this->rolesValid()) {
			$this->wpCli::warning('No roles found in config. Skipping custom role creation.');

			return;
		}

		$roles = $this->config['roles'];

		foreach ($roles as $role => $properties) {
			if (! isset($properties['display_name']) || ! is_string($properties['display_name'])) {
				$this->wpCli::warning("No display name configured for role $role. Skipping role creation.");

				continue;
			}

			$capabilities = [];

			if ($this->cloneValid($properties)) {
				$this->createRole($role, $properties, ['clone' => $properties['clone']['from']]);

				if (isset($properties['clone']['add']) && is_array($properties['clone']['add'])) {
					$capabilities = $this->capabilitiesFromRoleProperties($properties['clone']['add']);
				}
			} else {
				$this->createRole($role, $properties);
				$capabilities = $this->capabilitiesFromRoleProperties($properties);
			}

			$role = get_role($this->prefix . $role);

			Assert::notNull($role);

			foreach ($capabilities as $cap => $grant) {
				$role->add_cap((string)$cap, $grant);
			}
		}
	}

	/**
	 * @param array<mixed> $properties
	 */
	private function cloneValid(array $properties): bool
	{
		return isset($properties['clone']['from'])
			&& is_string($properties['clone']['from']);
	}

	/**
	 * @param array<mixed> $properties
	 * @param array<mixed> $assocArgs
	 */
	private function createRole(string $role, array $properties, array $assocArgs = []): void
	{
		$this->roleCommand->create([
			$this->prefix . $role,
			$properties['display_name'],
		], $assocArgs);
	}

	private function rolesValid(): bool
	{
		return isset($this->config['roles'])
			&& is_array($this->config['roles'])
			&& 0 !== count($this->config['roles']);
	}

	private function resetCoreRoles(): void
	{
		$this->wpCli::log($this->wpCli::colorize('%MReset core roles:%n'));

		$this->roleCommand->reset([], ['all' => true]);
	}

	private function deleteCoreRoles(): void
	{
		$this->wpCli::log($this->wpCli::colorize('%MDelete core roles:%n'));
		
		if (! $this->coreRolesValid()) {
			$this->wpCli::warning('No core roles found in config. Skipping core role deletion.');

			return;
		}

		$coreRoles = $this->config['core_roles'];

		foreach ($coreRoles as $role => $shouldStay) {
			if (false === $shouldStay) {
				$this->roleCommand->delete([$role]);
			}
		}
	}

	private function coreRolesValid(): bool
	{
		return isset($this->config['core_roles'])
			&& is_array($this->config['core_roles'])
			&& 0 !== count($this->config['core_roles']);
	}

	/**
	 * @param array<mixed> $properties
	 *
	 * @return array<mixed>
	 */
	public function capabilitiesFromRoleProperties(array $properties): array
	{
		$capabilities = [];
		if (! empty($properties['cap_groups'])) {
			$capGroups = $this->config['cap_groups'];
			Assert::isArray($capGroups);
			Assert::isArray($properties['cap_groups']);
			foreach ($properties['cap_groups'] as $group) {
				$groupCaps = $capGroups[$group];
				Assert::isArray($groupCaps);

				foreach ($groupCaps as $cap) {
					$capabilities[$cap] = true;
				}
			}
		}

		if (! empty($properties['post_type_caps'])) {
			Assert::isArray($properties['post_type_caps']);
			foreach ($properties['post_type_caps'] as $postType) {
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

		if (! empty($properties['caps'])) {
			foreach ($properties['caps'] as $cap) {
				$capabilities[$cap] = true;
			}
		}

		return $capabilities;
	}
}
