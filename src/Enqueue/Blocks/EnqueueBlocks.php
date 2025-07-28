<?php

/**
 * Enqueue class used to define all script and style enqueues for Gutenberg blocks.
 *
 * @package EightshiftForms\Enqueue\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Blocks;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Enrichment\SettingsEnrichment;
use EightshiftForms\Settings\SettingsSettings;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\CustomPostType\Result;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Enqueue\SharedEnqueue;
use EightshiftForms\Enqueue\Captcha\EnqueueCaptcha;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Hooks\FiltersOutputMock;
use EightshiftForms\Validation\ValidationPatterns;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\IntegrationsHelpers;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Blocks\AbstractEnqueueBlocks;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * EnqueueBlocks class.
 */
class EnqueueBlocks extends AbstractEnqueueBlocks
{
	/**
	 * Use shared helper trait.
	 */
	use SharedEnqueue;

	/**
	 * Instance variable of enrichment data.
	 *
	 * @var EnrichmentInterface
	 */
	protected EnrichmentInterface $enrichment;

	/**
	 * Create a new admin instance.
	 *
	 * @param EnrichmentInterface $enrichment Inject enrichment which holds data about for storing to enrichment.
	 */
	public function __construct(EnrichmentInterface $enrichment)
	{
		$this->enrichment = $enrichment;
	}

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		// Editor only script.
		\add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorScript']);

		// Editor only style.
		\add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorStyle'], 50);

		// Frontend only style.
		\add_action('wp_enqueue_scripts', [$this, 'enqueueBlockFrontendStyleMandatory'], 49);
		\add_action('wp_enqueue_scripts', [$this, 'enqueueBlockFrontendStyleLocal'], 50);

