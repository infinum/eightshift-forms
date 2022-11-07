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
use EightshiftForms\Integrations\Greenhouse\GreenhouseClient;
use EightshiftForms\Integrations\Hubspot\HubspotClient;
use EightshiftForms\Integrations\Mailchimp\MailchimpClient;
use EightshiftForms\Integrations\Mailerlite\MailerliteClient;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Settings\Settings\Settings;
use EightshiftForms\Settings\Settings\SettingsDashboard;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\Settings\SettingsLocation;
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
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.882 18.5h1.236a1.5 1.5 0 0 0 1.5-1.5v-6.558c0-.576.347-1.086.774-1.472C10.375 8.082 11 6.598 11 5.348c0-1.676-1.122-3.114-2.724-3.737a.237.237 0 0 0-.32.228V5.5a.25.25 0 0 1-.25.25H5.294a.25.25 0 0 1-.25-.25V1.839a.237.237 0 0 0-.32-.228C3.122 2.234 2 3.672 2 5.348c0 1.25.625 2.734 1.608 3.622.427.386.774.896.774 1.472V17a1.5 1.5 0 0 0 1.5 1.5z" stroke="#29A3A3" strokeWidth="1.5" strokeLinejoin="round" fill="none"/><path d="M13 13v3a2.5 2.5 0 0 0 2.5 2.5v0A2.5 2.5 0 0 0 18 16v-3m-5 0v-2.5h1M13 13h5m0 0v-2.5h-1m-3 0v-5l-.5-2 .5-2h3l.5 2-.5 2v5m-3 0h3" stroke="#29A3A3" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsGeneral::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGeneral::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGeneral::FILTER_SETTINGS_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.196 1.41c-1.813-.743-3.59-.31-4.25 0 .118 1.619-.581 4.37-4.321 2.428-.661.62-2.012 2.186-2.125 3.5 1.653.761 3.995 2.885.142 5.285.236.976.963 3.042 1.983 3.499 1.417-1 4.264-1.9 4.32 2.5.922.285 3.06.685 4.25 0-.117-1.762.567-4.728 4.25-2.5.567-.476 1.772-1.843 2.055-3.5-1.511-.928-3.627-3.285 0-5.284-.212-.834-.935-2.7-2.125-3.5-3.287 1.943-4.156-.81-4.18-2.428z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><ellipse cx="10.071" cy="9.91" rx="2.975" ry="3" stroke="#29A3A3" stroke-width="1.5" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsValidation::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsValidation::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsValidation::FILTER_SETTINGS_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5.25 9.813 3.818 3.937 8.182-9" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15.579 2.621A9.21 9.21 0 0 0 10 .75a9.25 9.25 0 1 0 8.758 6.266" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsCaptcha::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsCaptcha::FILTER_SETTINGS_GLOBAL_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.5 13v-1.5m4 1.5v-1.5m-2-8c.828 0 1.25-.422 1.25-1.25C6.75 1.422 6.328 1 5.5 1c-.828 0-1.25.422-1.25 1.25 0 .828.422 1.25 1.25 1.25zm0 0V5M1 7.3a1.8 1.8 0 0 1 1.8-1.8h5.4A1.8 1.8 0 0 1 10 7.3v2.4a1.8 1.8 0 0 1-1.8 1.8H2.8A1.8 1.8 0 0 1 1 9.7V7.3z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="3.75" cy="8.25" r=".75" fill="#29A3A3"/><circle cx="7.25" cy="8.25" r=".75" fill="#29A3A3"/><path d="M12.264 17.918a4 4 0 0 0 5.654-5.654m-5.654 5.654a4 4 0 1 1 5.654-5.654m-5.654 5.654 5.654-5.654" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'use' => SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY,
		],
		SettingsGeolocation::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGeolocation::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><ellipse cx="10" cy="18.625" rx="2.5" ry=".625" fill="#29A3A3" fill-opacity=".12"/><path d="m10 18-.53.53a.75.75 0 0 0 1.06 0L10 18zm4.75-10.5c0 2.261-1.26 4.726-2.618 6.7a27.012 27.012 0 0 1-2.442 3.04 14.893 14.893 0 0 1-.208.218l-.01.01-.002.002.53.53.53.53h.001l.001-.002a2.19 2.19 0 0 0 .018-.018l.05-.05.183-.193a28.473 28.473 0 0 0 2.585-3.217c1.393-2.026 2.882-4.811 2.882-7.55h-1.5zM10 18l.53-.53-.002-.002-.01-.01a8.665 8.665 0 0 1-.208-.217 27 27 0 0 1-2.442-3.04C6.511 12.225 5.25 9.76 5.25 7.5h-1.5c0 2.739 1.49 5.524 2.882 7.55a28.494 28.494 0 0 0 2.585 3.217 16.44 16.44 0 0 0 .233.244l.014.013.004.004v.002h.001L10 18zM5.25 7.5A4.75 4.75 0 0 1 10 2.75v-1.5A6.25 6.25 0 0 0 3.75 7.5h1.5zM10 2.75a4.75 4.75 0 0 1 4.75 4.75h1.5A6.25 6.25 0 0 0 10 1.25v1.5z" fill="#29A3A3"/><circle cx="10" cy="7.5" r="1.5" fill="#29A3A3" fill-opacity=".3"/></svg>',
			'use' => SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY,
		],
		SettingsMailer::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMailer::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailer::FILTER_SETTINGS_NAME,
			'valid' => SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="4" width="18" height="12" rx="1.5" stroke="#29A3A3" stroke-width="1.5" fill="none"/><path d="m2 5 8 6 8-6M2 15.5l5.5-6m11 6-5.5-6" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'use' => SettingsMailer::SETTINGS_MAILER_USE_KEY,
		],
		SettingsMailchimp::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMailchimp::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailchimp::FILTER_SETTINGS_NAME,
			'fields' => Mailchimp::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsMailchimp::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.927 11.43c-.615-.488-1.661-1.85-.923-3.408.923-1.947 3.23-5.354 5.076-6.328 2.662-1.404 2.768-.973 4.152 0" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M4.772 9.969c0-1.947 1.043-5.94 8.306-9.005 2.307-.973 4.614 1.541 1.845 4.137" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M14.924 5.101c-2.153-.649-6.736-1.752-9.69 2.92m9.69-2.92c.308.65.923 2.19.923 4.138.923.243 2.492 1.022 1.384 2.19.77.487 1.846 2.142 0 4.868-1.846 2.725-5.075 3.082-6.46 2.92-1.23-.162-3.968-1.265-5.075-4.38" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15.616 7.535c-.154-.487-1.061-1.655-2.538-.487-1.697 1.343-3.23-1.46-3.691 1.947 0 .325.185 1.266.923 2.434-.77.974-1.486 2.761-.462 4.38.923 1.461 3.23 2.921 7.383.488" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M17.231 11.43c-.461.486-1.846 1.46-3.691 1.46-1.846 0-2.307.648-2.307.973.153 1.136 1.245 3.115 5.306 0m-5.063-4.031c.175-.2.65-.5 1.149-.1m1.376-1.223.23.73" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><ellipse cx="14.117" cy="11.064" rx=".577" ry=".608" fill="#29A3A3"/><ellipse cx="14.809" cy="10.821" rx=".577" ry=".608" fill="#29A3A3"/><path d="M2.927 11.43c.566-1.088 1.385-1.461 1.846-1.461.462 0 1.846.487 1.846 2.92 0 2.613-3.158 1.835-3.62.861-.499-1-.499-1.5-.072-2.32z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M5.695 14.35c-.307-.812-.825-2.49-1.195-2.1" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
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
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="15.373" r="3.75" stroke="#29A3A3" stroke-width="1.5" fill="none"/><circle cx="10" cy="5.873" r="2.75" stroke="#29A3A3" stroke-width="1.5" fill="none"/><circle cx="13" cy="1.373" r="1.25" fill="#29A3A3"/><path d="M9.25 8.623c.5.5 1.2 1.8 0 3" stroke="#29A3A3" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M10.912 8.623c-.5.5-1.2 1.8 0 3m1.885-10.5c-.085.453-.513 1.454-1.547 1.844" stroke="#29A3A3" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M13.371 1.606c-.43.162-1.343.757-1.547 1.843" stroke="#29A3A3" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
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
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m8.5 17 2.5-2m3.25-11v3.5M3.5 3 11 8.625" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" fill="none"/><circle cx="14.25" cy="11.75" r="4.25" stroke="#29A3A3" stroke-width="1.5" fill="none"/><circle cx="2.75" cy="2.25" fill="#29A3A3" r="1.75"/><circle cx="14.25" cy="2.75" fill="#29A3A3" r="1.75"/><circle cx="7.75" cy="17.75" fill="#29A3A3" r="1.75"/></svg>',
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
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.25 11.25v-5m2.5 5v-3" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="m11.25 11.2-.304.06a1 1 0 0 1-1.196-.98V6.25l-1 1h2" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="6.75" cy="6.5" r=".75" fill="#29A3A3"/><path d="M13 9h3.25v-.725c0-.897-.727-1.625-1.625-1.625v0c-.898 0-1.625.728-1.625 1.625V9zm0 0v.4c0 2 1.5 2.1 3.25 1.668" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M3.676 14.703 1 17.5V4a1.5 1.5 0 0 1 1.5-1.5h15A1.5 1.5 0 0 1 19 4v9.203a1.5 1.5 0 0 1-1.5 1.5H3.676z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
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
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m6.5 13.5 2.358-7.074m.249 7.074 2.358-7.074m.25 7.074 2.358-7.074" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="10" cy="10" r="9" stroke="#29A3A3" stroke-width="1.5" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY,
		],
		SettingsClearbit::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsClearbit::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 3a2 2 0 0 1 2-2h7v18H3a2 2 0 0 1-2-2V3z" fill="#29A3A3"/><path opacity=".7" d="M10 1h7a2 2 0 0 1 2 2v7h-9V1z" fill="#29A3A3"/><path opacity=".4" d="M10 10h9v7a2 2 0 0 1-2 2h-7v-9z" fill="#29A3A3"/></svg>',
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
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m4 1.5 10.272 7.276a1.5 1.5 0 0 1 0 2.448L4 18.5m0-12L9.5 10" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY,
			'cache' => [
				ActiveCampaignClient::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME,
				ActiveCampaignClient::CACHE_ACTIVE_CAMPAIGN_ITEM_TRANSIENT_NAME,
			],
		],
		SettingsCache::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsCache::FILTER_SETTINGS_GLOBAL_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.5 2.778v7.11c0 .983 1.79 1.779 4 1.779.45 0 .883-.033 1.286-.094M1.5 2.778c0 .982 1.79 1.778 4 1.778s4-.796 4-1.778m-8 0C1.5 1.796 3.29 1 5.5 1s4 .796 4 1.778m0 0V9m-8-2.667c0 .982 1.79 1.778 4 1.778s4-.796 4-1.778" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M10.24 10.877a.75.75 0 1 0-1.48.246l1.48-.246zM10.833 19l-.74.123a.75.75 0 0 0 .74.627V19zm5.334 0v.75a.75.75 0 0 0 .74-.627l-.74-.123zm2.073-7.877a.75.75 0 1 0-1.48-.246l1.48.246zM8.5 10.25a.75.75 0 0 0 0 1.5v-1.5zm10 1.5a.75.75 0 0 0 0-1.5v1.5zM10.75 11a.75.75 0 0 0 1.5 0h-1.5zm4 0a.75.75 0 0 0 1.5 0h-1.5zm-1.5 2a.75.75 0 0 0-1.5 0h1.5zm-1.5 4a.75.75 0 0 0 1.5 0h-1.5zm3.5-4a.75.75 0 0 0-1.5 0h1.5zm-1.5 4a.75.75 0 0 0 1.5 0h-1.5zm-4.99-5.877 1.334 8 1.48-.246-1.334-8-1.48.246zm2.073 8.627h5.334v-1.5h-5.334v1.5zm6.074-.627 1.333-8-1.48-.246-1.333 8 1.48.246zM8.5 11.75h10v-1.5h-10v1.5zm3.75-.75c0-.69.56-1.25 1.25-1.25v-1.5A2.75 2.75 0 0 0 10.75 11h1.5zm1.25-1.25c.69 0 1.25.56 1.25 1.25h1.5a2.75 2.75 0 0 0-2.75-2.75v1.5zM11.75 13v4h1.5v-4h-1.5zm2 0v4h1.5v-4h-1.5z" fill="#29A3A3"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
		SettingsLocation::SETTINGS_TYPE_KEY => [
			'settings' => SettingsLocation::FILTER_SETTINGS_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity=".3" d="M7.5 11.75H12m-4.5 3H11m-6.5-6h5" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle opacity=".3" cx="5" cy="11.75" r="1" fill="#29A3A3"/><circle opacity=".3" cx="5" cy="14.75" r="1" fill="#29A3A3"/><path d="M19 14.125c0 2.273-2.5 4.773-2.5 4.773s-2.5-2.5-2.5-4.773a2.5 2.5 0 0 1 5 0z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="16.5" cy="14.125" r=".682" fill="#29A3A3"/><path opacity=".2" fill="#29A3A3" d="M1 1h18v5H1z"/><path d="M19 10V2.5A1.5 1.5 0 0 0 17.5 1h-15A1.5 1.5 0 0 0 1 2.5v15A1.5 1.5 0 0 0 2.5 19H13M4.5 3.75h8" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
		SettingsFallback::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsFallback::FILTER_SETTINGS_GLOBAL_NAME,
			'valid' => SettingsFallback::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 1H7l2.25 6h1.5L13 1zm6 12V7l-6 2.25v1.5L19 13zm-6 6H7l2.25-6h1.5L13 19zM1 13V7l6 2.25v1.5L1 13z" fill="#29A3A3" fill-opacity=".3"/><circle cx="10" cy="10" r="9" stroke="#29A3A3" stroke-width="1.5" fill="none"/><circle cx="10" cy="10" r="3" stroke="#29A3A3" stroke-width="1.5" fill="none"/><path d="M8.8 7 7 2m4.2 5L13 2m0 6.8L18 7m-5 4.2 5 1.8m-9.2 0L7 18m4.2-5 1.8 5M7 8.8 2 7m5 4.2L2 13" stroke="#29A3A3" stroke-width="1.5" fill="none"/><circle opacity=".6" cx="17" cy="3" r="1" stroke="#29A3A3" fill="none"/><circle opacity=".6" cx="17" cy="17" r="1" stroke="#29A3A3" fill="none"/><circle opacity=".6" cx="3" cy="17" r="1" stroke="#29A3A3" fill="none"/><circle opacity=".6" cx="3" cy="3" r="1" stroke="#29A3A3" fill="none"/></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
			'use' => SettingsFallback::SETTINGS_FALLBACK_USE_KEY,
		],
		SettingsDebug::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsDebug::FILTER_SETTINGS_GLOBAL_NAME,
			'valid' => SettingsDebug::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m2.186 16.867 1.197 1.197a1.489 1.489 0 0 0 2.106 0l6.2-6.2c.395-.396.977-.517 1.532-.444 1.416.186 2.873-.246 3.932-1.305 1.392-1.391 1.7-3.47.948-5.24a.236.236 0 0 0-.387-.069l-2.562 2.562a.248.248 0 0 1-.35 0l-1.92-1.92a.248.248 0 0 1 0-.35l2.562-2.562a.236.236 0 0 0-.069-.387c-1.77-.752-3.848-.444-5.24.948-1.06 1.059-1.49 2.516-1.305 3.932.073.555-.049 1.137-.444 1.532l-6.2 6.2a1.489 1.489 0 0 0 0 2.106z" stroke="#29A3A3" stroke-width="1.5" stroke-linejoin="round" fill="none"></path></svg>',
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
			'use' => SettingsDebug::SETTINGS_DEBUG_USE_KEY,
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
		],
		'tracking' => [
			'allowedTags' => 'allowed_tags',
			'expiration' => 'expiration',
		],
		'geolocation' => [
			'disable' => 'disable',
			'countries' => 'countries_list',
			'dbLocation' => 'db_location',
			'pharLocation' => 'phar_location',
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
		],
		'troubleshooting' => [
			'outputLog' => 'output_log',
		],
	];

	public static function getSettingsLabels(string $type, string $key = 'title') {
		$data = [
			SettingsDashboard::SETTINGS_TYPE_KEY => [
				'title' => \__('Dashboard', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change activate all featured you would like to use in your project.', 'eightshift-forms'),
			],
			SettingsGeneral::SETTINGS_TYPE_KEY => [
				'title' => \__('General', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding general configuration.', 'eightshift-forms'),
			],
			SettingsValidation::SETTINGS_TYPE_KEY => [
				'title' => \__('Validation', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding form validation.', 'eightshift-forms'),
			],
			SettingsCaptcha::SETTINGS_TYPE_KEY => [
				'title' => \__('Captcha', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding Google reCaptcha.', 'eightshift-forms'),
				'externalLink' => 'https://www.google.com/recaptcha/about/',
			],
			SettingsGeolocation::SETTINGS_TYPE_KEY => [
				'title' => \__('Geolocation', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding geolocation.', 'eightshift-forms'),
			],
			SettingsMailer::SETTINGS_TYPE_KEY => [
				'title' => \__('Mailer', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding simple emails.', 'eightshift-forms'),
			],
			SettingsMailchimp::SETTINGS_TYPE_KEY => [
				'title' => \__('Mailchimp', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding Mailchimp integration.', 'eightshift-forms'),
				'externalLink' => 'https://mailchimp.com/',
			],
			SettingsGreenhouse::SETTINGS_TYPE_KEY => [
				'title' => \__('Greenhouse', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding Greenhouse integration.', 'eightshift-forms'),
				'externalLink' => 'https://www.greenhouse.io/',
			],
			SettingsHubspot::SETTINGS_TYPE_KEY => [
				'title' => \__('HubSpot', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding HubSpot integration.', 'eightshift-forms'),
				'externalLink' => 'https://www.hubspot.com/',
			],
			SettingsMailerlite::SETTINGS_TYPE_KEY => [
				'title' => \__('MailerLite', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding MailerLite integration.', 'eightshift-forms'),
				'externalLink' => 'https://www.mailerlite.com/',
			],
			SettingsGoodbits::SETTINGS_TYPE_KEY => [
				'title' => \__('Goodbits', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding Goodbits integration.', 'eightshift-forms'),
				'externalLink' => 'https://goodbits.io/',
			],
			SettingsClearbit::SETTINGS_TYPE_KEY => [
				'title' => \__('Clearbit', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding Clearbit integration.', 'eightshift-forms'),
				'externalLink' => 'https://clearbit.com/',
			],
			SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
				'title' => \__('Active Campaign', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding Active Campaign integration.', 'eightshift-forms'),
				'externalLink' => 'https://www.activecampaign.com/',
			],
			SettingsCache::SETTINGS_TYPE_KEY => [
				'title' => \__('Cache', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding forms internal caching.', 'eightshift-forms'),
			],
			SettingsLocation::SETTINGS_TYPE_KEY => [
				'title' => \__('Locations', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding forms location usage.', 'eightshift-forms'),
			],
			SettingsFallback::SETTINGS_TYPE_KEY => [
				'title' => \__('Fallback', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding forms fallbacks.', 'eightshift-forms'),
			],
			SettingsDebug::SETTINGS_TYPE_KEY => [
				'title' => \__('Debug', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding forms debugging.', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_GENERAL => [
				'title' => \__('General', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding all general configuration.', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION => [
				'title' => \__('Integration', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding all forms integrations.', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING => [
				'title' => \__('Troubleshooting', 'eightshift-forms'),
				'desc' => \__('In these settings, you can change all options regarding all troubleshooting.', 'eightshift-forms'),
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
	 * Get Tracking filter by name.
	 *
	 * @param string $name Filter name.
	 *
	 * @throws MissingFilterInfoException Throws error if filter name is missing or wrong.
	 *
	 * @return string
	 *
	 * @example filter_name es_forms_tracking_allowed_tags
	 */
	public static function getTrackingFilterName(string $name): string
	{
		$filter = self::ALL_PUBLIC['tracking'][$name] ?? '';

		if (!$filter) {
			throw MissingFilterInfoException::viewException('tracking', '', $name);
		}

		return self::FILTER_PREFIX . "_tracking_{$filter}";
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
