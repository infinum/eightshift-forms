<?php

/**
 * The Filters class for all public filters/actions.
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Migration\SettingsMigration;
use EightshiftForms\Enrichment\SettingsEnrichment;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Integrations\Workable\SettingsWorkable;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\Integrations\Pipedrive\SettingsPipedrive;
use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftForms\Misc\SettingsWpml;
use EightshiftForms\Security\SettingsSecurity;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;

/**
 * The Filters class for all public filters/actions.
 */
final class Filters
{
	/**
	 * Get public filter/actions global variable data.
	 *
	 * @return array<mixed>
	 */
	public static function getHooksData(): array
	{
		return [
			UtilsConfig::PUBLIC_FILTERS_NAME => self::getPublicFilters(),
			UtilsConfig::PUBLIC_ACTIONS_NAME => self::getPublicActions(),
			UtilsConfig::PUBLIC_NONE_TRANSLATABLE_NAMES_NAME => self::getSettingsNoneTranslatableNames(),
		];
	}

	/**
	 * Get list of all public filters.
	 *
	 * @return array<mixed>
	 */
	private static function getPublicFilters(): array
	{
		return [
			'block' => [
				'forms' => [
					'styleOptions',
				],
				'form' => [
					'redirectionTimeout',
					'hideGlobalMsgTimeout',
					'successRedirectUrl',
					'successRedirectVariation',
					'successRedirectVariationOptions',
					'trackingEventName',
					'trackingAdditionalData',
					'dataTypeSelector',
					'phoneSync',
					'globalMsgHeadings',
					'additionalContent',
					'preResponseAddonData',
					'preResponseSuccessRedirectData',
					'additionalHiddenFields',
				],
				'formSelector' => [
					'formTemplates',
					'additionalContent',
				],
				'field' => [
					'styleOptions',
					'styleClasses',
					'additionalContent',
				],
				'input' => [
					'additionalContent',
				],
				'textarea' => [
					'additionalContent',
				],
				'select' => [
					'additionalContent',
				],
				'file' => [
					'additionalContent',
					'previewRemoveLabel',
				],
				'checkboxes' => [
					'additionalContent',
				],
				'radios' => [
					'additionalContent',
				],
				'phone' => [
					'additionalContent',
				],
				'country' => [
					'additionalContent',
					'alternativeDataSet',
				],
				'date' => [
					'additionalContent',
				],
				'submit' => [
					'component',
					'additionalContent',
				],
				'step' => [
					'componentPrev',
					'componentNext',
				],
				'rating' => [
					'additionalContent',
				],
			],
			'blocks' => [
				'allowedBlocks',
				'additionalBlocks',
				'mediaBreakpoints',
			],
			'general' => [
				'httpRequestTimeout',
				'locale',
			],
			'scripts' => [
				'dependency' => [
					'admin',
					'theme',
					'blocksEditor',
					'blocksFrontend',
				],
				'routes' => [
					'public',
					'private',
				],
			],
			'geolocation' => [
				'countriesList',
				'dbLocation',
				'pharLocation',
			],
			'integrations' => [
				SettingsMailer::SETTINGS_TYPE_KEY => [
					'prePostParams',
				],
				SettingsMailchimp::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
				],
				SettingsGreenhouse::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
				],
				SettingsHubspot::SETTINGS_TYPE_KEY => [
					'filesOptions',
					'data',
					'order',
					'prePostId',
					'prePostParams',
				],
				SettingsMailerlite::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
				],
				SettingsGoodbits::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
				],
				SettingsClearbit::SETTINGS_TYPE_KEY => [
					'map',
				],
				SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
				],
				SettingsAirtable::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
				],
				SettingsMoments::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
					'prePostEventParams',
					'prePostEventParamsAfter',
				],
				SettingsWorkable::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
				],
				SettingsJira::SETTINGS_TYPE_KEY => [
					'prePostParams',
				],
				SettingsPipedrive::SETTINGS_TYPE_KEY => [
					'prePostParams',
				],
			],
			'entries' => [
				'prePostParams',
			],
			'enrichment' => [
				'manualMap',
			],
			'validation' => [
				'forceMimetypeFromFs',
			],
			'admin' => [
				'settings' => [
					'data',
				],
			],
		];
	}

	/**
	 * Get list of all public actions.
	 *
	 * @return array<mixed>
	 */
	private static function getPublicActions(): array
	{
		return [
			'migration' => [
				'twoToThreeGeneral',
				'twoToThreeForms',
				'twoToThreeLocale',
			],
			'entries' => [
				'saveEntry',
			],
		];
	}

	/**
	 * Get the settings names that are not translatable, like API keys, secrets, etc.
	 *
	 * @return array<int, string>
	 */
	private static function getSettingsNoneTranslatableNames(): array
	{
		return [
			SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY,
			SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY,
			SettingsCaptcha::SETTINGS_CAPTCHA_SECRET_KEY,
			SettingsCaptcha::SETTINGS_CAPTCHA_PROJECT_ID_KEY,
			SettingsCaptcha::SETTINGS_CAPTCHA_API_KEY,

			SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY,

			SettingsEnrichment::SETTINGS_ENRICHMENT_USE_KEY,

			SettingsSecurity::SETTINGS_SECURITY_USE_KEY,

			SettingsMailer::SETTINGS_MAILER_USE_KEY,

			SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY,
			SettingsMailchimp::SETTINGS_MAILCHIMP_API_KEY_KEY,

			SettingsGreenhouse::SETTINGS_GREENHOUSE_USE_KEY,
			SettingsGreenhouse::SETTINGS_GREENHOUSE_API_KEY_KEY,
			SettingsGreenhouse::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY,

			SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY,
			SettingsHubspot::SETTINGS_HUBSPOT_API_KEY_KEY,

			SettingsMailerlite::SETTINGS_MAILERLITE_USE_KEY,
			SettingsMailerlite::SETTINGS_MAILERLITE_API_KEY_KEY,

			SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY,
			SettingsGoodbits::SETTINGS_GOODBITS_API_KEY_KEY,

			SettingsClearbit::SETTINGS_CLEARBIT_USE_KEY,
			SettingsClearbit::SETTINGS_CLEARBIT_API_KEY_KEY,

			SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY,
			SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY,
			SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY,

			SettingsAirtable::SETTINGS_AIRTABLE_USE_KEY,
			SettingsAirtable::SETTINGS_AIRTABLE_API_KEY_KEY,

			SettingsMoments::SETTINGS_MOMENTS_USE_KEY,
			SettingsMoments::SETTINGS_MOMENTS_API_URL_KEY,
			SettingsMoments::SETTINGS_MOMENTS_API_KEY_KEY,

			SettingsWorkable::SETTINGS_WORKABLE_USE_KEY,
			SettingsWorkable::SETTINGS_WORKABLE_API_KEY_KEY,
			SettingsWorkable::SETTINGS_WORKABLE_SUBDOMAIN_KEY,

			SettingsJira::SETTINGS_JIRA_USE_KEY,
			SettingsJira::SETTINGS_JIRA_API_KEY_KEY,
			SettingsJira::SETTINGS_JIRA_API_BOARD_KEY,
			SettingsJira::SETTINGS_JIRA_API_USER_KEY,
			SettingsJira::SETTINGS_JIRA_SELF_HOSTED_KEY,

			SettingsPipedrive::SETTINGS_PIPEDRIVE_USE_KEY,
			SettingsPipedrive::SETTINGS_PIPEDRIVE_API_KEY_KEY,

			SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY,

			SettingsWpml::SETTINGS_WPML_USE_KEY,

			SettingsFallback::SETTINGS_FALLBACK_USE_KEY,

			SettingsMigration::SETTINGS_MIGRATION_USE_KEY,

			SettingsTransfer::SETTINGS_TRANSFER_USE_KEY,

			SettingsDebug::SETTINGS_DEBUG_USE_KEY,
			SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY,

			SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY,
		];
	}
}
