<?php

/**
 * Enqueue class used to define all script and style enqueues for Gutenberg blocks.
 *
 * @package EightshiftForms\Enqueue\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Blocks;

use EightshiftForms\Config\Config;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Rest\Routes\GeolocationCountriesRoute;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Tracking\TrackingInterface;
use EightshiftForms\Validation\SettingsCaptcha;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Blocks\AbstractEnqueueBlocks;
use EightshiftFormsVendor\EightshiftLibs\Manifest\ManifestInterface;

/**
 * Enqueue_Blocks class.
 */
class EnqueueBlocks extends AbstractEnqueueBlocks
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

	/**
	 * Instance variable of tracking data.
	 *
	 * @var TrackingInterface
	 */
	protected TrackingInterface $tracking;

	/**
	 * Create a new admin instance.
	 *
	 * @param ManifestInterface $manifest Inject manifest which holds data about assets from manifest.json.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param TrackingInterface $tracking Inject tracking which holds data about for storing to localstorage.
	 */
	public function __construct(
		ManifestInterface $manifest,
		ValidatorInterface $validator,
		TrackingInterface $tracking
	) {
		$this->manifest = $manifest;
		$this->validator = $validator;
		$this->tracking = $tracking;
	}

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		// Editor only script.
		\add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorScript']);

		// Editor only style.
		\add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorStyleLocal'], 50);
		\add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorOptionsStyles'], 51);

		// Editor and frontend style.
		\add_action('enqueue_block_assets', [$this, 'enqueueBlockStyleLocal'], 50);

		// Frontend only script.
		\add_action('wp_enqueue_scripts', [$this, 'enqueueBlockFrontendScript']);
	}

	/**
	 * Method that returns editor only style with check.
	 *
	 * @return mixed
	 */
	public function enqueueBlockEditorStyleLocal()
	{
		if ($this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return null;
		}

		$this->enqueueBlockEditorStyle();
	}

	/**
	 * Method that returns editor and frontend style with check.
	 *
	 * @return mixed
	 */
	public function enqueueBlockStyleLocal()
	{
		if ($this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return null;
		}

		$this->enqueueBlockStyle();
	}

	/**
	 * Enqueue blocks style for editor only - used for libs component styles.
	 *
	 * @return void
	 */
	public function enqueueBlockEditorOptionsStyles(): void
	{
		$handler = "{$this->getAssetsPrefix()}-editor-style";

		\wp_register_style(
			$handler,
			$this->manifest->getAssetsManifestItem('applicationEditor.css'),
			[],
			$this->getAssetsVersion(),
			$this->getMedia()
		);

		\wp_enqueue_style($handler);
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
		$output = [];

		// Admin part.
		if (\is_admin()) {
			$additionalBlocksFilterName = Filters::getBlocksFilterName('additionalBlocks');
			$formsStyleOptionsFilterName = Filters::getBlockFilterName('forms', 'styleOptions');
			$fieldStyleOptionsFilterName = Filters::getBlockFilterName('field', 'styleOptions');
			$formSelectorAdditionalContentFilterName = Filters::getBlockFilterName('formSelector', 'additionalContent');
			$inputAdditionalContentFilterName = Filters::getBlockFilterName('input', 'additionalContent');
			$textareaAdditionalContentFilterName = Filters::getBlockFilterName('textarea', 'additionalContent');
			$selectAdditionalContentFilterName = Filters::getBlockFilterName('select', 'additionalContent');
			$fileAdditionalContentFilterName = Filters::getBlockFilterName('file', 'additionalContent');
			$checkboxesAdditionalContentFilterName = Filters::getBlockFilterName('checkboxes', 'additionalContent');
			$radiosAdditionalContentFilterName = Filters::getBlockFilterName('radios', 'additionalContent');
			$submitAdditionalContentFilterName = Filters::getBlockFilterName('submit', 'additionalContent');
			$customDataOptionsFilterName = Filters::getBlockFilterName('customData', 'options');
			$breakpointsFilterName = Filters::getBlocksFilterName('breakpoints');

			$output['additionalBlocks'] = \apply_filters($additionalBlocksFilterName, []);
			$output['formsBlockStyleOptions'] = \apply_filters($formsStyleOptionsFilterName, []);
			$output['fieldBlockStyleOptions'] = \apply_filters($fieldStyleOptionsFilterName, []);
			$output['formSelectorBlockAdditionalContent'] = \apply_filters($formSelectorAdditionalContentFilterName, []);
			$output['inputBlockAdditionalContent'] = \apply_filters($inputAdditionalContentFilterName, []);
			$output['textareaBlockAdditionalContent'] = \apply_filters($textareaAdditionalContentFilterName, []);
			$output['selectBlockAdditionalContent'] = \apply_filters($selectAdditionalContentFilterName, []);
			$output['fileBlockAdditionalContent'] = \apply_filters($fileAdditionalContentFilterName, []);
			$output['checkboxesBlockAdditionalContent'] = \apply_filters($checkboxesAdditionalContentFilterName, []);
			$output['radiosBlockAdditionalContent'] = \apply_filters($radiosAdditionalContentFilterName, []);
			$output['submitBlockAdditionalContent'] = \apply_filters($submitAdditionalContentFilterName, []);
			$output['customDataBlockOptions'] = \apply_filters($customDataOptionsFilterName, []);
			$output['validationPatternsOptions'] = $this->validator->getValidationPatterns();
			$output['mediaBreakpoints'] = \apply_filters($breakpointsFilterName, []);
			$output['postType'] = \get_post_type() ? \get_post_type() : '';

			$restApiUrl = \get_rest_url(\get_current_blog_id()) . Config::getProjectRoutesNamespace() . '/' . Config::getProjectRoutesVersion() . '/';

			// Check if Geolocation data is set and valid.
			$output['useGeolocation'] = \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);
			$output['geolocationApi'] = $restApiUrl . GeolocationCountriesRoute::ROUTE_NAME;

			$output['wpAdminUrl'] = \get_admin_url();
		} else {
			// Frontend part.
			$restRoutesPath = \rest_url() . Config::getProjectRoutesNamespace() . '/' . Config::getProjectRoutesVersion();

			$hideGlobalMsgTimeoutFilterName = Filters::getBlockFilterName('form', 'hideGlobalMsgTimeout');
			$redirectionTimeoutFilterName = Filters::getBlockFilterName('form', 'redirectionTimeout');
			$previewRemoveLabelFilterName = Filters::getBlockFilterName('file', 'previewRemoveLabel');
			$hideLoadingStateTimeoutFilterName = Filters::getBlockFilterName('form', 'hideLoadingStateTimeout');

			$output = [
				'formSubmitRestApiUrl' => $restRoutesPath . '/form-submit',
				'hideGlobalMessageTimeout' => \apply_filters($hideGlobalMsgTimeoutFilterName, 6000),
				'redirectionTimeout' => \apply_filters($redirectionTimeoutFilterName, 300),
				'hideLoadingStateTimeout' => \apply_filters($hideLoadingStateTimeoutFilterName, 600),
				'fileCustomRemoveLabel' => \apply_filters($previewRemoveLabelFilterName, \esc_html__('Remove', 'eightshift-forms')),
				'formDisableScrollToFieldOnError' => $this->isCheckboxOptionChecked(
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
				),
				'formDisableScrollToGlobalMessageOnSuccess' => $this->isCheckboxOptionChecked(
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
				),
				'formDisableAutoInit' => $this->isCheckboxOptionChecked(
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY,
					SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY
				),
				'formResetOnSuccess' => !Variables::isDevelopMode(),
				'captcha' => '',
				'storageConfig' => '',
			];

			// Check if Captcha data is set and valid.
			$isCaptchaSettingsGlobalValid = \apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

			if ($isCaptchaSettingsGlobalValid) {
				$output['captcha'] = !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY);
			}

			// Localstorage allowed tags.
			$allowedTrackingTags = $this->tracking->getAllowedTags();

			if ($allowedTrackingTags) {
				$output['storageConfig'] = \wp_json_encode([
					'allowed' => $allowedTrackingTags,
					'expiration' => $this->tracking->getTrackingExpiration(),
				]);
			}
		}

		return [
			'esFormsLocalization' => $output,
		];
	}
}