		// Frontend only script.
		\add_action('wp_enqueue_scripts', [$this, 'enqueueBlockFrontendScript'], 11);
	}

	/**
	 * Method that returns assets name used to prefix asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsPrefix(): string
	{
		return Config::MAIN_PLUGIN_ENQUEUE_ASSETS_PREFIX;
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

	// -----------------------------------------------------
	// Block Editor only
	// -----------------------------------------------------

	/**
	 * Enqueue blocks style for editor only.
	 *
	 * @return void
	 */
	public function enqueueBlockEditorStyle(): void
	{
		$handle = $this->getBlockEditorStyleHandle();

		\wp_register_style(
			$handle,
			$this->setAssetsItem(static::BLOCKS_EDITOR_STYLE_URI),
			[],
			$this->getAssetsVersion(),
			$this->getMedia()
		);

		\wp_enqueue_style($handle);
	}

	/**
	 * Enqueue scripts from AbstractEnqueueBlocks, extended to expose additional data. Only Editor.
	 *
	 * @return void
	 */
	public function enqueueBlockEditorScript(): void
	{
		// If not admin exit.
		if (!\is_admin()) {
			return;
		}

		parent::enqueueBlockEditorScript();

		$output = $this->getEnqueueSharedInlineCommonItems(false);

		$additionalBlocksFilterName = HooksHelpers::getFilterName(['blocks', 'additionalBlocks']);
		$formsStyleOptionsFilterName = HooksHelpers::getFilterName(['block', 'forms', 'styleOptions']);
		$formsUseCustomResultOutputFeatureFilterName = HooksHelpers::getFilterName(['block', 'forms', 'useCustomResultOutputFeature']);
		$fieldStyleOptionsFilterName = HooksHelpers::getFilterName(['block', 'field', 'styleOptions']);
		$formSelectorTemplatesFilterName = HooksHelpers::getFilterName(['block', 'formSelector', 'formTemplates']);

		$output['additionalBlocks'] = \apply_filters($additionalBlocksFilterName, []);
		$output['formsBlockStyleOptions'] = \apply_filters($formsStyleOptionsFilterName, []);
		$output['formsUseCustomResultOutputFeature'] = \apply_filters($formsUseCustomResultOutputFeatureFilterName, false);
		$output['fieldBlockStyleOptions'] = \apply_filters($fieldStyleOptionsFilterName, []);
		$output['validationPatternsOptions'] = ValidationPatterns::getValidationPatternsEditor();
		$output['formsSelectorTemplates'] = \apply_filters($formSelectorTemplatesFilterName, []);
		$output['currentPostType'] = [
			'isForms' => \get_post_type() === Forms::POST_TYPE_SLUG,
			'isResults' => \get_post_type() === Result::POST_TYPE_SLUG,
			'isCommon' => \get_post_type() !== Forms::POST_TYPE_SLUG && \get_post_type() !== Result::POST_TYPE_SLUG,
		];
		$output['postTypes'] = [
			'results' => Result::POST_TYPE_SLUG,
			'forms' => Forms::POST_TYPE_SLUG,
		];

		$output['use'] = [
			'activeIntegrations' => IntegrationsHelpers::getActiveIntegrations(),
			'geolocation' => \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false),
		];

		$output['wpAdminUrl'] = \get_admin_url();
		$output['nonce'] = \wp_create_nonce('wp_rest');
		$output['isDeveloperMode'] = DeveloperHelpers::isDeveloperModeActive();
		$output['isAdmin'] = true;

		$output = \wp_json_encode($output);

		\wp_add_inline_script($this->getBlockEditorScriptsHandle(), "const esFormsLocalization = {$output}", 'before');
	}

	/**
	 * List block editor script dependencies.
	 *
	 * @return string[] List of all the admin dependencies.
	 */
	protected function getBlockEditorScriptDependencies(): array
	{
		$scriptsDependency = HooksHelpers::getFilterName(['scripts', 'dependency', 'blocksEditor']);
		$scriptsDependencyOutput = [];

		if (\has_filter($scriptsDependency)) {
			$scriptsDependencyOutput = \apply_filters($scriptsDependency, []);
		}

		return \array_merge(
			parent::getBlockEditorScriptDependencies(),
			[
				'lodash',
			],
			$scriptsDependencyOutput
		);
	}

	// -----------------------------------------------------
	// Block Frontend Mandatory only
	// -----------------------------------------------------

	/**
	 * Method that returns editor and frontend style with check.
	 *
	 * @return void
	 */
	public function enqueueBlockFrontendStyleMandatory(): void
	{
		$handle = "{$this->getAssetsPrefix()}-block-frontend-mandatory-style";

		\wp_register_style(
			$handle,
			$this->setAssetsItem('applicationBlocksFrontendMandatory.css'),
			[],
			$this->getAssetsVersion(),
			$this->getMedia()
		);

		\wp_enqueue_style($handle);
	}

	// -----------------------------------------------------
	// Block Frontend only
	// -----------------------------------------------------

	/**
	 * Method that returns editor and frontend style with check.
	 *
	 * @return void
	 */
	public function enqueueBlockFrontendStyleLocal(): void
	{
		if (SettingsHelpers::isOptionCheckboxChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return;
		}


		$this->enqueueBlockFrontendStyle();
	}

	/**
	 * Enqueue scripts from AbstractEnqueueBlocks, extended to expose additional data. Only Frontend.
	 *
	 * @return void
	 */
	public function enqueueBlockFrontendScript(): void
	{
		parent::enqueueBlockFrontendScript();

		$output = $this->getEnqueueSharedInlineCommonItems();

		// Frontend part.
		$hideGlobalMessageTimeout = HooksHelpers::getFilterName(['block', 'form', 'hideGlobalMsgTimeout']);
		$redirectionTimeout = HooksHelpers::getFilterName(['block', 'form', 'redirectionTimeout']);
		$fileRemoveLabel = HooksHelpers::getFilterName(['block', 'file', 'previewRemoveLabel']);

		$output['hideGlobalMessageTimeout'] = \apply_filters($hideGlobalMessageTimeout, 6000);
		$output['redirectionTimeout'] = \apply_filters($redirectionTimeout, 300);
		$output['labels'] = [
			'selectOptionAria' => \esc_html__('Select option', 'eightshift-forms'),
			'fileRemoveContent' => \apply_filters($fileRemoveLabel, \esc_html__('Remove file', 'eightshift-forms')),
			'fileRemoveAria' => \esc_html__('Remove file', 'eightshift-forms'),
		];
		$output['formDisableScrollToFieldOnError'] = SettingsHelpers::isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableScrollToGlobalMessageOnSuccess'] = SettingsHelpers::isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableAutoInit'] = SettingsHelpers::isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_AUTO_INIT_ENQUEUE_SCRIPT_KEY,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY
		);
		$output['formResetOnSuccess'] = !DeveloperHelpers::isDeveloperSkipFormResetActive();
		$output['formServerErrorMsg'] = \esc_html__('A server error occurred while submitting your form. Please try again.', 'eightshift-forms');
		$output['formCaptchaErrorMsg'] = \esc_html__('A ReCaptcha error has occurred. Please try again.', 'eightshift-forms');
		$output['formMisconfigured'] = \is_user_logged_in() ? \esc_html__('You form is missing forms block or it is misconfigured.', 'eightshift-forms') : '';

		// Enrichment config.
		if (\apply_filters(SettingsEnrichment::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			$output['enrichment'] = \array_merge(
				[
					'isUsed' => true,
					'isUsedPrefill' => SettingsHelpers::isOptionCheckboxChecked(SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_USE_KEY, SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_USE_KEY),
					'isUsedPrefillUrl' => SettingsHelpers::isOptionCheckboxChecked(SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY, SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY),
					'allowedSmart' => \array_values(\array_filter(\explode(\PHP_EOL, SettingsHelpers::getOptionValueAsJson(SettingsEnrichment::SETTINGS_ENRICHMENT_ALLOWED_SMART_TAGS_KEY, 1)))),
				],
				FiltersOutputMock::getEnrichmentManualMapFilterValue($this->enrichment->getEnrichmentConfig())['config'] ?? [],
			);
		} else {
			$output['enrichment'] = [
				'isUsed' => false,
				'isUsedPrefill' => false,
				'isUsedPrefillUrl' => false,
			];
		}

		// Geolocation config.
		$output['geolocation'] = [
			'isUsed' => \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false),
		];

		// Check if Captcha data is set and valid.
		if (\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			$output['captcha'] = [
				'isUsed' => true,
				'isEnterprise' => SettingsHelpers::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY),
				'siteKey' => SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaSiteKey(), SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY),
				'submitAction' => SettingsHelpers::getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_SUBMIT_ACTION_DEFAULT_KEY, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'initAction' => SettingsHelpers::getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_INIT_ACTION_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_INIT_ACTION_DEFAULT_KEY, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'loadOnInit' => SettingsHelpers::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
				'hideBadge' => SettingsHelpers::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_HIDE_BADGE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
			];
		} else {
			$output['captcha'] = [
				'isUsed' => false,
			];
		}

		$output['isAdmin'] = false;

		if (\is_user_logged_in()) {
			$output['nonce'] = \wp_create_nonce('wp_rest');
		}

		$output = \wp_json_encode($output);

		\wp_add_inline_script($this->getBlockFrontendScriptHandle(), "const esFormsLocalization = {$output}", 'before');
	}

	/**
	 * Get frontend script dependencies.
	 *
	 * @return array<int, string> List of all the script dependencies.
	 */
	protected function getFrontendScriptDependencies(): array
	{
		if (!\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return [];
		}

		$scriptsDependency = HooksHelpers::getFilterName(['scripts', 'dependency', 'blocksFrontend']);
		$scriptsDependencyOutput = [];

		if (\has_filter($scriptsDependency)) {
			$scriptsDependencyOutput = \apply_filters($scriptsDependency, []);
		}

		return [
			"{$this->getAssetsPrefix()}-" . EnqueueCaptcha::CAPTCHA_ENQUEUE_HANDLE,
			...$scriptsDependencyOutput,
		];
	}
}
