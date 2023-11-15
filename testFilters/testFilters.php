<?php

/**
 * Class that holds class for admin sub menu - Form Listing.
 *
 * @package EightshiftForms\Testfilters
 */

declare(strict_types=1);

namespace EightshiftForms\Testfilters;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Test class used for mocking filters.
 */
class Testfilters implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		$filters = [
			'es_forms_block_forms_style_options' => ['getBlockFormsStyleOptions'],
			'es_forms_block_form_redirection_timeout' => ['getBlockFormRedirectionTimeout'],
			'es_forms_block_form_hide_global_msg_timeout' => ['getBlockFormHideGlobalMsgTimeout'],
			'es_forms_block_form_success_redirect_url' => ['getBlockFormSuccessRedirectUrl', 2],
			'es_forms_block_form_success_redirect_variation' => ['getBlockFormSuccessRedirectVariation', 2],
			'es_forms_block_form_success_redirect_variation_options' => ['getBlockFormSuccessRedirectVariationOptions'],
			'es_forms_block_form_tracking_event_name' => ['getBlockFormTrackingEventName', 2],
			'es_forms_block_form_tracking_additional_data' => ['getBlockFormTrackingAdditinalData', 2],
			'es_forms_block_form_data_type_selector' => ['getFormDataTypeSelector', 2],
			'es_forms_block_form_phone_sync' => ['getFormPhoneSync', 2],
			'es_forms_block_form_global_msg_headings' => ['getGlobalMsgHeadings'],

			'es_forms_block_form_selector_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_form_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_field_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_input_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_textarea_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_select_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_file_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_checkboxes_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_radios_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_phone_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_country_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_date_additional_content' => ['getBlockFormSelectorAdditionalContent'],
			'es_forms_block_submit_additional_content' => ['getBlockFormSelectorAdditionalContent'],

			'es_forms_block_form_selector_form_templates' => ['getBlockFormSelectorFormTemplates'],

			'es_forms_block_field_style_options' => ['getBlockFieldStyleOptions'],
			'es_forms_block_field_style_classes' => ['getBlockFieldStyleClasses'],

			'es_forms_block_file_preview_remove_label' => ['getBlockFilePreviewRemoveLabel'],

			'es_forms_block_country_alternative_data_set' => ['getBlockCountryAlternativeDataSet'],

			'es_forms_block_custom_data_options' => ['getBlockCustomDataOptions'],

			'es_forms_block_submit_component' => ['getBlockSubmitComponent'],

			'es_forms_block_submit_component_prev' => ['getBlockSubmitComponentPrev'],
			'es_forms_block_submit_component_next' => ['getBlockSubmitComponentNext'],

			// ---------------------------------------------------------------------------------------------------------
			// Blocks filters.
			'es_forms_blocks_additional_blocks' => ['getAdditionalBlocks'],

			'es_forms_blocks_media_breakpoints' => ['getMediaBreakpoints'],

			// ---------------------------------------------------------------------------------------------------------
			// General filters.
			'es_forms_general_http_request_timeout' => ['getHttpRequestTimeout'],

			'es_forms_general_locale' => ['getFormsLocale'],

			'es_forms_general_scripts_dependency' => ['getScriptsDependency'],

			// ---------------------------------------------------------------------------------------------------------
			// Geolocation filters.
			'es_forms_geolocation_countries_list' => ['getGeolocationCountriesList'],
			'es_forms_geolocation_disable' => ['getGeolocationDisable'],
			'es_forms_geolocation_db_location' => ['getGeolocationDbLocation'],
			'es_forms_geolocation_phar_location' => ['getGeolocationPharLocation'],

			// ---------------------------------------------------------------------------------------------------------
			// Integrations filters.
			'es_forms_integrations_greenhouse_data' => ['getGreenhouseIntegrationData', 2], // Dynamic name based on the integration type.
			'es_forms_integrations_workable_data' => ['getWorkableIntegrationData', 2], // Dynamic name based on the integration type.
			'es_forms_integrations_workable_pre_post_params' => ['getWorkableIntegrationPrePostParams'], // Dynamic name based on the integration type.
			'es_forms_integrations_workable_pre_post_id' => ['getWorkableIntegrationPrePostId', 3], // Dynamic name based on the integration type.
			'es_forms_integrations_workable_order' => ['getWorkableIntegrationOrder'], // Dynamic name based on the integration type.
			'es_forms_integrations_hubspot_files_options' => ['getFileUploadCustomOptions'],
			'es_forms_integrations_clearbit_map' => ['getClearbitFieldsMap'],

			// ---------------------------------------------------------------------------------------------------------
			// Enrichment filters.
			'es_forms_enrichment_manual_map' => ['getEnrichmentManualMap'],

			// ---------------------------------------------------------------------------------------------------------
			// Validation filters.
			'es_forms_validation_force_mimetype_from_fs' => ['forceMimetypeFs'],
		];

		$actions = [
			// Validation actions.
			'es_forms_migration_two_to_three_general' => ['runMigration2To3General'],
			'es_forms_migration_two_to_three_forms' => ['runMigration2To3Forms'],
			'es_forms_migration_two_to_three_labels' => ['runMigration2To3Labels'],
			'es_forms_migration_two_to_three_locale' => ['runMigration2To3Locale'],
			// ---------------------------------------------------------------------------------------------------------
		];

		// Turn off if constant is not set.
		if (\defined('ES_RUN_TEST_FILTERS')) {
			if (\ES_RUN_TEST_FILTERS === 'all') {
				// Loop all filters.
				foreach ($filters as $key => $value) {
					\add_filter($key, [$this, $value[0]], 10, $value[1] ?? 1);
				}
			} else {
				$filter = $filters[\ES_RUN_TEST_FILTERS] ?? '';

				if ($filter) {
					\add_filter(\ES_RUN_TEST_FILTERS, [$this, $filters[\ES_RUN_TEST_FILTERS][0]], 10, $filters[\ES_RUN_TEST_FILTERS][1] ?? 1);
				}
			}
		}

		if (\defined('ES_RUN_TEST_ACTIONS')) {
			if (\ES_RUN_TEST_ACTIONS === 'all') {
				// Loop all actions.
				foreach ($actions as $key => $value) {
					\add_action($key, [$this, $value[0]], 10, $value[1] ?? 1);
				}
			} else {
				$action = $actions[\ES_RUN_TEST_ACTIONS] ?? '';

				if ($action) {
					\add_action(\ES_RUN_TEST_ACTIONS, [$this, $actions[\ES_RUN_TEST_ACTIONS][0]], 10, $actions[\ES_RUN_TEST_FILTERS][1] ?? 1);
				}
			}
		}
	}

	// -----------------------------------------------------------------------------------------------------------
	// Block filters.

	/**
	 * Add additional style options to forms block.
	 *
	 * This filter will add new options to the style select dropdown in the forms block. Forms style option selector will not show unless a filter is provided. This option is shown in Block Editor.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function getBlockFormsStyleOptions(): array
	{
		return [
			[
				'label' => 'Default',
				'value' => 'default'
			],
			[
				'label' => 'Custom Style',
				'value' => 'custom-style'
			],
		];
	}

	/**
	 * Changing the default success redirection wait time
	 *
	 * This filter will override our default wait time once the form returns success and it is redirected. The time is calculated in milliseconds. *Example: 1000ms = 1s*.
	 *
	 * @return string
	 */
	public function getBlockFormRedirectionTimeout(): string
	{
		return '1000'; // 1 seconds.
	}

	/**
	 * Changing the default success hide global message wait time.
	 *
	 * This filter will override our default wait time before the global message is removed. The time is calculated in milliseconds. *Example: 1000ms = 1s*.
	 *
	 * @return string
	 */
	public function getBlockFormHideGlobalMsgTimeout(): string
	{
		return '10000'; // 10 seconds.
	}

	/**
	 * Set success redirect url value.
	 *
	 * This filter will override settings for success redirect url.
	 *
	 * @param string $formType Type of form used like greenhouse, hubspot, etc.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getBlockFormSuccessRedirectUrl(string $formType, string $formId): string
	{
		return 'https://infinum.com/custom-filter';
	}

	/**
	 * Set success redirect variation value.
	 *
	 * This filter will override settings for success redirect variation.
	 *
	 * @param string $formType Type of form used like greenhouse, hubspot, etc.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getBlockFormSuccessRedirectVariation(string $formType, string $formId): string
	{
		return 'aaa';
	}

	/**
	 * Set success redirect variation options value.
	 *
	 * This filter will override settings for success redirect variation options.
	 *
	 * @return array<string, string>
	 */
	public function getBlockFormSuccessRedirectVariationOptions(): array
	{
		return [
			[
				'test1',
				'label1',
			],
			[
				'test2',
				'label2',
			],
		];
	}

	/**
	 * Set tracking event name value.
	 *
	 * This filter will override settings for tracking event name.
	 *
	 * @param string $formType Type of form used like greenhouse, hubspot, etc.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getBlockFormTrackingEventName(string $formType, string $formId): string
	{
		return 'Event-Name-Filter';
	}

	/**
	 * Set tracking additional data and that data will be send to the GTM along with all field values and event name.
	 *
	 * This filter will override settings for tracking additiona data.
	 *
	 * @param string $formType Type of form used like greenhouse, hubspot, etc.
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<int, string>>
	 */
	public function getBlockFormTrackingAdditinalData(string $formType, string $formId): array
	{
		return [
			'general' => [
				[
					'customKey',
					'customValue',
				],
				[
					'additionalKey',
					'additionalValue',
				],
			],
			'success' => [
				[
					'successKey',
					'successValue',
				],
				[
					'successAdditionalKey',
					'successAdditionalValue',
				],
			],
			'error' => [
				[
					'errorKey',
					'errorValue',
				],
				[
					'errorAdditionalKey',
					'errorAdditionalValue',
				],
			],
		];
	}

	/**
	 * Changing the form type selector on render
	 * This filter will override the attribute-provided type selector for a Form component.
	 * Passes form component attributes to the callback function as well, so you can check all sorts of conditions when filtering.
	 *
	 * In other words, you can use this filter to change the value of the `formDataTypeSelector` attribute during a form render.
	 * The attribute is used to output a `data-type-selector` HTML attribute of the form element.
	 *
	 * @param string $selector The data type selector to filter.
	 * @param array<mixed> $attr Form component attributes.
	 *
	 * @return string Filtered value.
	 */
	public function getFormDataTypeSelector(string $selector, array $attr): string
	{
		if (($attr['formType'] ?? '') === 'mailchimp') {
			return '';
		}

		return 'my-new-selector';
	}

	/**
	 * Set phone sync settings.
	 *
	 * This filter will override global settings for phone sync.
	 *
	 * @param string $formType Type of form used like greenhouse, hubspot, etc.
	 * @param string $formId Form ID.
	 *
	 * @return bool
	 */
	public function getFormPhoneSync(string $formType, string $formId): bool
	{
		if ($formType === 'hubspot') {
			return true;
		}

		return false;
	}

	/**
	 * Set global msg headings.
	 *
	 * This filter will set global message headings for success and error.
	 *
	 * @return array<string, string>
	 */
	public function getGlobalMsgHeadings(): array
	{
		return [
			'success' => \__('Good news!', 'eightshift-form'),
			'error' => \__('Something is going wrong.', 'eightshift-form'),
		];
	}

	/**
	 * Adding additional content in blocks
	 *
	 * This filter is used if you want to add some custom string/component/css variables, etc. to the block. By changing the name of the filter you will target different blocks.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 *
	 * @return string
	 */
	public function getBlockFormSelectorAdditionalContent($attributes): string
	{
		return 'custom string';
	}

	/**
	 * Add additional forms templates in blocks form selector.
	 *
	 * @return array<int, mixed>
	 */
	public function getBlockFormSelectorFormTemplates(): array
	{
		return [
			[
				"label" => "Test Forms",
				"slug" => "test-form",
				"blockName" => "eightshift-forms/mailer",
				'icon' => "<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20' fill='none'><path d='M7 1H2.5A1.5 1.5 0 0 0 1 2.5V7a1.5 1.5 0 0 0 1.5 1.5H7A1.5 1.5 0 0 0 8.5 7V2.5A1.5 1.5 0 0 0 7 1Zm0 10.5H2.5A1.5 1.5 0 0 0 1 13v4.5A1.5 1.5 0 0 0 2.5 19H7a1.5 1.5 0 0 0 1.5-1.5V13A1.5 1.5 0 0 0 7 11.5ZM17.5 1H13a1.5 1.5 0 0 0-1.5 1.5V7A1.5 1.5 0 0 0 13 8.5h4.5A1.5 1.5 0 0 0 19 7V2.5A1.5 1.5 0 0 0 17.5 1Zm0 10.5H13a1.5 1.5 0 0 0-1.5 1.5v4.5A1.5 1.5 0 0 0 13 19h4.5a1.5 1.5 0 0 0 1.5-1.5V13a1.5 1.5 0 0 0-1.5-1.5Z' stroke='currentColor' stroke-linecap='round' fill='none'/></svg>",
				"innerBlocks" => [
					[
						"eightshift-forms/input",
						[
							"inputInputFieldLabel" => "E-mail",
							"inputInputType" => "email",
							"inputInputName" => "email",
							"inputInputIsRequired" => true,
							"inputInputIsEmail" => true,
							"inputInputDisabledOptions" => [
								"inputInputIsRequired",
								"inputInputName"
							]
						],
					],
					[
						"eightshift-forms/textarea",
						[
							"textareaTextareaFieldLabel" => "Message",
							"textareaTextareaName" => "message",
						],
					],
					[
						"eightshift-forms/submit"
					],
				]
			],
		];
	}

	/**
	 * Add additional style options to field block
	 *
	 * This filter will add new options to the style select dropdown in the field block. Field style option selector will not show unless a filter is provided. This option is shown in Block Editor.
	 *
	 * @return array<string, mixed>
	 */
	public function getBlockFieldStyleOptions(): array
	{
		return [
			'input' => [
				[
					'label' => 'Default',
					'value' => 'default'
				],
				[
					'label' => 'Custom Style',
					'value' => 'custom-style'
				],
			],
			'select' => [
				[
					'label' => 'Custom Style',
					'value' => 'custom-style',
				],
			]
		];
	}

	/**
	 * Add additional style classet to field block.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 *
	 * @return array<string, mixed>
	 */
	public function getBlockFieldStyleClasses(array $attributes): array
	{
		return [
			'input' => [
				'custom-style',
			]
		];
	}

	/**
	 * Changing the default custom file preview remove label.
	 *
	 * This filter will override our default file preview remove label.
	 *
	 * @return string
	 */
	public function getBlockFilePreviewRemoveLabel(): string
	{
		return 'Remove item'; // This can be string or svg.
	}

	/**
	 * Get country alternative changes for data set and provide filters.
	 *
	 * This filter will only provide alternative options and change the original list.
	 *
	 * @return array
	 */
	public function getBlockCountryAlternativeDataSet(): array
	{
		return [
			[
				'label' => 'New List',
				'slug' => 'new-list',
				'remove' => [
					'cz',
					'us',
				],
				'change' => [
					'hr' => 'New Albania',
				],
				'onlyUse' => [
					'de',
					'gb',
					'hr',
					'cz',
				],
			],
			[
				'label' => 'Cool List',
				'slug' => 'cool-list',
				'onlyUse' => [
					'ba',
					'jp',
					'gb',
					'fr',
				],
			],
		];
	}

	/**
	 * Add to custom data block
	 *
	 * These filters will add the necessary data for the custom data block to work. Field data option selector will not be shown unless a filter is added. This option is shown in Block Editor.
	 *
	 * @return array<string, mixed>
	 */
	public function getBlockCustomDataOptions(): array
	{
		return [
			[
				'label' => '',
				'value' => '',
				'items' => [],
			],
			[
				'label' => 'Blog posts',
				'value' => 'blog-posts',
				"items" => [
					[
						'label' => '',
						'value' => ''
					],
					[
						'label' => 'Post 1',
						'value' => 'post1'
					],
					[
						"label" => "Post 2",
						"value" => "post2"
					],
				],
			],
			[
				"label" => "Jobs",
				"value" => "jobs",
				"items" => [
					[
						'label' => '',
						'value' => ''
					],
					[
						'label' => 'Job 1',
						'value' => 'job1'
					],
					[
						"label" => "Job 2",
						"value" => "job2"
					],
				],
			],
		];
	}

	/**
	 * Override default submit button with your own component
	 *
	 * This filter will remove the default forms submit button component and use your callback. This will not apply to form settings pages.
	 *
	 * @param array<string, mixed> $data Data provided from the forms.
	 *
	 * @return string
	 */
	public function getBlockSubmitComponent(array $data): string
	{
		return '';
	}

	/**
	 * Override default step prev submit button with your own component.
	 *
	 * @param array<string, mixed> $data Data provided from the forms.
	 *
	 * @return string
	 */
	public function getBlockStepComponentPrev(array $data): string
	{
		return 'Prev';
	}

	/**
	 * Override default step next submit button with your own component.
	 *
	 * @param array<string, mixed> $data Data provided from the forms.
	 *
	 * @return string
	 */
	public function getBlockStepComponentNext(array $data): string
	{
		return 'Next';
	}

	// -----------------------------------------------------------------------------------------------------------
	// Blocks filters.

	/**
	 * Adding additional blocks in the custom forms block.
	 *
	 * This filter is used if you want to add your custom or core blocks to the custom form builder.
	 *
	 * @return array<int, string>
	 */
	public function getAdditionalBlocks(): array
	{
		return [
			'core/heading',
			'core/paragraph',
		];
	}

	/**
	 * Changing the default media breakpoints.
	 *
	 * This filter will override our default media breakpoints used for responsive selectors like widths of the form fields. You must provide all 4 breakpoints in order for this to work properly and you must follow our breakpoint names.
	 *
	 * @return array<string, int>
	 */
	public function getMediaBreakpoints(): array
	{
		return [
			'mobile' => 200,
			'tablet' => 500,
			'desktop' => 800,
			'large' => 1200
		];
	}

	// -----------------------------------------------------------------------------------------------------------
	// General filters.

	/**
	 * Change http request timeout.
	 *
	 * This filter can be used to change the cURL timeout for the file upload, useful if you have to upload large files.
	 *
	 * @return int
	 */
	public function getHttpRequestTimeout(): int
	{
		return 50;
	}

	/**
	 * Change the current locale.
	 *
	 * This filter can be used to change the value of current locale. By default WordPress sets the locale in the admin to `en_US` and with this filter it can be changed to whichever locale is needed (e.g. when using multilanguage plugin).
	 *
	 * @param string $locale Default locale from WordPress.
	 *
	 * @return string
	 */
	public function getFormsLocale(string $locale): string
	{
		// Get the custom locale (e.g. from WPML plugin).
		return $locale;
	}

	/**
	 * Provide custom scripts dependency.
	 *
	 * This filter will provide you with the ability to add custom scripts dependency so you can load your project scripts before the forms scripts.
	 *
	 * @return string<int, string>
	 */
	public function getScriptsDependency(): array
	{
		return [];
	}

	// -----------------------------------------------------------------------------------------------------------
	// Geolocation filters.

	/**
	 * Change default countries list.
	 *
	 * This filter provides you with the ability to add/remove/edit countries list and countries groups.
	 *
	 * @param array<mixed> $countries Countries list from internal db.
	 *
	 * @return array<mixed>
	 */
	public function getGeolocationCountriesList(array $countries): array
	{
		return \array_merge(
			$countries,
			[
				[
					'label' => \__('<country-name>', 'text-domain'),
					'value' => '<country-value>',
					'group' => [
						'<country-value>',
					],
				],
			],
		);
	}

	/**
	 * Provide custom geolocation db location.
	 *
	 * This filter provides you with the ability to provide custom database location for geolocation.
	 *
	 * @return string
	 */
	public function getGeolocationDbLocation(): string
	{
		return __DIR__ . \DIRECTORY_SEPARATOR . 'geoip.mmdb';
	}

	/**
	 * Provide custom geolocation phar location.
	 *
	 * This filter provides you with the ability to provide custom database location for geolocation.
	 *
	 * @return string
	 */
	public function getGeolocationPharLocation(): string
	{
		return __DIR__ . \DIRECTORY_SEPARATOR . 'geoip.phar';
	}

	// -----------------------------------------------------------------------------------------------------------
	// Integrations filters.

	/**
	 * Change form fields data before output.
	 *
	 * This filter is used if you want to change form fields data before output. By changing the name of the filter you will target different integrations.
	 *
	 * @param array<string, mixed> $data Array of component/attributes data.
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public function getWorkableIntegrationData(array $data, string $formId): array
	{
		return \array_merge(
			$data,
			[
				[
					'component' => 'input',
					'inputFieldHidden' => true,
					'inputFieldLabel' => 'utm_source',
					'inputName' => 'utm_source',
					'inputDisabledOptions' => [
						'inputName',
						'inputType',
						'inputFieldLabel',
						'inputFieldHidden',
					],
				],
				[
					'component' => 'input',
					'inputFieldHidden' => true,
					'inputFieldLabel' => 'utm_medium',
					'inputName' => 'utm_medium',
					'inputDisabledOptions' => [
						'inputName',
						'inputType',
						'inputFieldLabel',
						'inputFieldHidden',
					],
				],
			]
		);
	}

	/**
	 * Change form fields data before we send it to the external integration.
	 *
	 * @param array<string, mixed> $params Array of params.
	 *
	 * @return array<string, mixed>
	 */
	public function getWorkableIntegrationPrePostParams(array $params): array
	{

		$medium = isset($params['utm_source']['value']) ? \ucfirst($params['utm_source']['value']) : 'Unknown';
		$source = isset($params['utm_medium']['value']) ? '(' . \ucfirst($params['utm_medium']['value']) . ')' : '';

		$params['domain'] = [
			'name' => 'domain',
			'value' => "{$medium}{$source}",
			'type' => 'text',
			'typeCustom' => '',
		];

		unset($params['utm_source']);
		unset($params['utm_medium']);

		return $params;
	}

	/**
	 *
	 * Change integration ID fields data before we send it to the external integration.
	 *
	 * @param string $itemId Item id.
	 * @param array<mixed> $params Params to alterate.
	 * @param string $formId Form id.
	 *
	 * @return string
	 */
	public function getWorkableIntegrationPrePostId(
		string $itemId,
		array $params,
		string $formId
	): string {
		return $itemId;
	}

	/**
	 * Change form fields order before output.
	 *
	 * @return array<int, string>
	 */
	public function getWorkableIntegrationOrder(): array
	{
		return [
			'firstname',
			'lastname',
			'email',
			'phone',
			'headline',
			'summary',
		];
	}

	/**
	 * Change Hubspot file upload options.
	 *
	 * This filter is used to change default file upload options set by forms and Hubspot. We use this [api](https://legacydocs.hubspot.com/docs/methods/files/v3/upload_new_file), and you can change any of these options.
	 *
	 * @return array<mixed>
	 */
	public function getFileUploadCustomOptions(): array
	{
		return [
			'folderPath' => '/esforms',
			'options' => \wp_json_encode([
				"access" => "PUBLIC_NOT_INDEXABLE",
				"overwrite" => false,
			]),
		];
	}

	/**
	 * Change Clearbit integration fields map.
	 *
	 * This filter provides you the ability to change how we map Clearbit fields so you can combine multiple fields in one add some new one.
	 *
	 * @param array $params Default params provided by the forms.
	 *
	 * @return array<mixed>
	 */
	public function getClearbitFieldsMap(array $params): array
	{
		$street = $params['company-street-number'] ?? '';
		$city = $params['company-city'] ?? '';
		$postalCode = $params['company-postal-code'] ?? '';

		$params['company-location-combined'] = "{$street} {$city} {$postalCode}";

		return $params;
	}

	// -----------------------------------------------------------------------------------------------------------
	// Enrichment filters.

	/**
	 * Manual map enrichment array.
	 *
	 * This filter provides you with the ability to manualy map enrichment array combined with settings data.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function getEnrichmentManualMap(): array
	{
		return [
			'workable' => [
				'utm_source' => [
					'utm_source',
				],
				'utm_medium' => [
					'utm_medium',
				],
			],
			'moments' => [
				'__IB_LT_ga_client_id' => [
					'ga_client_id',
					'miro',
					'pero',
				],
				'aaaa' => [
					'ffff',
					'vvv',
					'rrr',
				],
			],
		];
	}

	// -----------------------------------------------------------------------------------------------------------
	// Validation filters.

	/**
	 * Force mimetype validation from filesystem values.
	 *
	 * By default, mime-types are validated from the filesystem mimetype.
	 * However, in case the file is not present on the filesystem for any reason, this will fall back to the POST-provided mimetype.
	 *
	 * Using this filter, you can force Eightshift Forms to fail every file upload where it can't validate the mimetype from the filesystem.
	 *
	 * @return bool
	 */
	public function forceMimetypeFs(): bool
	{
		return true;
	}

	// -----------------------------------------------------------------------------------------------------------
	// Migration actions.

	/**
	 * Run custom action after migration from verson 2 to 3 - general.
	 *
	 * @return void
	 */
	public function runMigration2To3General(): void
	{
		error_log(print_r(('Action hook activated 2-3 general'), true)); // phpcs:ignore
	}

	/**
	 * Run custom action after migration from verson 2 to 3 - forms.
	 *
	 * @return void
	 */
	public function runMigration2To3Forms(): void
	{
		error_log(print_r(('Action hook activated 2-3 forms'), true)); // phpcs:ignore
	}

	/**
	 * Run custom action after migration from verson 2 to 3 - locale.
	 *
	 * @return void
	 */
	public function runMigration2To3Locale(): void
	{
		error_log(print_r(('Action hook activated 2-3 locale'), true)); // phpcs:ignore
	}

	/**
	 * Run custom action after migration from verson 2 to 3 - labels.
	 *
	 * @return void
	 */
	public function runMigration2To3Labels(): void
	{
		error_log(print_r(('Action hook activated 2-3 labels'), true)); // phpcs:ignore
	}
}
