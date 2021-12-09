<?php

/**
 * The Filters class, used for defining settings and integrations filter variables.
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Helpers\Helper;
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
	 * Prefix added to all filters.
	 *
	 * @var string
	 */
	public const FILTER_PREFIX = 'es_forms';

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
	 * All public filters.
	 */
	public const ALL_PUBLIC = [
		'integrations' => [
			SettingsMailchimp::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
			],
			SettingsGreenhouse::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
			],
			SettingsHubspot::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
			],
			SettingsMailerlite::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
			],
			SettingsGoodbits::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
			],
		],
		'blocks' => [
			'additionalBlocks' => 'additional_blocks',
			'breakpoints' => 'media_breakpoints',
		],
		'block' => [
			'forms' => [
				'styleOptions' => 'style_options',
			],
			'form' => [
				'redirectionTimeout' => 'redirection_timeout',
				'hideGlobalMsgTimeout' => 'hide_global_message_timeout',
			],
			'formSelector' => [
				'additionalContent' => 'additional_content',
			],
			'field' => [
				'styleOptions' => 'style_options',
				'additionalContent' => 'additional_content',
			],
			'input' => [
				'additionalContent' => 'additional_content',
			],
			'textarea' => [
				'additionalContent' => 'additional_content',
			],
			'select' => [
				'additionalContent' => 'additional_content',
			],
			'file' => [
				'additionalContent' => 'additional_content',
				'previewRemoveLabel' => 'preview_remove_label',
			],
			'checkboxes' => [
				'additionalContent' => 'additional_content',
			],
			'radios' => [
				'additionalContent' => 'additional_content',
			],
			'customData' => [
				'options' => 'options',
				'data' => 'options_data',
			],
			'submit' => [
				'component' => 'component',
				'additionalContent' => 'additional_content',
			],
		]
	];

	/**
	 * Get Integration filter by name and type.
	 *
	 * @param string $type Integration type.
	 * @param string $name Filter name.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_integration_mailchimp_fields_settings
	 */
	public static function getIntegrationFilterName(string $type, string $name): string
	{
		$internalType = Helper::camelToSnakeCase($type);

		return self::FILTER_PREFIX . "_integration_{$internalType}_" . self::ALL_PUBLIC['integrations'][$type][$name];
	}

	/**
	 * Get Blocks filter by name.
	 *
	 * @param string $name Filter name.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_blocks_additional_blocks
	 */
	public static function getBlocksFilterName(string $name): string
	{
		return self::FILTER_PREFIX . '_blocks_' . self::ALL_PUBLIC['blocks'][$name];
	}

	/**
	 * Get Blocks filter by name.
	 *
	 * @param string $type Block type.
	 * @param string $name Filter name.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_block_input_additional_content
	 */
	public static function getBlockFilterName(string $type, string $name): string
	{
		$internalType = Helper::camelToSnakeCase($type);

		return self::FILTER_PREFIX . "_block_{$internalType}_" . self::ALL_PUBLIC['block'][$type][$name]; // @phpstan-ignore-line
	}
}
