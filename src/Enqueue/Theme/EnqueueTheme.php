<?php

/**
 * The Theme/Frontend Enqueue specific functionality.
 *
 * @package EightshiftForms\Enqueue\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Theme;

use EightshiftForms\Config\Config;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
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
		if ($this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
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
		if ($this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
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

		$hideGlobalMsgTimeoutFilterName = Filters::getBlockFilterName('form', 'hideGlobalMsgTimeout');
		$redirectionTimeoutFilterName = Filters::getBlockFilterName('form', 'redirectionTimeout');
		$previewRemoveLabelFilterName = Filters::getBlockFilterName('file', 'previewRemoveLabel');
		$hideLoadingStateTimeoutFilterName = Filters::getBlockFilterName('form', 'hideLoadingStateTimeout');

		return [
			'esFormsLocalization' => [
				'formSubmitRestApiUrl' => $restRoutesPath . '/form-submit',
				'hideGlobalMessageTimeout' => apply_filters($hideGlobalMsgTimeoutFilterName, 6000),
				'redirectionTimeout' => apply_filters($redirectionTimeoutFilterName, 600),
				'hideLoadingStateTimeout' => apply_filters($hideLoadingStateTimeoutFilterName, 600),
				'fileCustomRemoveLabel' => apply_filters($previewRemoveLabelFilterName, esc_html__('Remove', 'eightshift-forms')),
				'formDisableScrollToFieldOnError' => $this->isCheckboxOptionChecked(
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
				),
				'formDisableScrollToGlobalMessageOnSuccess' => $this->isCheckboxOptionChecked(
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
				),
				'formResetOnSuccess' => !Variables::isDevelopMode(),
			]
		];
	}
}
