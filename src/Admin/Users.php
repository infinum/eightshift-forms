<?php

/**
 * Modifying capabilities / roles.
 *
 * @package D66\EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms\Admin;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class that modifies user capabilities
 */
final class Users implements ServiceInterface
{

  /**
   * Register all the hooks
   *
   * @return void
   */
	public function register(): void
	{
		add_action('admin_init', [ $this, 'allow_forms_access' ], 10);
	}

  /**
   * Easy 1 function for managing additional capabilities.
   *
   * @return void
   */
	public function allow_forms_access()
	{
		if (has_filter(Filters::ROLES_WITH_FORMS_ACCESS)) {
			$roles = apply_filters(Filters::ROLES_WITH_FORMS_ACCESS, $this->get_roles_with_forms_access());
		} else {
			$roles = $this->get_roles_with_forms_access();
		}

		$this->manage_additional_roles($roles);
	}

  /**
   * Easy 1 function for managing additional capabilities.
   *
   * @param array $roles Roles in 'role_name' => true/false format.
   * @return void
   */
	public function manage_additional_roles(array $roles)
	{
		foreach ($roles as $role_name => $has_access) {
			$role_object = get_role($role_name);

			if (empty($role_object)) {
				continue;
			}

			if ($has_access) {
				foreach ($this->get_all_post_type_caps(Forms::POST_CAPABILITY_TYPE) as $cap => $value) {
					if (! $role_object->has_cap($cap)) {
						$role_object->add_cap($cap);
					}
				}
			} else {
				foreach ($this->get_all_post_type_caps(Forms::POST_CAPABILITY_TYPE) as $cap => $value) {
					if ($role_object->has_cap($cap)) {
						$role_object->remove_cap($cap);
					}
				}
			}
		}
	}

  /**
   * Returns the default array of roles which can access Forms CPT:
   *
   * @return array
   */
	public function get_roles_with_forms_access(): array
	{
		return [
		'administrator' => true,
		];
	}

  /**
   * Get all post type caps.
   *
   * @param string $type      Name of post type.
   *
   * @return array
   */
	private function get_all_post_type_caps(string $type): array
	{
		$output = [
		"publish_{$type}s"       => true,
		"edit_{$type}s"          => true,
		"edit_others_{$type}s"   => true,
		"delete_{$type}s"        => true,
		"delete_others_{$type}s" => true,
		"read_private_{$type}s"  => true,
		"edit_{$type}"           => true,
		"delete_{$type}"         => true,
		"read_{$type}"           => true,
		];

		return $output;
	}
}
