<?php

/**
 * The Admin Enqueue specific functionality.
 *
 * @package EightshiftForms\Enqueue\Admin
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Admin;

use EightshiftForms\Enqueue\SharedEnqueue;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Admin\AbstractEnqueueAdmin;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * Class EnqueueAdmin
 *
 * This class handles enqueue scripts and styles.
 */
class EnqueueAdmin extends AbstractEnqueueAdmin
{
	/**
	 * Use shared helper trait.
	 */
	use SharedEnqueue;

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('admin_enqueue_scripts', [$this, 'enqueueStyles'], 50);
		\add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
	}

	/**
	 * Method that returns assets name used to prefix asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsPrefix(): string
	{
		return UtilsConfig::MAIN_PLUGIN_ENQUEUE_ASSETS_PREFIX;
	}

	/**
	 * Method that returns assets version for versioning asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsVersion(): string
	{
		return Helpers::getPluginVersion();
	}

	/**
	 * Enqueue scripts from AbstractEnqueueBlocks, extended to expose additional data. Only admin.
	 *
	 * @param string $hook Hook name.
	 *
	 * @return void
	 */
	public function enqueueScripts(string $hook): void
	{
		parent::enqueueScripts($hook);

		$output = [];

		if (UtilsGeneralHelper::isEightshiftFormsAdminPages()) {
			$output = \array_merge(
				$this->getEnqueueSharedInlineCommonItems(false),
				[
					'nonce' => \wp_create_nonce('wp_rest'),
					'uploadConfirmMsg' => \__('Are you sure you want to contine?', 'eighshift-forms'),
					'importErrorMsg' => \__('There is an error with your data, please try again.', 'eighshift-forms'),
					'isAdmin' => true,
					'redirectionTimeout' => 100,
				],
			);
		}

		$output = \wp_json_encode($output);
		\wp_add_inline_script($this->getAdminScriptHandle(), "const esFormsLocalization = {$output}", 'before');
	}

	/**
	 * Get admin script dependencies.
	 *
	 * @return array<int, string> List of all the script dependencies.
	 */
	protected function getAdminScriptDependencies(): array
	{
		$scriptsDependency = UtilsHooksHelper::getFilterName(['scripts', 'dependency', 'admin']);
		$scriptsDependencyOutput = [];

		if (\has_filter($scriptsDependency)) {
			$scriptsDependencyOutput = \apply_filters($scriptsDependency, []);
		}

		return $scriptsDependencyOutput;
	}
}
