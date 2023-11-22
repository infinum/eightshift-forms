<?php

/**
 * Enqueue class used to define all script and style enqueues for Gutenberg blocks.
 *
 * @package EightshiftForms\Enqueue\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Blocks;

use EightshiftForms\Config\Config;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Enrichment\SettingsEnrichment;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\Enqueue\SharedEnqueue;
use EightshiftForms\Enqueue\Theme\EnqueueTheme;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Blocks\AbstractEnqueueBlocks;
use EightshiftFormsVendor\EightshiftLibs\Manifest\ManifestInterface;

/**
 * Enqueue_Blocks class.
 */
class EnqueueBlocks extends AbstractEnqueueBlocks
{
	/**
	 * Use shared helper trait.
	 */
	use SharedEnqueue;

	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use general helper trait.
	 */
	use FiltersOuputMock;

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
	 * Create a new admin instance.
	 *
	 * @param ManifestInterface $manifest Inject manifest which holds data about assets from manifest.json.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment which holds data about for storing to enrichment.
	 */
	public function __construct(
		ManifestInterface $manifest,
		ValidationPatternsInterface $validationPatterns,
		EnrichmentInterface $enrichment
	) {
		$this->manifest = $manifest;
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
	 * Enqueue blocks style for editor only.
	 *
	 * @return void
	 */
	public function enqueueBlockEditorStyle(): void
	{
		$handle = $this->getBlockEditorStyleHandle();

		\wp_register_style(
			$handle,
			$this->manifest->getAssetsManifestItem(static::BLOCKS_EDITOR_STYLE_URI),
			[],
			$this->getAssetsVersion(),
			$this->getMedia()
		);

		\wp_enqueue_style($handle);
	}

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
			$this->manifest->getAssetsManifestItem('applicationBlocksFrontendMandatory.css'),
			$this->getFrontendStyleDependencies(),
			$this->getAssetsVersion(),
			$this->getMedia()
		);

