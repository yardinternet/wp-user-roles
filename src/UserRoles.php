<?php

declare(strict_types=1);

namespace Yard\UserRoles;

use Role_Command;
use Webmozart\Assert\Assert;
use WP_CLI;

class UserRoles
{
	public function __construct(private Role_Command $roleCommand)
	{
	}

	public function setRoles(): void
	{
		if (is_multisite()) {
			foreach (get_sites(['fields' => 'ids']) as $siteId) {
				switch_to_blog($siteId);
				$this->setRolesForSite();
			}
		} else {
			$this->setRolesForSite();
		}
	}

	private function setRolesForSite(): void
	{
		$prefix = config('user-roles.prefix');
		Assert::stringNotEmpty($prefix);

		$this->removeCurrentCustomRoles();
		$this->resetDefaultRoles();
		$this->addCustomRoles();
	}

	private function removeCurrentCustomRoles(): void
	{
		WP_CLI::log(WP_CLI::colorize('%MDelete custom roles:%n'));

		$prefix = config('user-roles.prefix');
		$currentCustomRoles = wp_roles()->roles;
		$currentCustomRoles = array_filter(
			array_keys($currentCustomRoles),
			fn (string $role): bool => str_starts_with($role, $prefix)
		);
		foreach ($currentCustomRoles as $role) {
			$this->roleCommand->delete([$role]);
		}
	}

	private function addCustomRoles(): void
	{
		WP_CLI::log(WP_CLI::colorize('%MCreate custom roles:%n'));

		$prefix = config('user-roles.prefix');
		$roles = config('user-roles.roles');
		Assert::isArray($roles);

		foreach ($roles as $role => $properties) {
			$capabilities = [];
			if (! empty($properties['cap_groups'])) {
				$capGroups = config('user-roles.cap_groups');
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
						continue;
					}
					Assert::object($postTypeCaps);
					foreach (array_values((array)$postTypeCaps) as $cap) {
						$capabilities[$cap] = true;
					}
				}
			}

			if (! empty($properties['caps'])) {
				foreach ($properties['caps'] as $cap) {
					$capabilities[$cap] = true;
				}
			}

			$clone = [];
			if (! empty($properties['clone']) && is_string($properties['clone'])) {
				$clone['clone'] = $properties['clone'];
			}

			$this->roleCommand->create([
				$prefix . '_' . $role,
				$properties['display_name'],
			], $clone);

			$role = get_role($prefix . '_' . $role);

			foreach ($capabilities as $cap => $grant) {
				$role->add_cap((string)$cap, $grant);
			}
		}
	}

	private function resetDefaultRoles(): void
	{
		WP_CLI::log(WP_CLI::colorize('%MReset default roles:%n'));

		$this->roleCommand->reset([], ['all' => true]);
	}
}
