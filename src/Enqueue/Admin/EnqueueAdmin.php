<?php

/**
 * The Admin Enqueue specific functionality.
 *
 * @package EightshiftForms\Enqueue\Admin
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Admin;

use EightshiftForms\Config\Config;
use EightshiftForms\Enqueue\SharedEnqueue;
use EightshiftFormsVendor\EightshiftLibs\Manifest\ManifestInterface;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Admin\AbstractEnqueueAdmin;

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
	 * Create a new admin instance.
	 *
	 * @param ManifestInterface $manifest Inject manifest which holds data about assets from manifest.json.
	 */
	public function __construct(ManifestInterface $manifest)
	{
		$this->manifest = $manifest;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('login_enqueue_scripts', [$this, 'enqueueStyles']);
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
		return Config::getProjectName();
	}

	/**
	 * Method that returns assets version for versioning asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsVersion(): string
	{
		return Config::getProjectVersion();
	}

	/**
	 * Enqueue scripts from AbstractEnqueueBlocks, extended to expose additional data. Only admin.
	 *
	 * @return void
	 */
	public function enqueueScripts(): void
	{
		parent::enqueueScripts();

		$output = \array_merge(
			$this->getEnqueueSharedInlineCommonItems(),
			[
				'nonce' => \wp_create_nonce('wp_rest'),
				'uploadConfirmMsg' => \__('Are you sure you want to contine?', 'eighshift-forms'),
				'isAdmin' => true,
			],
		);

		$output = \wp_json_encode($output);

		\wp_add_inline_script($this->getAdminScriptHandle(), "const esFormsLocalization = {$output}", 'before');
	}
}
