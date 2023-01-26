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
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorCreateRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorSyncRoute;
use EightshiftForms\Rest\Routes\Editor\Options\GeolocationCountriesRoute;
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
		$restRoutesPrefixProject = Config::getProjectRoutesNamespace() . '/' . Config::getProjectRoutesVersion();
		$restRoutesPrefix = \get_rest_url(\get_current_blog_id()) . $restRoutesPrefixProject;

		$output = [
			'customFormParams' => AbstractBaseRoute::CUSTOM_FORM_PARAMS,
			'customFormDataAttributes' => AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES,
			'restPrefixProject' => $restRoutesPrefixProject,
			'restPrefix' => $restRoutesPrefix,
		];

		// Admin part.
		if (\is_admin()) {
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
			];

			$output['wpAdminUrl'] = \get_admin_url();
		} else {
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
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
			);
			$output['formDisableScrollToGlobalMessageOnSuccess'] = $this->isCheckboxOptionChecked(
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
			);
			$output['formDisableAutoInit'] = $this->isCheckboxOptionChecked(
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY
			);
			$output['formResetOnSuccess'] = !$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_RESET_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);
			$output['formServerErrorMsg'] = \esc_html__('A server error occurred while submitting your form. Please try again.', 'eightshift-forms');

			// Enrichment config.
			$output['enrichmentConfig'] = \wp_json_encode($this->enrichment->getEnrichmentConfig());

			$output['captcha'] = '';

			// Check if Captcha data is set and valid.
			$isCaptchaSettingsGlobalValid = \apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

			if ($isCaptchaSettingsGlobalValid) {
				$output['captcha'] = !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY);
			}
		}

		return [
			'esFormsLocalization' => $output,
		];
	}
}
