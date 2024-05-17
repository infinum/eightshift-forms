<?php

/**
 * Enqueue class used to define all script and style enqueues for Gutenberg blocks.
 *
 * @package EightshiftForms\Enqueue\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Blocks;

use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Enrichment\SettingsEnrichment;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\CustomPostType\Result;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Enqueue\SharedEnqueue;
use EightshiftForms\Enqueue\Captcha\EnqueueCaptcha;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsIntegrationsHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Cache\ManifestCacheInterface;
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
	 * Instance variable of ValidationPatternsInterface data.
	 *
	 * @var ValidationPatternsInterface
	 */
	protected $validationPatterns;

	/**
	 * Instance variable of enrichment data.
	 *
	 * @var EnrichmentInterface
	 */
	protected EnrichmentInterface $enrichment;

	/**
	 * Instance variable for manifest cache.
	 *
	 * @var ManifestCacheInterface
	 */
	protected $manifestCache;

	/**
	 * Create a new admin instance.
	 *
	 * @param ManifestCacheInterface $manifestCache Inject manifest cache.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment which holds data about for storing to enrichment.
	 */
	public function __construct(
		ManifestCacheInterface $manifestCache,
		ValidationPatternsInterface $validationPatterns,
		EnrichmentInterface $enrichment
	) {
		$this->manifestCache = $manifestCache;
		$this->validationPatterns = $validationPatterns;
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

	// -----------------------------------------------------
	// Block Editor only
	// -----------------------------------------------------

	/**
	 * Enqueue blocks style for editor only.
	 *
	 * @param string $hook Hook name.
	 *
	 * @return void
	 */
	public function enqueueBlockEditorStyle(string $hook): void
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
	 * @param string $hook Hook name.
	 *
	 * @return void
	 */
	public function enqueueBlockEditorScript(string $hook): void
	{
		// If not admin exit.
		if (!\is_admin()) {
			return;
		}

		parent::enqueueBlockEditorScript($hook);

		$output = $this->getEnqueueSharedInlineCommonItems(false);

		$additionalBlocksFilterName = UtilsHooksHelper::getFilterName(['blocks', 'additionalBlocks']);
		$formsStyleOptionsFilterName = UtilsHooksHelper::getFilterName(['block', 'forms', 'styleOptions']);
		$fieldStyleOptionsFilterName = UtilsHooksHelper::getFilterName(['block', 'field', 'styleOptions']);
		$breakpointsFilterName = UtilsHooksHelper::getFilterName(['blocks', 'mediaBreakpoints']);
		$formSelectorTemplatesFilterName = UtilsHooksHelper::getFilterName(['block', 'formSelector', 'formTemplates']);

		$output['additionalBlocks'] = \apply_filters($additionalBlocksFilterName, []);
		$output['formsBlockStyleOptions'] = \apply_filters($formsStyleOptionsFilterName, []);
		$output['fieldBlockStyleOptions'] = \apply_filters($fieldStyleOptionsFilterName, []);
		$output['validationPatternsOptions'] = $this->validationPatterns->getValidationPatternsEditor();
		$output['mediaBreakpoints'] = \apply_filters($breakpointsFilterName, []);
		$output['formsSelectorTemplates'] = \apply_filters($formSelectorTemplatesFilterName, []);
		$output['currentPostType'] = \get_post_type() ? \get_post_type() : '';
		$output['postTypes'] = [
			'results' => Result::POST_TYPE_SLUG,
			'forms' => Forms::POST_TYPE_SLUG,
		];

		$output['settings'] = [
			'successRedirectVariations' => FiltersOuputMock::getSuccessRedirectVariationOptionsFilterValue()['data'],
		];

		$output['use'] = [
			'activeIntegrations' => UtilsIntegrationsHelper::getActiveIntegrations(),
			'geolocation' => \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false),
		];

		$output['wpAdminUrl'] = \get_admin_url();
		$output['nonce'] = \wp_create_nonce('wp_rest');
		$output['isDeveloperMode'] = UtilsDeveloperHelper::isDeveloperModeActive();
		$output['isAdmin'] = true;

		$output = \wp_json_encode($output);

		\wp_add_inline_script($this->getBlockEditorScriptsHandle(), "const esFormsLocalization = {$output}", 'before');
	}

	/**
	 * List of admin script dependencies
	 *
	 * @return string[] List of all the admin dependencies.
	 */
	protected function getAdminScriptDependencies(): array
	{
		$scriptsDependency = UtilsHooksHelper::getFilterName(['scripts', 'dependency', 'blocksEditor']);
		$scriptsDependencyOutput = [];

		if (\has_filter($scriptsDependency)) {
			$scriptsDependencyOutput = \apply_filters($scriptsDependency, []);
		}

		return \array_merge(
			parent::getAdminScriptDependencies(),
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
			$this->getFrontendStyleDependencies(),
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
	 * @param string $hook Hook name.
	 *
	 * @return void
	 */
	public function enqueueBlockFrontendStyleLocal(string $hook): void
	{
		if (UtilsSettingsHelper::isOptionCheckboxChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return;
		}


		$this->enqueueBlockFrontendStyle($hook);
	}

	/**
	 * Enqueue scripts from AbstractEnqueueBlocks, extended to expose additional data. Only Frontend.
	 *
	 * @param string $hook Hook name.
	 *
	 * @return void
	 */
	public function enqueueBlockFrontendScript(string $hook): void
	{
		parent::enqueueBlockFrontendScript($hook);

		$output = $this->getEnqueueSharedInlineCommonItems();

		// Frontend part.
		$hideGlobalMessageTimeout = UtilsHooksHelper::getFilterName(['block', 'form', 'hideGlobalMsgTimeout']);
		$redirectionTimeout = UtilsHooksHelper::getFilterName(['block', 'form', 'redirectionTimeout']);
		$fileRemoveLabel = UtilsHooksHelper::getFilterName(['block', 'file', 'previewRemoveLabel']);

		$output['hideGlobalMessageTimeout'] = \apply_filters($hideGlobalMessageTimeout, 6000);
		$output['redirectionTimeout'] = \apply_filters($redirectionTimeout, 300);
		$output['fileRemoveLabel'] = \apply_filters($fileRemoveLabel, \esc_html__('Remove', 'eightshift-forms'));
		$output['formDisableScrollToFieldOnError'] = UtilsSettingsHelper::isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableScrollToGlobalMessageOnSuccess'] = UtilsSettingsHelper::isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableNativeRedirectOnSuccess'] = UtilsSettingsHelper::isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_NATIVE_REDIRECT_ON_SUCCESS,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableAutoInit'] = UtilsSettingsHelper::isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY
		);
		$output['formResetOnSuccess'] = !UtilsDeveloperHelper::isDeveloperSkipFormResetActive();
		$output['formServerErrorMsg'] = \esc_html__('A server error occurred while submitting your form. Please try again.', 'eightshift-forms');
		$output['formCaptchaErrorMsg'] = \esc_html__('A ReCaptcha error has occured. Please try again.', 'eightshift-forms');
		$output['formMisconfigured'] = \is_user_logged_in() ? \esc_html__('You form is missing forms block or it is missconfigured.', 'eightshift-forms') : '';

		// Enrichment config.
		if (\apply_filters(SettingsEnrichment::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			$output['enrichment'] = \array_merge(
				[
					'isUsed' => true,
					'isUsedPrefill' => UtilsSettingsHelper::isOptionCheckboxChecked(SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_USE_KEY, SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_USE_KEY),
					'isUsedPrefillUrl' => UtilsSettingsHelper::isOptionCheckboxChecked(SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY, SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY),
				],
				FiltersOuputMock::getEnrichmentManualMapFilterValue($this->enrichment->getEnrichmentConfig())['config'] ?? [],
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
				'isEnterprise' => UtilsSettingsHelper::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY),
				'siteKey' => UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaSiteKey(), SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY)['value'],
				'submitAction' => UtilsSettingsHelper::getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_SUBMIT_ACTION_DEFAULT_KEY, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'initAction' => UtilsSettingsHelper::getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_INIT_ACTION_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_INIT_ACTION_DEFAULT_KEY, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'loadOnInit' => UtilsSettingsHelper::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
				'hideBadge' => UtilsSettingsHelper::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_HIDE_BADGE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
			];
		} else {
			$output['captcha'] = [
				'isUsed' => false,
			];
		}

		$output['isAdmin'] = false;

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

		$scriptsDependency = UtilsHooksHelper::getFilterName(['scripts', 'dependency', 'blocksFrontend']);
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
