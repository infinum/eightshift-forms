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
use EightshiftForms\Integrations\Talentlyft\SettingsTalentlyft;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Integrations\Corvus\SettingsCorvus;
use EightshiftForms\Integrations\Nationbuilder\OauthNationbuilder;
use EightshiftForms\Integrations\Nationbuilder\SettingsNationbuilder;
use EightshiftForms\Integrations\Paycek\SettingsPaycek;
use EightshiftForms\Integrations\Pipedrive\SettingsPipedrive;
use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftForms\Misc\SettingsCloudFront;
use EightshiftForms\Misc\SettingsRocketCache;
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
					'useCustomResultOutputFeature',
				],
				'form' => [
					'redirectionTimeout',
					'hideGlobalMsgTimeout',
					'successRedirectUrl',
					'variation',
					'trackingEventName',
					'trackingAdditionalData',
					'dataTypeSelector',
					'globalMsgHeadings',
					'additionalContent',
					'additionalHiddenFields',
					'customClassSelector',
					'componentShowForm',
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
					'infoAdditionalContent',
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
					'modifyDataSet',
					'alternativeDataSet',
					'customOrder',
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
					'starIcon',
				],
				'dynamic' => [
					'additionalContent',
					'dataOutput',
				],
				'loader' => [
					'additionalContent',
				],
			],
			'blocks' => [
				'allowedBlocks',
				'additionalBlocks',
				'mediaBreakpoints',
				'tailwindSelectors',
			],
			'general' => [
				'httpRequestTimeout',
				'locale',
			],
			'scripts' => [
				'dependency' => [
					'admin',
					'captcha',
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
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
					'bodyTemplate',
				],
				SettingsMailchimp::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsGreenhouse::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsHubspot::SETTINGS_TYPE_KEY => [
					'filesOptions',
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsMailerlite::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsGoodbits::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsClearbit::SETTINGS_TYPE_KEY => [
					'map',
				],
				SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsAirtable::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsMoments::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
					'prePostEventParams',
					'prePostEventParamsAfter',
				],
				SettingsWorkable::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsTalentlyft::SETTINGS_TYPE_KEY => [
					'data',
					'order',
					'prePostId',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsJira::SETTINGS_TYPE_KEY => [
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsCorvus::SETTINGS_TYPE_KEY => [
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsPaycek::SETTINGS_TYPE_KEY => [
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsPipedrive::SETTINGS_TYPE_KEY => [
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsCalculator::SETTINGS_TYPE_KEY => [
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
				],
				SettingsNationbuilder::SETTINGS_TYPE_KEY => [
					'overridePostRequest',
					'prePostParams',
					'beforeSuccessResponse',
					'afterCustomResultOutputProcess',
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
			'encryption' => [
				'secretKey',
				'secretIvKey',
			],
			'admin' => [
				'topBarMenu' => [
					'items',
				],
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
				'clearbit',
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

			SettingsTalentlyft::SETTINGS_TALENTLYFT_USE_KEY,
			SettingsTalentlyft::SETTINGS_TALENTLYFT_API_KEY_KEY,

			SettingsJira::SETTINGS_JIRA_USE_KEY,
			SettingsJira::SETTINGS_JIRA_API_KEY_KEY,
			SettingsJira::SETTINGS_JIRA_API_BOARD_KEY,
			SettingsJira::SETTINGS_JIRA_API_USER_KEY,
			SettingsJira::SETTINGS_JIRA_SELF_HOSTED_KEY,

			SettingsCorvus::SETTINGS_CORVUS_USE_KEY,
			SettingsCorvus::SETTINGS_CORVUS_API_KEY_KEY,
			SettingsCorvus::SETTINGS_CORVUS_STORE_IDS_KEY,

			SettingsPaycek::SETTINGS_PAYCEK_USE_KEY,
			SettingsPaycek::SETTINGS_PAYCEK_API_KEY_KEY,

			SettingsPipedrive::SETTINGS_PIPEDRIVE_USE_KEY,
			SettingsPipedrive::SETTINGS_PIPEDRIVE_API_KEY_KEY,

			SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_ID,
			SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_SECRET,
			SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_SLUG,
			OauthNationbuilder::OAUTH_NATIONBUILDER_ACCESS_TOKEN_KEY,
			OauthNationbuilder::OAUTH_NATIONBUILDER_REFRESH_TOKEN_KEY,

			SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY,
			SettingsCloudFront::SETTINGS_CLOUDFRONT_USE_KEY,

			SettingsWpml::SETTINGS_WPML_USE_KEY,

			SettingsRocketCache::SETTINGS_ROCKET_CACHE_USE_KEY,

			SettingsFallback::SETTINGS_FALLBACK_USE_KEY,

			SettingsMigration::SETTINGS_MIGRATION_USE_KEY,

			SettingsTransfer::SETTINGS_TRANSFER_USE_KEY,

			SettingsDebug::SETTINGS_DEBUG_USE_KEY,
			SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY,

			SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY,

			SettingsGeneral::INCREMENT_META_KEY,
		];
	}
}
