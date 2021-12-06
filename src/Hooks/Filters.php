<?php

/**
 * The Filters class, used for defining settings and integrations filter variables.
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Integrations\Goodbits\Goodbits;
use EightshiftForms\Integrations\Greenhouse\Greenhouse;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\Hubspot;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\Mailchimp\Mailchimp;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Integrations\Mailerlite\Mailerlite;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\Settings\SettingsTest;
use EightshiftForms\Validation\SettingsValidation;

/**
 * The Filters class, used for defining settings and integrations filter variables.
 */
class Filters
{
	/**
	 * All settings, panels and integrations.
	 * Order of items here will determin the order in the browser sidebar for settings.
	 */
	public const ALL = [
		SettingsGeneral::SETTINGS_TYPE_KEY => [
			'global' => SettingsGeneral::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGeneral::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsGeneral::FILTER_SETTINGS_SIDEBAR_NAME,
		],
		SettingsValidation::SETTINGS_TYPE_KEY => [
			'global' => SettingsValidation::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsValidation::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsValidation::FILTER_SETTINGS_SIDEBAR_NAME,
		],
		SettingsCache::SETTINGS_TYPE_KEY => [
			'global' => SettingsCache::FILTER_SETTINGS_GLOBAL_NAME,
			'settingsSidebar' => SettingsCache::FILTER_SETTINGS_SIDEBAR_NAME,
		],
		SettingsMailer::SETTINGS_TYPE_KEY => [
			'settings' => SettingsMailer::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsMailer::FILTER_SETTINGS_SIDEBAR_NAME,
		],
		SettingsMailchimp::SETTINGS_TYPE_KEY => [
			'global' => SettingsMailchimp::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailchimp::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsMailchimp::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Mailchimp::FILTER_FORM_FIELDS_NAME,
		],
		SettingsGreenhouse::SETTINGS_TYPE_KEY => [
			'global' => SettingsGreenhouse::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGreenhouse::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsGreenhouse::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Greenhouse::FILTER_FORM_FIELDS_NAME,
		],
		SettingsHubspot::SETTINGS_TYPE_KEY => [
			'global' => SettingsHubspot::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsHubspot::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsHubspot::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Hubspot::FILTER_FORM_FIELDS_NAME,
		],
		SettingsMailerlite::SETTINGS_TYPE_KEY => [
			'global' => SettingsMailerlite::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailerlite::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsMailerlite::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Mailerlite::FILTER_FORM_FIELDS_NAME,
		],
		SettingsGoodbits::SETTINGS_TYPE_KEY => [
			'global' => SettingsGoodbits::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGoodbits::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsGoodbits::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
		],
		SettingsTest::SETTINGS_TYPE_KEY => [
			'global' => SettingsTest::FILTER_SETTINGS_GLOBAL_NAME,
			'settingsSidebar' => SettingsTest::FILTER_SETTINGS_SIDEBAR_NAME,
		],
	];

	/**
	 * Filter form js redirection timeout key.
	 */
	public const FILTER_FORM_JS_REDIRECTION_TIMEOUT_NAME = 'es_forms_form_js_redirection_timeout';

	/**
	 * Filter form js hide global message timeout key.
	 */
	public const FILTER_FORM_JS_HIDE_GLOBAL_MESSAGE_TIMEOUT_NAME = 'es_forms_form_js_hide_global_message_timeout';

	/**
	 * Filter form js file preview remove key.
	 */
	public const FILTER_FORM_JS_FILE_PREVIEW_REMOVE_LABEL_NAME = 'es_forms_file_js_preview_remove_label';

	/**
	 * Filter additional blocks key.
	 */
	public const FILTER_ADDITIONAL_BLOCKS_NAME = 'es_forms_additional_blocks';

	/**
	 * Filter media breakpoints key.
	 */
	public const FILTER_MEDIA_BREAKPOINTS_NAME = 'es_forms_media_breakpoints';

	/**
	 * Filter block forms style options key.
	 */
	public const FILTER_BLOCK_FORMS_STYLE_OPTIONS_NAME = 'es_forms_block_forms_style_options';

	/**
	 * Filter block field style options key.
	 */
	public const FILTER_BLOCK_FIELD_STYLE_OPTIONS_NAME = 'es_forms_block_field_style_options';

	/**
	 * Filter block custom data options key.
	 */
	public const FILTER_BLOCK_CUSTOM_DATA_OPTIONS_NAME = 'es_forms_block_custom_data_options';

	/**
	 * Filter block custom data options data key.
	 */
	public const FILTER_BLOCK_CUSTOM_DATA_OPTIONS_DATA_NAME = 'es_forms_block_custom_data_options_data';

	/**
	 * Filter block submit key.
	 */
	public const FILTER_BLOCK_SUBMIT_NAME = 'es_forms_block_submit';

	/**
	 * Filter additional content key.
	 */
	public const FILTER_BLOCK_FORM_SELECTOR_ADDITIONAL_CONTENT_NAME = 'es_forms_block_form_selector_additional_content';
	public const FILTER_BLOCK_INPUT_ADDITIONAL_CONTENT_NAME = 'es_forms_block_input_additional_content';
	public const FILTER_BLOCK_TEXTAREA_ADDITIONAL_CONTENT_NAME = 'es_forms_block_textarea_additional_content';
	public const FILTER_BLOCK_SELECT_ADDITIONAL_CONTENT_NAME = 'es_forms_block_select_additional_content';
	public const FILTER_BLOCK_FILE_ADDITIONAL_CONTENT_NAME = 'es_forms_block_file_additional_content';
	public const FILTER_BLOCK_CHECKBOXES_ADDITIONAL_CONTENT_NAME = 'es_forms_block_checkboxes_additional_content';
	public const FILTER_BLOCK_RADIOS_ADDITIONAL_CONTENT_NAME = 'es_forms_block_radios_additional_content';
	public const FILTER_BLOCK_SUBMIT_ADDITIONAL_CONTENT_NAME = 'es_forms_block_submit_additional_content';

	/**
	 * Filter integration form data key
	 */
	public const FILTER_INTEGRATION_GOODBITS_FORM_DATA_NAME = 'es_forms_integration_goodbits_form_data';
	public const FILTER_INTEGRATION_GREENHOUSE_FORM_DATA_NAME = 'es_forms_integration_greenhouse_form_data';
	public const FILTER_INTEGRATION_HUBSPOT_FORM_DATA_NAME = 'es_forms_integration_hubspot_form_data';
	public const FILTER_INTEGRATION_MAILCHIMP_FORM_DATA_NAME = 'es_forms_integration_mailchimp_form_data';
	public const FILTER_INTEGRATION_MAILERLITE_FORM_DATA_NAME = 'es_forms_integration_mailerlite_form_data';
}