		\wp_enqueue_style($handle);
	}

	/**
	 * Method that returns editor and frontend style with check.
	 *
	 * @return void
	 */
	public function enqueueBlockFrontendStyleLocal(): void
	{
		if ($this->isOptionCheckboxChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return;
		}

		$this->enqueueBlockFrontendStyle();
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
	 * Get frontend script dependencies.
	 *
	 * @return array<int, string> List of all the script dependencies.
	 */
	protected function getFrontendScriptDependencies(): array
	{
		if (!\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return [];
		}

		$scriptsDependency = Filters::getFilterName(['general', 'scriptsDependency']);
		$scriptsDependencyOutput = [];

		if (\has_filter($scriptsDependency)) {
			$scriptsDependencyOutput = \apply_filters($scriptsDependency, []);
		}

		return [
			"{$this->getAssetsPrefix()}-" . EnqueueTheme::CAPTCHA_ENQUEUE_HANDLE,
			...$scriptsDependencyOutput,
		];
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
		$hideGlobalMessageTimeout = Filters::getFilterName(['block', 'form', 'hideGlobalMsgTimeout']);
		$redirectionTimeout = Filters::getFilterName(['block', 'form', 'redirectionTimeout']);
		$fileRemoveLabel = Filters::getFilterName(['block', 'file', 'previewRemoveLabel']);

		$output['hideGlobalMessageTimeout'] = \apply_filters($hideGlobalMessageTimeout, 6000);
		$output['redirectionTimeout'] = \apply_filters($redirectionTimeout, 300);
		$output['fileRemoveLabel'] = \apply_filters($fileRemoveLabel, \esc_html__('Remove', 'eightshift-forms'));
		$output['formDisableScrollToFieldOnError'] = $this->isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableScrollToGlobalMessageOnSuccess'] = $this->isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableNativeRedirectOnSuccess'] = $this->isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_NATIVE_REDIRECT_ON_SUCCESS,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableAutoInit'] = $this->isOptionCheckboxChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY
		);
		$output['formResetOnSuccess'] = !\apply_filters(SettingsDebug::FILTER_SETTINGS_IS_DEBUG_ACTIVE, SettingsDebug::SETTINGS_DEBUG_SKIP_RESET_KEY);
		$output['formServerErrorMsg'] = \esc_html__('A server error occurred while submitting your form. Please try again.', 'eightshift-forms');
		$output['formMisconfigured'] = \is_user_logged_in() ? \esc_html__('You form is missing forms block or it is missconfigured.', 'eightshift-forms') : '';

		// Enrichment config.
		if (\apply_filters(SettingsEnrichment::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			$output['enrichment'] = \array_merge(
				[
					'isUsed' => true,
					'isUsedPrefill' => $this->isOptionCheckboxChecked(SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_USE_KEY, SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_USE_KEY),
					'isUsedPrefillUrl' => $this->isOptionCheckboxChecked(SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY, SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY),
				],
				$this->getEnrichmentManualMapFilterValue($this->enrichment->getEnrichmentConfig())['config'] ?? [],
			);
		} else {
			$output['enrichment'] = [
				'isUsed' => false,
				'isUsedPrefill' => false,
				'isUsedPrefillUrl' => false,
			];
		}

		// Check if Captcha data is set and valid.
		if (\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			$output['captcha'] = [
				'isUsed' => true,
				'isEnterprise' => $this->isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY),
				'siteKey' => $this->getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaSiteKey(), SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY)['value'],
				'submitAction' => $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_SUBMIT_ACTION_DEFAULT_KEY, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'initAction' => $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_INIT_ACTION_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_INIT_ACTION_DEFAULT_KEY, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'loadOnInit' => $this->isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
				'hideBadge' => $this->isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_HIDE_BADGE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
			];
		} else {
			$output['captcha'] = [
				'isUsed' => false,
			];
		}

		$output['isAdmin'] = false;

		$output = \wp_json_encode($output);

		\wp_add_inline_script($this->getBlockFrontentScriptHandle(), "const esFormsLocalization = {$output}", 'before');
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

		$output = $this->getEnqueueSharedInlineCommonItems();

		$additionalBlocksFilterName = Filters::getFilterName(['blocks', 'additionalBlocks']);
		$formsStyleOptionsFilterName = Filters::getFilterName(['block', 'forms', 'styleOptions']);
		$fieldStyleOptionsFilterName = Filters::getFilterName(['block', 'field', 'styleOptions']);
		$breakpointsFilterName = Filters::getFilterName(['blocks', 'breakpoints']);
		$formSelectorTemplatesFilterName = Filters::getFilterName(['block', 'formSelector', 'formTemplates']);

		$output['additionalBlocks'] = \apply_filters($additionalBlocksFilterName, []);
		$output['formsBlockStyleOptions'] = \apply_filters($formsStyleOptionsFilterName, []);
		$output['fieldBlockStyleOptions'] = \apply_filters($fieldStyleOptionsFilterName, []);
		$output['validationPatternsOptions'] = $this->validationPatterns->getValidationPatternsEditor();
		$output['mediaBreakpoints'] = \apply_filters($breakpointsFilterName, []);
		$output['formsSelectorTemplates'] = \apply_filters($formSelectorTemplatesFilterName, []);
		$output['postType'] = \get_post_type() ? \get_post_type() : '';

		$output['settings'] = [
			'successRedirectVariations' => $this->getSuccessRedirectVariationOptionsFilterValue()['data'],
		];

		$output['use'] = [
			'activeIntegrations' => $this->getActiveIntegrations(),
			'geolocation' => \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false),
		];

		$output['wpAdminUrl'] = \get_admin_url();
		$output['nonce'] = \wp_create_nonce('wp_rest');
		$output['isDeveloperMode'] = \apply_filters(SettingsDebug::FILTER_SETTINGS_IS_DEBUG_ACTIVE, SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY);
		$output['isAdmin'] = true;

		$output = \wp_json_encode($output);

		\wp_add_inline_script($this->getBlockEditorScriptsHandle(), "const esFormsLocalization = {$output}", 'before');
	}
}
