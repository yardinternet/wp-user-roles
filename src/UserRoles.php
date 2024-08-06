<?php

declare(strict_types=1);

namespace Yard\UserRoles;

use Illuminate\Contracts\Foundation\Application;
use Webmozart\Assert\Assert;

class UserRoles
{
    /**
     * Create a new UserRoles instance.
     */
    public function __construct(protected Application $app)
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

        $roles = config('user-roles.roles');
        $prefix = config('user-roles.prefix');

        Assert::isArray($roles);
        Assert::stringNotEmpty($prefix);


        //Remove current custom roles
        $current_roles = \wp_roles()->roles;
        $current_roles = array_filter(
            array_keys($current_roles),
            fn (string $role): bool => str_starts_with($role, $prefix)
        );
        foreach($current_roles as $role) {
            remove_role($role);
        }

        // Add custom roles
        foreach($roles as $role => $properties) {
            $capabilities = [];

            $capGroups = config('user-roles.cap_groups');
            Assert::isArray($capGroups);
            foreach ($properties['cap_groups'] as $group) {
                $groupCaps = $capGroups[$group];
                Assert::isArray($groupCaps);

                foreach ($groupCaps as $cap) {
                    $capabilities[$cap] = true;
                }
            }

            foreach ($properties['post_type_caps'] as $postType) {
                $postTypeCaps = get_post_type_object($postType)?->cap;
                Assert::object($postTypeCaps);
                foreach(array_values((array)$postTypeCaps) as $cap) {
                    $capabilities[$cap] = true;
                }
            }

            foreach($properties['caps'] as $cap => $value) {
                $capabilities[$cap] = $value;
            }

            add_role(
                $prefix . '_' . $role,
                $properties['display_name'],
                $capabilities
            );
        }
    }
}
