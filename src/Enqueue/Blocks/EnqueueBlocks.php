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
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Rest\Routes\Editor\FormFieldsRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorCreateRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorSyncRoute;
use EightshiftForms\Rest\Routes\Editor\Options\GeolocationCountriesRoute;
use EightshiftForms\Rest\Routes\Settings\CacheDeleteRoute;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Validation\SettingsCaptcha;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Blocks\AbstractEnqueueBlocks;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
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
		if ($this->isCheckboxOptionChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
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
	 * Enqueue scripts from AbstractEnqueueBlocks, extended to expose additional data. Only Frontend.
	 *
	 * @return void
	 */
	public function enqueueBlockFrontendScript(): void
	{
		parent::enqueueBlockFrontendScript();

		$output = $this->getInlineScriptCommon();

		// Frontend part.
		$hideGlobalMessageTimeout = Filters::getFilterName(['block', 'form', 'hideGlobalMsgTimeout']);
		$redirectionTimeout = Filters::getFilterName(['block', 'form', 'redirectionTimeout']);
		$hideLoadingStateTimeout = Filters::getFilterName(['block', 'form', 'hideLoadingStateTimeout']);
		$fileCustomRemoveLabel = Filters::getFilterName(['block', 'file', 'previewRemoveLabel']);

		$output['restRoutes'] = [
			'formSubmit' => AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT,
		];

		$output['hideGlobalMessageTimeout'] = \apply_filters($hideGlobalMessageTimeout, 6000);
		$output['redirectionTimeout'] = \apply_filters($redirectionTimeout, 300);
		$output['hideLoadingStateTimeout'] = \apply_filters($hideLoadingStateTimeout, 600);
		$output['fileCustomRemoveLabel'] = \apply_filters($fileCustomRemoveLabel, \esc_html__('Remove', 'eightshift-forms'));
		$output['formDisableScrollToFieldOnError'] = $this->isCheckboxOptionChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableScrollToGlobalMessageOnSuccess'] = $this->isCheckboxOptionChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableNativeRedirectOnSuccess'] = $this->isCheckboxOptionChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_NATIVE_REDIRECT_ON_SUCCESS,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);
		$output['formDisableAutoInit'] = $this->isCheckboxOptionChecked(
			SettingsSettings::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY
		);
		$output['formResetOnSuccess'] = !$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_RESET_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);
		$output['formServerErrorMsg'] = \esc_html__('A server error occurred while submitting your form. Please try again.', 'eightshift-forms');

		// Enrichment config.
		$output['enrichmentConfig'] = \wp_json_encode($this->getEnrichmentManualMapFilterValue($this->enrichment->getEnrichmentConfig())['data']['config']);

		$output['delimiter'] = AbstractBaseRoute::DELIMITER;
		$output['captcha'] = [];

		// Check if Captcha data is set and valid.
		$isCaptchaSettingsGlobalValid = \apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

		if ($isCaptchaSettingsGlobalValid) {
			$output['captcha'] = [
				'isEnterprise' => $this->isCheckboxOptionChecked(SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY),
				'siteKey' => !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY),
				'submitAction' => $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_SUBMIT_ACTION_DEFAULT_KEY, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'initAction' => $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_INIT_ACTION_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_INIT_ACTION_DEFAULT_KEY, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'loadOnInit' => $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY) ?: false, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				'hideBadge' => $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_HIDE_BADGE_KEY) ?: false, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			];
		}

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

		$output = $this->getInlineScriptCommon();

		$additionalBlocksFilterName = Filters::getFilterName(['blocks', 'additionalBlocks']);
		$formsStyleOptionsFilterName = Filters::getFilterName(['block', 'forms', 'styleOptions']);
		$fieldStyleOptionsFilterName = Filters::getFilterName(['block', 'field', 'styleOptions']);
		$customDataOptionsFilterName = Filters::getFilterName(['block', 'customData', 'options']);
		$breakpointsFilterName = Filters::getFilterName(['blocks', 'breakpoints']);

		$output['additionalBlocks'] = \apply_filters($additionalBlocksFilterName, []);
		$output['formsBlockStyleOptions'] = \apply_filters($formsStyleOptionsFilterName, []);
		$output['fieldBlockStyleOptions'] = \apply_filters($fieldStyleOptionsFilterName, []);
		$output['customDataBlockOptions'] = \apply_filters($customDataOptionsFilterName, []);
		$output['validationPatternsOptions'] = $this->validationPatterns->getValidationPatternsEditor();
		$output['mediaBreakpoints'] = \apply_filters($breakpointsFilterName, []);
		$output['postType'] = \get_post_type() ? \get_post_type() : '';

		// phpcs:disable
		$output['additionalContent'] = [
			'formSelector' => \apply_filters(Filters::getFilterName(['block', 'formSelector', 'additionalContent']), ''),
			Components::getComponent('input')['componentName'] => \apply_filters(Filters::getFilterName(['block', 'input', 'additionalContent']), ''),
			Components::getComponent('textarea')['componentName'] => \apply_filters(Filters::getFilterName(['block', 'textarea', 'additionalContent']), ''),
			Components::getComponent('select')['componentName'] => \apply_filters(Filters::getFilterName(['block', 'select', 'additionalContent']), ''),
			Components::getComponent('file')['componentName'] => \apply_filters(Filters::getFilterName(['block', 'file', 'additionalContent']), ''),
			Components::getComponent('checkboxes')['componentName'] => \apply_filters(Filters::getFilterName(['block', 'checkboxes', 'additionalContent']), ''),
			Components::getComponent('radios')['componentName'] => \apply_filters(Filters::getFilterName(['block', 'radios', 'additionalContent']), ''),
			Components::getComponent('submit')['componentName'] => \apply_filters(Filters::getFilterName(['block', 'submit', 'additionalContent']), ''),
		];
		// phpcs:enable

		$output['settings'] = [
			'successRedirectVariations' => $this->getSuccessRedirectVariationOptionsFilterValue()['data'],
		];

		$output['use'] = [
			'geolocation' => \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false),
		];

		$output['countryDataset'] = \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

		$output['restRoutes'] = [
			'countriesGeolocation' => GeolocationCountriesRoute::ROUTE_SLUG,
			'integrationsItemsInner' => AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_ITEMS_INNER,
			'integrationsItems' => AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_ITEMS,
			'integrationsEditorSync' => IntegrationEditorSyncRoute::ROUTE_SLUG,
			'integrationsEditorCreate' => IntegrationEditorCreateRoute::ROUTE_SLUG,
			'formFields' => FormFieldsRoute::ROUTE_SLUG,
			'cacheClear' => CacheDeleteRoute::ROUTE_SLUG,
		];

		$output['wpAdminUrl'] = \get_admin_url();
		$output['nonce'] = \wp_create_nonce('wp_rest');
		$output['isDeveloperMode'] =  $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		$output = \wp_json_encode($output);

		\wp_add_inline_script($this->getBlockEditorScriptsHandle(), "const esFormsLocalization = {$output}", 'before');
	}

	/**
	 * Get common inline scripts details for frontend and editor.
	 *
	 * @return array<string, string>
	 */
	private function getInlineScriptCommon(): array
	{
		$restRoutesPrefixProject = Config::getProjectRoutesNamespace() . '/' . Config::getProjectRoutesVersion();
		$restRoutesPrefix = \get_rest_url(\get_current_blog_id()) . $restRoutesPrefixProject;

		return [
			'customFormParams' => AbstractBaseRoute::CUSTOM_FORM_PARAMS,
			'customFormDataAttributes' => AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES,
			'restPrefixProject' => $restRoutesPrefixProject,
			'restPrefix' => $restRoutesPrefix,
		];
	}
}
