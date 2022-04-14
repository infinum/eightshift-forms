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
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\Settings\SettingsLocation;
use EightshiftForms\Settings\Settings\SettingsTest;
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
		SettingsGeneral::SETTINGS_TYPE_KEY => [
			'global' => SettingsGeneral::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGeneral::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsGeneral::FILTER_SETTINGS_SIDEBAR_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.196 1.41c-1.813-.743-3.59-.31-4.25 0 .118 1.619-.581 4.37-4.321 2.428-.661.62-2.012 2.186-2.125 3.5 1.653.761 3.995 2.885.142 5.285.236.976.963 3.042 1.983 3.499 1.417-1 4.264-1.9 4.32 2.5.922.285 3.06.685 4.25 0-.117-1.762.567-4.728 4.25-2.5.567-.476 1.772-1.843 2.055-3.5-1.511-.928-3.627-3.285 0-5.284-.212-.834-.935-2.7-2.125-3.5-3.287 1.943-4.156-.81-4.18-2.428z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><ellipse cx="10.071" cy="9.91" rx="2.975" ry="3" stroke="#29A3A3" stroke-width="1.5" fill="none"/></svg>',
		],
		SettingsValidation::SETTINGS_TYPE_KEY => [
			'global' => SettingsValidation::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsValidation::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsValidation::FILTER_SETTINGS_SIDEBAR_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5.25 9.813 3.818 3.937 8.182-9" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15.579 2.621A9.21 9.21 0 0 0 10 .75a9.25 9.25 0 1 0 8.758 6.266" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		],
		SettingsCaptcha::SETTINGS_TYPE_KEY => [
			'global' => SettingsCaptcha::FILTER_SETTINGS_GLOBAL_NAME,
			'settingsSidebar' => SettingsCaptcha::FILTER_SETTINGS_SIDEBAR_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.5 13v-1.5m4 1.5v-1.5m-2-8c.828 0 1.25-.422 1.25-1.25C6.75 1.422 6.328 1 5.5 1c-.828 0-1.25.422-1.25 1.25 0 .828.422 1.25 1.25 1.25zm0 0V5M1 7.3a1.8 1.8 0 0 1 1.8-1.8h5.4A1.8 1.8 0 0 1 10 7.3v2.4a1.8 1.8 0 0 1-1.8 1.8H2.8A1.8 1.8 0 0 1 1 9.7V7.3z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="3.75" cy="8.25" r=".75" fill="#29A3A3"/><circle cx="7.25" cy="8.25" r=".75" fill="#29A3A3"/><path d="M12.264 17.918a4 4 0 0 0 5.654-5.654m-5.654 5.654a4 4 0 1 1 5.654-5.654m-5.654 5.654 5.654-5.654" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		],
		SettingsCache::SETTINGS_TYPE_KEY => [
			'global' => SettingsCache::FILTER_SETTINGS_GLOBAL_NAME,
			'settingsSidebar' => SettingsCache::FILTER_SETTINGS_SIDEBAR_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.5 2.778v7.11c0 .983 1.79 1.779 4 1.779.45 0 .883-.033 1.286-.094M1.5 2.778c0 .982 1.79 1.778 4 1.778s4-.796 4-1.778m-8 0C1.5 1.796 3.29 1 5.5 1s4 .796 4 1.778m0 0V9m-8-2.667c0 .982 1.79 1.778 4 1.778s4-.796 4-1.778" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M10.24 10.877a.75.75 0 1 0-1.48.246l1.48-.246zM10.833 19l-.74.123a.75.75 0 0 0 .74.627V19zm5.334 0v.75a.75.75 0 0 0 .74-.627l-.74-.123zm2.073-7.877a.75.75 0 1 0-1.48-.246l1.48.246zM8.5 10.25a.75.75 0 0 0 0 1.5v-1.5zm10 1.5a.75.75 0 0 0 0-1.5v1.5zM10.75 11a.75.75 0 0 0 1.5 0h-1.5zm4 0a.75.75 0 0 0 1.5 0h-1.5zm-1.5 2a.75.75 0 0 0-1.5 0h1.5zm-1.5 4a.75.75 0 0 0 1.5 0h-1.5zm3.5-4a.75.75 0 0 0-1.5 0h1.5zm-1.5 4a.75.75 0 0 0 1.5 0h-1.5zm-4.99-5.877 1.334 8 1.48-.246-1.334-8-1.48.246zm2.073 8.627h5.334v-1.5h-5.334v1.5zm6.074-.627 1.333-8-1.48-.246-1.333 8 1.48.246zM8.5 11.75h10v-1.5h-10v1.5zm3.75-.75c0-.69.56-1.25 1.25-1.25v-1.5A2.75 2.75 0 0 0 10.75 11h1.5zm1.25-1.25c.69 0 1.25.56 1.25 1.25h1.5a2.75 2.75 0 0 0-2.75-2.75v1.5zM11.75 13v4h1.5v-4h-1.5zm2 0v4h1.5v-4h-1.5z" fill="#29A3A3"/></svg>',
		],
		SettingsMailer::SETTINGS_TYPE_KEY => [
			'settings' => SettingsMailer::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsMailer::FILTER_SETTINGS_SIDEBAR_NAME,
			'valid' => SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="4" width="18" height="12" rx="1.5" stroke="#29A3A3" stroke-width="1.5" fill="none"/><path d="m2 5 8 6 8-6M2 15.5l5.5-6m11 6-5.5-6" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		],
		SettingsMailchimp::SETTINGS_TYPE_KEY => [
			'global' => SettingsMailchimp::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailchimp::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsMailchimp::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Mailchimp::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsMailchimp::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.927 11.43c-.615-.488-1.661-1.85-.923-3.408.923-1.947 3.23-5.354 5.076-6.328 2.662-1.404 2.768-.973 4.152 0" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M4.772 9.969c0-1.947 1.043-5.94 8.306-9.005 2.307-.973 4.614 1.541 1.845 4.137" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M14.924 5.101c-2.153-.649-6.736-1.752-9.69 2.92m9.69-2.92c.308.65.923 2.19.923 4.138.923.243 2.492 1.022 1.384 2.19.77.487 1.846 2.142 0 4.868-1.846 2.725-5.075 3.082-6.46 2.92-1.23-.162-3.968-1.265-5.075-4.38" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15.616 7.535c-.154-.487-1.061-1.655-2.538-.487-1.697 1.343-3.23-1.46-3.691 1.947 0 .325.185 1.266.923 2.434-.77.974-1.486 2.761-.462 4.38.923 1.461 3.23 2.921 7.383.488" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M17.231 11.43c-.461.486-1.846 1.46-3.691 1.46-1.846 0-2.307.648-2.307.973.153 1.136 1.245 3.115 5.306 0m-5.063-4.031c.175-.2.65-.5 1.149-.1m1.376-1.223.23.73" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><ellipse cx="14.117" cy="11.064" rx=".577" ry=".608" fill="#29A3A3"/><ellipse cx="14.809" cy="10.821" rx=".577" ry=".608" fill="#29A3A3"/><path d="M2.927 11.43c.566-1.088 1.385-1.461 1.846-1.461.462 0 1.846.487 1.846 2.92 0 2.613-3.158 1.835-3.62.861-.499-1-.499-1.5-.072-2.32z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M5.695 14.35c-.307-.812-.825-2.49-1.195-2.1" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		],
		SettingsGreenhouse::SETTINGS_TYPE_KEY => [
			'global' => SettingsGreenhouse::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGreenhouse::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsGreenhouse::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Greenhouse::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsGreenhouse::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="15.373" r="3.75" stroke="#29A3A3" stroke-width="1.5" fill="none"/><circle cx="10" cy="5.873" r="2.75" stroke="#29A3A3" stroke-width="1.5" fill="none"/><circle cx="13" cy="1.373" r="1.25" fill="#29A3A3"/><path d="M9.25 8.623c.5.5 1.2 1.8 0 3" stroke="#29A3A3" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M10.912 8.623c-.5.5-1.2 1.8 0 3m1.885-10.5c-.085.453-.513 1.454-1.547 1.844" stroke="#29A3A3" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M13.371 1.606c-.43.162-1.343.757-1.547 1.843" stroke="#29A3A3" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		],
		SettingsHubspot::SETTINGS_TYPE_KEY => [
			'global' => SettingsHubspot::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsHubspot::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsHubspot::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Hubspot::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsHubspot::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m8.5 17 2.5-2m3.25-11v3.5M3.5 3 11 8.625" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" fill="none"/><circle cx="14.25" cy="11.75" r="4.25" stroke="#29A3A3" stroke-width="1.5" fill="none"/><circle cx="2.75" cy="2.25" fill="#29A3A3" r="1.75"/><circle cx="14.25" cy="2.75" fill="#29A3A3" r="1.75"/><circle cx="7.75" cy="17.75" fill="#29A3A3" r="1.75"/></svg>',
		],
		SettingsMailerlite::SETTINGS_TYPE_KEY => [
			'global' => SettingsMailerlite::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailerlite::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsMailerlite::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Mailerlite::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsMailerlite::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.25 11.25v-5m2.5 5v-3" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="m11.25 11.2-.304.06a1 1 0 0 1-1.196-.98V6.25l-1 1h2" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="6.75" cy="6.5" r=".75" fill="#29A3A3"/><path d="M13 9h3.25v-.725c0-.897-.727-1.625-1.625-1.625v0c-.898 0-1.625.728-1.625 1.625V9zm0 0v.4c0 2 1.5 2.1 3.25 1.668" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M3.676 14.703 1 17.5V4a1.5 1.5 0 0 1 1.5-1.5h15A1.5 1.5 0 0 1 19 4v9.203a1.5 1.5 0 0 1-1.5 1.5H3.676z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		],
		SettingsGoodbits::SETTINGS_TYPE_KEY => [
			'global' => SettingsGoodbits::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGoodbits::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsGoodbits::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
			'valid' => SettingsGoodbits::FILTER_SETTINGS_IS_VALID_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m6.5 13.5 2.358-7.074m.249 7.074 2.358-7.074m.25 7.074 2.358-7.074" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="10" cy="10" r="9" stroke="#29A3A3" stroke-width="1.5" fill="none"/></svg>',
		],
		SettingsClearbit::SETTINGS_TYPE_KEY => [
			'global' => SettingsClearbit::FILTER_SETTINGS_GLOBAL_NAME,
			'settingsSidebar' => SettingsClearbit::FILTER_SETTINGS_SIDEBAR_NAME,
			'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
			'icon' => '<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><g fill-rule="nonzero" fill="none"><path d="M10 0h7.324C18.8 0 20 1.2 20 2.676V10H10V0Z" fill="#31C2C2"/><path d="M0 10h10v10H3.19A3.191 3.191 0 0 1 0 16.81V10Z" fill="#29A3A3"/><path d="M10 10h10v7.431c0 1.42-1.15 2.57-2.57 2.57H10V10Z" fill="#E7F2FC"/><path d="M3.273 0H10v10H0V3.273A3.275 3.275 0 0 1 3.273 0Z" fill="#2CB7B7"/></g></svg>',
			'integration' => [
				SettingsHubspot::SETTINGS_TYPE_KEY => [
					'use' => SettingsHubspot::SETTINGS_HUBSPOT_USE_CLEARBIT_KEY,
					'email' => SettingsHubspot::SETTINGS_HUBSPOT_CLEARBIT_EMAIL_FIELD_KEY,
					'map' => SettingsHubspot::SETTINGS_HUBSPOT_CLEARBIT_MAP_KEYS_KEY,
				],
			]
		],
		SettingsLocation::SETTINGS_TYPE_KEY => [
			'settings' => SettingsLocation::FILTER_SETTINGS_NAME,
			'settingsSidebar' => SettingsLocation::FILTER_SETTINGS_SIDEBAR_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity=".3" d="M7.5 11.75H12m-4.5 3H11m-6.5-6h5" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle opacity=".3" cx="5" cy="11.75" r="1" fill="#29A3A3"/><circle opacity=".3" cx="5" cy="14.75" r="1" fill="#29A3A3"/><path d="M19 14.125c0 2.273-2.5 4.773-2.5 4.773s-2.5-2.5-2.5-4.773a2.5 2.5 0 0 1 5 0z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="16.5" cy="14.125" r=".682" fill="#29A3A3"/><path opacity=".2" fill="#29A3A3" d="M1 1h18v5H1z"/><path d="M19 10V2.5A1.5 1.5 0 0 0 17.5 1h-15A1.5 1.5 0 0 0 1 2.5v15A1.5 1.5 0 0 0 2.5 19H13M4.5 3.75h8" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		],
		SettingsGeolocation::SETTINGS_TYPE_KEY => [
			'global' => SettingsGeolocation::FILTER_SETTINGS_GLOBAL_NAME,
			'settingsSidebar' => SettingsGeolocation::FILTER_SETTINGS_SIDEBAR_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><ellipse cx="10" cy="18.625" rx="2.5" ry=".625" fill="#29A3A3" fill-opacity=".12"/><path d="m10 18-.53.53a.75.75 0 0 0 1.06 0L10 18zm4.75-10.5c0 2.261-1.26 4.726-2.618 6.7a27.012 27.012 0 0 1-2.442 3.04 14.893 14.893 0 0 1-.208.218l-.01.01-.002.002.53.53.53.53h.001l.001-.002a2.19 2.19 0 0 0 .018-.018l.05-.05.183-.193a28.473 28.473 0 0 0 2.585-3.217c1.393-2.026 2.882-4.811 2.882-7.55h-1.5zM10 18l.53-.53-.002-.002-.01-.01a8.665 8.665 0 0 1-.208-.217 27 27 0 0 1-2.442-3.04C6.511 12.225 5.25 9.76 5.25 7.5h-1.5c0 2.739 1.49 5.524 2.882 7.55a28.494 28.494 0 0 0 2.585 3.217 16.44 16.44 0 0 0 .233.244l.014.013.004.004v.002h.001L10 18zM5.25 7.5A4.75 4.75 0 0 1 10 2.75v-1.5A6.25 6.25 0 0 0 3.75 7.5h1.5zM10 2.75a4.75 4.75 0 0 1 4.75 4.75h1.5A6.25 6.25 0 0 0 10 1.25v1.5z" fill="#29A3A3"/><circle cx="10" cy="7.5" r="1.5" fill="#29A3A3" fill-opacity=".3"/></svg>',
		],
		SettingsTest::SETTINGS_TYPE_KEY => [
			'global' => SettingsTest::FILTER_SETTINGS_GLOBAL_NAME,
			'settingsSidebar' => SettingsTest::FILTER_SETTINGS_SIDEBAR_NAME,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.988 7.75v-5c0-1-.315-1.5-1.037-1.5-.72 0-1.217.5-1.217 1.5v8L5.372 7.9c-.58-.767-1.111-.925-1.664-.41-.553.513-.643 1.25-.063 2.016l.934 1.244c.45.5.587 1.104 1.352 4.5.45 2 2.664 3.432 4.959 3.5 3.113.092 5.86-2.545 5.86-6v-4c0-1-.316-1.5-1.037-1.5-.721 0-1.217.5-1.217 1.5m-4.508-1c0-1 .496-1.5 1.217-1.5.722 0 1.037 1 1.037 2m-2.254-.5v1.5m2.254-1c0-1 .496-1.5 1.217-1.5.722 0 1.037 1 1.037 2m-2.254-.5v1.5m2.254-1v2" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
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
		],
		'geolocation' => [
			'disable' => 'disable',
			'countries' => 'countries_list',
			'userLocation' => 'user_location',
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
		]
	];

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
	public static function getValidationSettingsFilterName(string $name): string {
		$filter = self::ALL_PUBLIC['validation'][$name] ?? '';
		if (!$filter) {
			throw MissingFilterInfoException::viewException('geolocation', '', $name);
		}

		return self::FILTER_PREFIX . "_validation_{$filter}";
	}
}
