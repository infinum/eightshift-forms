<?php

/**
 * The Theme/Frontend Enqueue specific functionality.
 *
 * @package EightshiftForms\Enqueue\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Theme;

use EightshiftForms\Config\Config;
use EightshiftForms\Rest\Routes\FormSubmitRoute;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Manifest\ManifestInterface;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Theme\AbstractEnqueueTheme;

/**
 * Class EnqueueTheme
 */
class EnqueueTheme extends AbstractEnqueueTheme
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
		\add_action('wp_enqueue_scripts', [$this, 'enqueueStylesLocal'], 10);
		\add_action('wp_enqueue_scripts', [$this, 'enqueueScriptsLocal']);
	}

	/**
	 * Method that returns frontend script with check.
	 *
	 * @return mixed
	 */
	public function enqueueScriptsLocal()
	{
		if ($this->getOptionValue(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_SCRIPTS_KEY)) {
			return null;
		}

		$this->enqueueScripts();
	}

	/**
	 * Method that returns frontend style with check.
	 *
	 * @return mixed
	 */
	public function enqueueStylesLocal()
	{
		if ($this->getOptionValue(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_STYLES_KEY)) {
			return null;
		}

		$this->enqueueStyles();
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
	 * Get script localizations
	 *
	 * @return array<string, mixed>
	 */
	protected function getLocalizations(): array
	{
		$restRoutesPath = \rest_url() . Config::getProjectRoutesNamespace() . '/' . Config::getProjectRoutesVersion();

		return [
			'esFormsLocalization' => [
				'formSubmitRestApiUrl' => $restRoutesPath . FormSubmitRoute::ROUTE_SLUG,
				'hideGlobalMessageTimeout' => apply_filters(Filters::FILTER_FORM_JS_REDIRECTION_TIMEOUT_NAME, 6000),
				'redirectionTimeout' => apply_filters(Filters::FILTER_FORM_JS_HIDE_GLOBAL_MESSAGE_TIMEOUT_NAME, 600),
			]
		];
	}
}
