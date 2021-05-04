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
use EightshiftLibs\Services\ServiceInterface;

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
		add_action('admin_init', [$this, 'allowFormsAccess'], 10);
	}

	/**
	 * Easy 1 function for managing additional capabilities.
	 *
	 * @return void
	 */
	public function allowFormsAccess()
	{
		if (has_filter(Filters::ROLES_WITH_FORMS_ACCESS)) {
			$roles = apply_filters(Filters::ROLES_WITH_FORMS_ACCESS, $this->getRolesWithFormsAccess());
		} else {
			$roles = $this->getRolesWithFormsAccess();
		}

		$this->manageAdditionalRoles($roles);
	}

	/**
	 * Easy 1 function for managing additional capabilities.
	 *
	 * @param array $roles Roles in 'roleName' => true/false format.
	 * @return void
	 */
	public function manageAdditionalRoles(array $roles)
	{
		foreach ($roles as $roleName => $hasAccess) {
			$roleObject = get_role($roleName);

			if (empty($roleObject)) {
				continue;
			}

			if ($hasAccess) {
				foreach ($this->getAllPostTypeCaps(Forms::POST_CAPABILITY_TYPE) as $cap => $value) {
					if (! $roleObject->has_cap($cap)) {
						$roleObject->add_cap($cap);
					}
				}
			} else {
				foreach ($this->getAllPostTypeCaps(Forms::POST_CAPABILITY_TYPE) as $cap => $value) {
					if ($roleObject->has_cap($cap)) {
						$roleObject->remove_cap($cap);
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
	public function getRolesWithFormsAccess(): array
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
	private function getAllPostTypeCaps(string $type): array
	{
		return [
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
	}
}
