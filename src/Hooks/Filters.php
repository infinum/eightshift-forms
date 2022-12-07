<?php

/**
 * The Filters class, used for defining settings and integrations filter variables.
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
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
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaign;
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaignClient;
use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Integrations\Airtable\Airtable;
use EightshiftForms\Integrations\Airtable\AirtableClient;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Integrations\Greenhouse\GreenhouseClient;
use EightshiftForms\Integrations\Hubspot\HubspotClient;
use EightshiftForms\Integrations\Mailchimp\MailchimpClient;
use EightshiftForms\Integrations\Mailerlite\MailerliteClient;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Migration\SettingsMigration;
use EightshiftForms\Settings\Settings\Settings;
use EightshiftForms\Settings\Settings\SettingsDashboard;
use EightshiftForms\Settings\Settings\SettingsDocumentation;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Enrichment\SettingsEnrichment;
use EightshiftForms\Settings\Settings\SettingsLocation;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Validation\SettingsCaptcha;
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
		SettingsDashboard::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsDashboard::FILTER_SETTINGS_GLOBAL_NAME,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7 1H2.5A1.5 1.5 0 0 0 1 2.5V7a1.5 1.5 0 0 0 1.5 1.5H7A1.5 1.5 0 0 0 8.5 7V2.5A1.5 1.5 0 0 0 7 1Zm0 10.5H2.5A1.5 1.5 0 0 0 1 13v4.5A1.5 1.5 0 0 0 2.5 19H7a1.5 1.5 0 0 0 1.5-1.5V13A1.5 1.5 0 0 0 7 11.5ZM17.5 1H13a1.5 1.5 0 0 0-1.5 1.5V7A1.5 1.5 0 0 0 13 8.5h4.5A1.5 1.5 0 0 0 19 7V2.5A1.5 1.5 0 0 0 17.5 1Zm0 10.5H13a1.5 1.5 0 0 0-1.5 1.5v4.5A1.5 1.5 0 0 0 13 19h4.5a1.5 1.5 0 0 0 1.5-1.5V13a1.5 1.5 0 0 0-1.5-1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsGeneral::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGeneral::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGeneral::FILTER_SETTINGS_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.196 1.41c-1.813-.743-3.59-.31-4.25 0 .118 1.619-.581 4.37-4.321 2.428-.661.62-2.012 2.186-2.125 3.5 1.653.761 3.995 2.885.142 5.285.236.976.963 3.042 1.983 3.499 1.417-1 4.264-1.9 4.32 2.5.922.285 3.06.685 4.25 0-.117-1.762.567-4.728 4.25-2.5.567-.476 1.772-1.843 2.055-3.5-1.511-.928-3.627-3.285 0-5.284-.212-.834-.935-2.7-2.125-3.5-3.287 1.943-4.156-.81-4.18-2.428z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><ellipse cx="10.071" cy="9.91" rx="2.975" ry="3" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsValidation::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsValidation::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsValidation::FILTER_SETTINGS_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5.25 9.813 3.818 3.937 8.182-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15.579 2.621A9.21 9.21 0 0 0 10 .75a9.25 9.25 0 1 0 8.758 6.266" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsCaptcha::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsCaptcha::FILTER_SETTINGS_GLOBAL_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.5 13v-1.5m4 1.5v-1.5m-2-8c.828 0 1.25-.422 1.25-1.25C6.75 1.422 6.328 1 5.5 1c-.828 0-1.25.422-1.25 1.25 0 .828.422 1.25 1.25 1.25zm0 0V5M1 7.3a1.8 1.8 0 0 1 1.8-1.8h5.4A1.8 1.8 0 0 1 10 7.3v2.4a1.8 1.8 0 0 1-1.8 1.8H2.8A1.8 1.8 0 0 1 1 9.7V7.3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="3.75" cy="8.25" r=".75" fill="currentColor"/><circle cx="7.25" cy="8.25" r=".75" fill="currentColor"/><path d="M12.264 17.918a4 4 0 0 0 5.654-5.654m-5.654 5.654a4 4 0 1 1 5.654-5.654m-5.654 5.654 5.654-5.654" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'use' => SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY,
		],
		SettingsGeolocation::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGeolocation::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><ellipse cx="10" cy="18.625" rx="2.5" ry=".625" fill="currentColor" fill-opacity=".12"/><path d="m10 18-.53.53a.75.75 0 0 0 1.06 0L10 18zm4.75-10.5c0 2.261-1.26 4.726-2.618 6.7a27.012 27.012 0 0 1-2.442 3.04 14.893 14.893 0 0 1-.208.218l-.01.01-.002.002.53.53.53.53h.001l.001-.002a2.19 2.19 0 0 0 .018-.018l.05-.05.183-.193a28.473 28.473 0 0 0 2.585-3.217c1.393-2.026 2.882-4.811 2.882-7.55h-1.5zM10 18l.53-.53-.002-.002-.01-.01a8.665 8.665 0 0 1-.208-.217 27 27 0 0 1-2.442-3.04C6.511 12.225 5.25 9.76 5.25 7.5h-1.5c0 2.739 1.49 5.524 2.882 7.55a28.494 28.494 0 0 0 2.585 3.217 16.44 16.44 0 0 0 .233.244l.014.013.004.004v.002h.001L10 18zM5.25 7.5A4.75 4.75 0 0 1 10 2.75v-1.5A6.25 6.25 0 0 0 3.75 7.5h1.5zM10 2.75a4.75 4.75 0 0 1 4.75 4.75h1.5A6.25 6.25 0 0 0 10 1.25v1.5z" fill="currentColor"/><circle cx="10" cy="7.5" r="1.5" fill="currentColor" fill-opacity=".3"/></svg>',
			'use' => SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY,
		],
		SettingsEnrichment::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsEnrichment::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M1 10h18M1 10v4.5A1.5 1.5 0 0 0 2.5 16h15a1.5 1.5 0 0 0 1.5-1.5V10M1 10l2.13-5.538a1.5 1.5 0 0 1 1.4-.962h11.362a1.5 1.5 0 0 1 1.434 1.059L19 10" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" fill="none"/><path d="M13 13h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="4.25" cy="13" r="0.75" fill="currentColor" fill-opacity="0.3"/></svg>',
			'use' => SettingsEnrichment::SETTINGS_ENRICHMENT_USE_KEY,
		],
		SettingsMailer::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMailer::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailer::FILTER_SETTINGS_NAME,
			'valid' => SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="4" width="18" height="12" rx="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="m2 5 8 6 8-6M2 15.5l5.5-6m11 6-5.5-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsMailer::SETTINGS_MAILER_USE_KEY,
			'formBlockName' => 'form',
		],
		SettingsMailchimp::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMailchimp::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailchimp::FILTER_SETTINGS_NAME,
			'fields' => Mailchimp::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsMailchimp::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.927 11.43c-.615-.488-1.661-1.85-.923-3.408.923-1.947 3.23-5.354 5.076-6.328 2.662-1.404 2.768-.973 4.152 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M4.772 9.969c0-1.947 1.043-5.94 8.306-9.005 2.307-.973 4.614 1.541 1.845 4.137" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M14.924 5.101c-2.153-.649-6.736-1.752-9.69 2.92m9.69-2.92c.308.65.923 2.19.923 4.138.923.243 2.492 1.022 1.384 2.19.77.487 1.846 2.142 0 4.868-1.846 2.725-5.075 3.082-6.46 2.92-1.23-.162-3.968-1.265-5.075-4.38" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15.616 7.535c-.154-.487-1.061-1.655-2.538-.487-1.697 1.343-3.23-1.46-3.691 1.947 0 .325.185 1.266.923 2.434-.77.974-1.486 2.761-.462 4.38.923 1.461 3.23 2.921 7.383.488" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M17.231 11.43c-.461.486-1.846 1.46-3.691 1.46-1.846 0-2.307.648-2.307.973.153 1.136 1.245 3.115 5.306 0m-5.063-4.031c.175-.2.65-.5 1.149-.1m1.376-1.223.23.73" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><ellipse cx="14.117" cy="11.064" rx=".577" ry=".608" fill="currentColor"/><ellipse cx="14.809" cy="10.821" rx=".577" ry=".608" fill="currentColor"/><path d="M2.927 11.43c.566-1.088 1.385-1.461 1.846-1.461.462 0 1.846.487 1.846 2.92 0 2.613-3.158 1.835-3.62.861-.499-1-.499-1.5-.072-2.32z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M5.695 14.35c-.307-.812-.825-2.49-1.195-2.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY,
			'cache' => [
				MailchimpClient::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME,
				MailchimpClient::CACHE_MAILCHIMP_ITEM_TRANSIENT_NAME,
				MailchimpClient::CACHE_MAILCHIMP_ITEM_TAGS_TRANSIENT_NAME,
			],
		],
		SettingsGreenhouse::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGreenhouse::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGreenhouse::FILTER_SETTINGS_NAME,
			'fields' => Greenhouse::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsGreenhouse::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="15.373" r="3.75" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="10" cy="5.873" r="2.75" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="13" cy="1.373" r="1.25" fill="currentColor"/><path d="M9.25 8.623c.5.5 1.2 1.8 0 3" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M10.912 8.623c-.5.5-1.2 1.8 0 3m1.885-10.5c-.085.453-.513 1.454-1.547 1.844" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M13.371 1.606c-.43.162-1.343.757-1.547 1.843" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsGreenhouse::SETTINGS_GREENHOUSE_USE_KEY,
			'cache' => [
				GreenhouseClient::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME,
				GreenhouseClient::CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME,
			],
		],
		SettingsHubspot::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsHubspot::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsHubspot::FILTER_SETTINGS_NAME,
			'fields' => Hubspot::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsHubspot::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m8.5 17 2.5-2m3.25-11v3.5M3.5 3 11 8.625" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><circle cx="14.25" cy="11.75" r="4.25" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="2.75" cy="2.25" fill="currentColor" r="1.75"/><circle cx="14.25" cy="2.75" fill="currentColor" r="1.75"/><circle cx="7.75" cy="17.75" fill="currentColor" r="1.75"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY,
			'cache' => [
				HubspotClient::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME,
				HubspotClient::CACHE_HUBSPOT_CONTACT_PROPERTIES_TRANSIENT_NAME,
			],
		],
		SettingsMailerlite::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMailerlite::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailerlite::FILTER_SETTINGS_NAME,
			'fields' => Mailerlite::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsMailerlite::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.25 11.25v-5m2.5 5v-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="m11.25 11.2-.304.06a1 1 0 0 1-1.196-.98V6.25l-1 1h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="6.75" cy="6.5" r=".75" fill="currentColor"/><path d="M13 9h3.25v-.725c0-.897-.727-1.625-1.625-1.625v0c-.898 0-1.625.728-1.625 1.625V9zm0 0v.4c0 2 1.5 2.1 3.25 1.668" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M3.676 14.703 1 17.5V4a1.5 1.5 0 0 1 1.5-1.5h15A1.5 1.5 0 0 1 19 4v9.203a1.5 1.5 0 0 1-1.5 1.5H3.676z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsMailerlite::SETTINGS_MAILERLITE_USE_KEY,
			'cache' => [
				MailerliteClient::CACHE_MAILERLITE_ITEMS_TRANSIENT_NAME,
				MailerliteClient::CACHE_MAILERLITE_ITEM_TRANSIENT_NAME,
			],
		],
		SettingsGoodbits::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGoodbits::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGoodbits::FILTER_SETTINGS_NAME,
			'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsGoodbits::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m6.5 13.5 2.358-7.074m.249 7.074 2.358-7.074m.25 7.074 2.358-7.074" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY,
		],
		SettingsClearbit::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsClearbit::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 3a2 2 0 0 1 2-2h7v18H3a2 2 0 0 1-2-2V3z" fill="currentColor"/><path opacity=".7" d="M10 1h7a2 2 0 0 1 2 2v7h-9V1z" fill="currentColor"/><path opacity=".4" d="M10 10h9v7a2 2 0 0 1-2 2h-7v-9z" fill="currentColor"/></svg>',
			'integration' => [
				SettingsHubspot::SETTINGS_TYPE_KEY => [
					'use' => SettingsHubspot::SETTINGS_HUBSPOT_USE_CLEARBIT_KEY,
					'email' => SettingsHubspot::SETTINGS_HUBSPOT_CLEARBIT_EMAIL_FIELD_KEY,
					'map' => SettingsHubspot::SETTINGS_HUBSPOT_CLEARBIT_MAP_KEYS_KEY,
				],
			],
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsClearbit::SETTINGS_CLEARBIT_USE_KEY,
		],
		SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsActiveCampaign::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsActiveCampaign::FILTER_SETTINGS_NAME,
			'fields' => ActiveCampaign::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsActiveCampaign::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m4 1.5 10.272 7.276a1.5 1.5 0 0 1 0 2.448L4 18.5m0-12L9.5 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY,
			'cache' => [
				ActiveCampaignClient::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME,
				ActiveCampaignClient::CACHE_ACTIVE_CAMPAIGN_ITEM_TRANSIENT_NAME,
			],
		],
		SettingsAirtable::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsAirtable::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsAirtable::FILTER_SETTINGS_NAME,
			'fields' => Airtable::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsAirtable::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M1.5 13.595V7.88c0-.18.184-.3.348-.23l6.157 2.639a.25.25 0 0 1 .013.453L1.862 13.82a.25.25 0 0 1-.362-.224ZM9.91 2.783 3.087 5.285a.25.25 0 0 0-.013.464l6.83 2.96a.25.25 0 0 0 .193.002l6.823-2.729a.25.25 0 0 0 0-.464l-6.831-2.732a.25.25 0 0 0-.179-.003Zm8.59 11.546V8.115a.25.25 0 0 0-.34-.233l-7.25 2.806a.25.25 0 0 0-.16.233v6.214a.25.25 0 0 0 .34.233l7.25-2.806a.25.25 0 0 0 .16-.233Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsAirtable::SETTINGS_AIRTABLE_USE_KEY,
			'cache' => [
				AirtableClient::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME,
			],
		],
		SettingsCache::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsCache::FILTER_SETTINGS_GLOBAL_NAME,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M17.5 1.75h-15A1.5 1.5 0 0 0 1 3.25v13.5a1.5 1.5 0 0 0 1.5 1.5h15a1.5 1.5 0 0 0 1.5-1.5V3.25a1.5 1.5 0 0 0-1.5-1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="M1.5 7.25h17m-17 5.5h17" stroke="currentColor" stroke-width="1.5" fill="none"/><rect x="14" y="3.5" width="3" height="2" rx="0.5" fill="currentColor"/><rect x="14" y="9" width="3" height="2" rx="0.5" fill="currentColor"/><rect x="14" y="14.5" width="3" height="2" rx="0.5" fill="currentColor"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
		SettingsLocation::SETTINGS_TYPE_KEY => [
			'settings' => SettingsLocation::FILTER_SETTINGS_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity=".3" d="M7.5 11.75H12m-4.5 3H11m-6.5-6h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle opacity=".3" cx="5" cy="11.75" r="1" fill="currentColor"/><circle opacity=".3" cx="5" cy="14.75" r="1" fill="currentColor"/><path d="M19 14.125c0 2.273-2.5 4.773-2.5 4.773s-2.5-2.5-2.5-4.773a2.5 2.5 0 0 1 5 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="16.5" cy="14.125" r=".682" fill="currentColor"/><path opacity=".2" fill="currentColor" d="M1 1h18v5H1z"/><path d="M19 10V2.5A1.5 1.5 0 0 0 17.5 1h-15A1.5 1.5 0 0 0 1 2.5v15A1.5 1.5 0 0 0 2.5 19H13M4.5 3.75h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
		SettingsFallback::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsFallback::FILTER_SETTINGS_GLOBAL_NAME,
			'valid' => SettingsFallback::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M19 11.5v-6A1.5 1.5 0 0 0 17.5 4h-15A1.5 1.5 0 0 0 1 5.5v9A1.5 1.5 0 0 0 2.5 16h9.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="m2 5 8 6 8-6M2 15.5l5.5-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle opacity="0.12" cx="16.25" cy="15.5" r="3.5" fill="currentColor"/><path d="m14.75 17 3-3m0 3-3-3M13 9.5l1.375 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
			'use' => SettingsFallback::SETTINGS_FALLBACK_USE_KEY,
		],
		SettingsMigration::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMigration::FILTER_SETTINGS_GLOBAL_NAME,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M14 13H2.5A1.5 1.5 0 0 1 1 11.5v-9A1.5 1.5 0 0 1 2.5 1h9A1.5 1.5 0 0 1 13 2.5V4m1 9-2 2m2-2-2-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M6 7h11.5A1.5 1.5 0 0 1 19 8.5v9a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 7 17.5V16M6 7l2-2M6 7l2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
		SettingsTransfer::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsTransfer::FILTER_SETTINGS_GLOBAL_NAME,
			'valid' => SettingsTransfer::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 7H6V4L1 8l5 5v-3h8V7l5 4-5 5v-3h-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
		SettingsDebug::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsDebug::FILTER_SETTINGS_GLOBAL_NAME,
			'valid' => SettingsDebug::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6.152 11.233a3.867 3.867 0 0 1 7.697 0l.203 2.04a4.072 4.072 0 1 1-8.104 0l.204-2.04Z" stroke="currentColor" stroke-width="1.5" fill="none"/><path opacity="0.12" d="M10 9.75v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="M6 12.75H4.454a1.5 1.5 0 0 0-.832.252L2.5 13.75m11.5-1h1.546a1.5 1.5 0 0 1 .832.252l1.122.748M7 8.75H5.121a1.5 1.5 0 0 0-1.06.44l-.561.56m9.5-1h1.879a1.5 1.5 0 0 1 1.06.44l.561.56m-9.5 7H5.121a1.5 1.5 0 0 0-1.06.44l-.561.56m9.5-1h1.879a1.5 1.5 0 0 1 1.06.44l.561.56M12 6.25C12 5.007 11.105 4 10 4S8 5.007 8 6.25m.5-2.5L7 2.25m4.5 1.5 1.5-1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
			'use' => SettingsDebug::SETTINGS_DEBUG_USE_KEY,
		],
		SettingsDocumentation::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsDocumentation::FILTER_SETTINGS_GLOBAL_NAME,
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M3.5 4h2m-2 3h2m-2 3h2m-2 3h2m-2 3h2m0-14v16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15.5 18.5h-11A1.5 1.5 0 0 1 3 17V3a1.5 1.5 0 0 1 1.5-1.5h11A1.5 1.5 0 0 1 17 3v14a1.5 1.5 0 0 1-1.5 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="M8.25 4.5h5m-5 2.5h3m-3 2.5h2" stroke="currentColor" stroke-opacity="0.3" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
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
				'adminFieldsSettings' => 'admin_field_settings_additional_content',
			],
			SettingsGreenhouse::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
				'adminFieldsSettings' => 'admin_field_settings_additional_content',
			],
			SettingsHubspot::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
				'adminFieldsSettings' => 'admin_field_settings_additional_content',
				'filesOptions' => 'files_options',
				'localStorageMap' => 'local_storage_map',
			],
			SettingsMailerlite::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
				'adminFieldsSettings' => 'admin_field_settings_additional_content',
			],
			SettingsGoodbits::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
				'adminFieldsSettings' => 'admin_field_settings_additional_content',
			],
			SettingsClearbit::SETTINGS_TYPE_KEY => [
				'map' => 'map',
			],
			SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
				'adminFieldsSettings' => 'admin_field_settings_additional_content',
			],
			SettingsAirtable::SETTINGS_TYPE_KEY => [
				'fieldsSettings' => 'fields_settings',
				'fieldsSettingsIsEditable' => 'fields_settings_is_editable',
				'data' => 'data',
				'adminFieldsSettings' => 'admin_field_settings_additional_content',
			],
		],
		'geolocation' => [
			'countries' => 'countries_list',
			'disable' => 'disable',
			'dbLocation' => 'db_location',
			'pharLocation' => 'phar_location',
			'cookieName' => 'cookie_name',
			'wpRocketAdvancedCache' => 'wp_rocket_advanced_cache',
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
				'hideLoadingStateTimeout' => 'hide_loading_state_timeout',
				'successRedirectUrl' => 'success_redirect_url',
				'trackingEventName' => 'tracking_event_name',
				'dataTypeSelector' => 'data_type_selector',
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
		],
		'validation' => [
			'failMimetypeValidationWhenFileNotOnFS' => 'force_mimetype_from_fs',
		],
		'general' => [
			'httpRequestTimeout' => 'http_request_timeout',
			'setLocale' => 'set_locale',
		],
		'troubleshooting' => [
			'outputLog' => 'output_log',
		],
	];

	/**
	 * Get the settings labels and details by type and key.
	 * This method is used to provide the ability to translate all strings.
	 *
	 * @param string $type Settings type from the Settings class.
	 * @param string $key Key to output.
	 *
	 * @return string
	 */
	public static function getSettingsLabels(string $type, string $key = 'title'): string
	{
		$data = [
			SettingsDashboard::SETTINGS_TYPE_KEY => [
				'title' => \__('Dashboard', 'eightshift-forms'),
				'desc' => \__('Activate or deactivate all features you want to use in your project.', 'eightshift-forms'),
			],
			SettingsGeneral::SETTINGS_TYPE_KEY => [
				'title' => \__('General', 'eightshift-forms'),
				'desc' => \__('Options that modify the general behavior of the forms (disabling styles and scripts, custom fields, action behavior, etc.).', 'eightshift-forms'),
			],
			SettingsValidation::SETTINGS_TYPE_KEY => [
				'title' => \__('Validation', 'eightshift-forms'),
				'desc' => \__('Form validation settings.', 'eightshift-forms'),
			],
			SettingsCaptcha::SETTINGS_TYPE_KEY => [
				'title' => \__('Captcha', 'eightshift-forms'),
				'desc' => \__('Google reCaptcha specific settings.', 'eightshift-forms'),
				'externalLink' => 'https://www.google.com/recaptcha/about/',
			],
			SettingsGeolocation::SETTINGS_TYPE_KEY => [
				'title' => \__('Geolocation', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding geolocation. Geolocation allows you to render different forms based on the user\'s location conditionally, using an internal geolocation API.', 'eightshift-forms'),
			],
			SettingsEnrichment::SETTINGS_TYPE_KEY => [
				'title' => \__('Enrichment', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding enrichment roles. We use browser storage to cache data from the URL parameters and pass them to external resources. This way, you can follow the users even if they leave your website.', 'eightshift-forms'),
			],
			SettingsMailer::SETTINGS_TYPE_KEY => [
				'title' => \__('Mailer', 'eightshift-forms'),
				'desc' => \__('Regular mail specific settings.', 'eightshift-forms'),
				'detail' => \__('An internal emailing system that is used for sending and creating beautiful emails.', 'eightshift-forms'),
			],
			SettingsMailchimp::SETTINGS_TYPE_KEY => [
				'title' => \__('Mailchimp', 'eightshift-forms'),
				'desc' => \__('Mailchimp integration settings (API key, etc.).', 'eightshift-forms'),
				'detail' => \__('Mailchimp is a comprehensive email marketing platform that fulfills all your requirements of email correspondence with customers, affiliates, and more.', 'eightshift-forms'),
				'externalLink' => 'https://mailchimp.com/',
			],
			SettingsGreenhouse::SETTINGS_TYPE_KEY => [
				'title' => \__('Greenhouse', 'eightshift-forms'),
				'desc' => \__('Greenhouse integration settings (API key, file size limit, etc.).', 'eightshift-forms'),
				'detail' => \__('Greenhouse is a sourcing automation tool to help hiring teams find, reach and engage top talent quickly and effectively.', 'eightshift-forms'),
				'externalLink' => 'https://www.greenhouse.io/',
			],
			SettingsHubspot::SETTINGS_TYPE_KEY => [
				'title' => \__('HubSpot', 'eightshift-forms'),
				'desc' => \__('HubSpot integration settings (API key, allowed file types for upload, etc.).', 'eightshift-forms'),
				'detail' => \__('HubSpot is a CRM platform that connects everything scaling companies need to deliver a best-in-class customer experience into one place.', 'eightshift-forms'),
				'externalLink' => 'https://www.hubspot.com/',
			],
			SettingsMailerlite::SETTINGS_TYPE_KEY => [
				'title' => \__('MailerLite', 'eightshift-forms'),
				'desc' => \__('MailerLite integration settings.', 'eightshift-forms'),
				'detail' => \__('MailerLite is an email service provider that makes it easier to plan email marketing campaigns for any growing business.', 'eightshift-forms'),
				'externalLink' => 'https://www.mailerlite.com/',
			],
			SettingsGoodbits::SETTINGS_TYPE_KEY => [
				'title' => \__('Goodbits', 'eightshift-forms'),
				'desc' => \__('Goodbits integration settings.', 'eightshift-forms'),
				'detail' => \__('Goodbits helps you and your business create stellar newsletters from the best links your team and customers are reading.', 'eightshift-forms'),
				'externalLink' => 'https://goodbits.io/',
			],
			SettingsClearbit::SETTINGS_TYPE_KEY => [
				'title' => \__('Clearbit', 'eightshift-forms'),
				'desc' => \__('Clearbit integration settings.', 'eightshift-forms'),
				'detail' => \__('Clearbit is a marketing intelligence tool that you can use to effectively get quality B2B data for understanding customers, identifying prospects, and creating personalised marketing and sales exchanges.', 'eightshift-forms'),
				'externalLink' => 'https://clearbit.com/',
			],
			SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
				'title' => \__('Active Campaign', 'eightshift-forms'),
				'desc' => \__('Active Campaign integration settings.', 'eightshift-forms'),
				'detail' => \__('ActiveCampaign is an integrated email marketing, automation, sales software, and CRM platform. It lets users perform powerful automation, email marketing, and customer relationship management.', 'eightshift-forms'),
				'externalLink' => 'https://www.activecampaign.com/',
			],
			SettingsAirtable::SETTINGS_TYPE_KEY => [
				'title' => \__('Airtable', 'eightshift-forms'),
				'desc' => \__('Airtable integration settings.', 'eightshift-forms'),
				'detail' => \__('Airtable is a platform that makes it easy to build powerful, custom applications.', 'eightshift-forms'),
				'externalLink' => 'https://airtable.com/',
			],
			SettingsCache::SETTINGS_TYPE_KEY => [
				'title' => \__('Cache', 'eightshift-forms'),
				'desc' => \__('Clear cache for specific integrations.', 'eightshift-forms'),
			],
			SettingsLocation::SETTINGS_TYPE_KEY => [
				'title' => \__('Locations', 'eightshift-forms'),
				'desc' => \__('Change the options regarding forms location usage.', 'eightshift-forms'),
			],
			SettingsFallback::SETTINGS_TYPE_KEY => [
				'title' => \__('Fallback', 'eightshift-forms'),
				'desc' => \__('Options for handling form errors by sending a plain email', 'eightshift-forms'),
			],
			SettingsDebug::SETTINGS_TYPE_KEY => [
				'title' => \__('Debug', 'eightshift-forms'),
				'desc' => \__('Form debugging settings.', 'eightshift-forms'),
			],
			SettingsTransfer::SETTINGS_TYPE_KEY => [
				'title' => \__('Import/Export', 'eightshift-forms'),
				'desc' => \__('Transfer your forms and settings from one enviroment to another.', 'eightshift-forms'),
			],
			SettingsDocumentation::SETTINGS_TYPE_KEY => [
				'title' => \__('Documentation', 'eightshift-forms'),
				'desc' => \__('Need help? Interested in learning more? Find resources here.', 'eightshift-forms'),
			],
			SettingsMigration::SETTINGS_TYPE_KEY => [
				'title' => \__('Migration', 'eightshift-forms'),
				'desc' => \__('Migrate your form from older version to the latest one with one easy click.', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_GENERAL => [
				'title' => \__('General', 'eightshift-forms'),
				'desc' => \__('General plugin configuration.', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION => [
				'title' => \__('Form Type', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding all forms types.', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING => [
				'title' => \__('Troubleshooting', 'eightshift-forms'),
				'desc' => \__('Settings for various troubleshooting and debugging options.', 'eightshift-forms'),
			],
		];

		return isset($data[$type][$key]) ? $data[$type][$key] : '';
	}

	/**
	 * Get Integration filter by name and type.
	 *
	 * @param string $type Integration type.
	 * @param string $name Filter name.
	 *
	 * @throws MissingFilterInfoException Throws error if filter name is missing or wrong.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_integration_mailchimp_fields_settings
	 */
	public static function getIntegrationFilterName(string $type, string $name): string
	{
		$internalType = Helper::camelToSnakeCase($type);

		$filter = self::ALL_PUBLIC['integrations'][$internalType][$name] ?? '';

		if (!$filter) {
			throw MissingFilterInfoException::viewException('integrations', $type, $name);
		}

		return self::FILTER_PREFIX . "_integration_{$internalType}_{$filter}";
	}

	/**
	 * Get Blocks filter by name.
	 *
	 * @param string $name Filter name.
	 *
	 * @throws MissingFilterInfoException Throws error if filter name is missing or wrong.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_blocks_additional_blocks
	 */
	public static function getBlocksFilterName(string $name): string
	{
		$filter = self::ALL_PUBLIC['blocks'][$name] ?? '';

		if (!$filter) {
			throw MissingFilterInfoException::viewException('blocks', '', $name);
		}

		return self::FILTER_PREFIX . "_blocks_{$filter}";
	}

	/**
	 * Get Blocks filter by name.
	 *
	 * @param string $type Block type.
	 * @param string $name Filter name.
	 *
	 * @throws MissingFilterInfoException Throws error if filter name is missing or wrong.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_block_input_additional_content
	 */
	public static function getBlockFilterName(string $type, string $name): string
	{
		$internalType = Helper::camelToSnakeCase($type);

		$filter = self::ALL_PUBLIC['block'][$type][$name] ?? '';

		if (!$filter) {
			throw MissingFilterInfoException::viewException('block', $type, $name);
		}

		return self::FILTER_PREFIX . "_block_{$internalType}_{$filter}";
	}

	/**
	 * Get Geolocation filter by name.
	 *
	 * @param string $name Filter name.
	 *
	 * @throws MissingFilterInfoException Throws error if filter name is missing or wrong.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_geolocation_disable
	 */
	public static function getGeolocationFilterName(string $name): string
	{
		$filter = self::ALL_PUBLIC['geolocation'][$name] ?? '';

		if (!$filter) {
			throw MissingFilterInfoException::viewException('geolocation', '', $name);
		}

		return self::FILTER_PREFIX . "_geolocation_{$filter}";
	}

	/**
	 * Get Validation filter by name.
	 *
	 * @param string $name Filter name.
	 *
	 * @throws MissingFilterInfoException Throws error if filter name is missing or wrong.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_validation_force_mimetype_from_fs
	 */
	public static function getValidationFilterName(string $name): string
	{
		$filter = self::ALL_PUBLIC['validation'][$name] ?? '';
		if (!$filter) {
			throw MissingFilterInfoException::viewException('validation', '', $name);
		}

		return self::FILTER_PREFIX . "_validation_{$filter}";
	}

	/**
	 * Get General filter by name.
	 *
	 * @param string $name Filter name.
	 *
	 * @throws MissingFilterInfoException Throws error if filter name is missing or wrong.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_general_http_request_timeout
	 */
	public static function getGeneralFilterName(string $name): string
	{
		$filter = self::ALL_PUBLIC['general'][$name] ?? '';
		if (!$filter) {
			throw MissingFilterInfoException::viewException('general', '', $name);
		}

		return self::FILTER_PREFIX . "_general_{$filter}";
	}

	/**
	 * Get Troubleshooting filter by name.
	 *
	 * @param string $name Filter name.
	 *
	 * @throws MissingFilterInfoException Throws error if filter name is missing or wrong.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_troubleshooting_output_log
	 */
	public static function getTroubleshootingFilterName(string $name): string
	{
		$filter = self::ALL_PUBLIC['troubleshooting'][$name] ?? '';
		if (!$filter) {
			throw MissingFilterInfoException::viewException('troubleshooting', '', $name);
		}

		return self::FILTER_PREFIX . "_troubleshooting_{$filter}";
	}
}
