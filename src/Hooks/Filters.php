<?php

/**
 * The Filters class, used for defining settings and integrations filter variables.
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

use EightshiftForms\Cache\SettingsCache;
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
use EightshiftForms\Integrations\Moments\Moments;
use EightshiftForms\Integrations\Moments\MomentsClient;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Migration\SettingsMigration;
use EightshiftForms\Settings\Settings\Settings;
use EightshiftForms\Dashboard\SettingsDashboard;
use EightshiftForms\Documentation\SettingsDocumentation;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Enrichment\SettingsEnrichment;
use EightshiftForms\Integrations\Jira\JiraClient;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Integrations\Workable\SettingsWorkable;
use EightshiftForms\Integrations\Workable\Workable;
use EightshiftForms\Integrations\Workable\WorkableClient;
use EightshiftForms\Blocks\SettingsBlocks;
use EightshiftForms\Location\SettingsLocation;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftForms\Security\SettingsSecurity;
use EightshiftForms\Validation\SettingsValidation;
use EightshiftForms\Validation\Validator;

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
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsGeneral::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGeneral::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsGeneral::FILTER_SETTINGS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsValidation::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsValidation::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsValidation::FILTER_SETTINGS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'cache' => [
				Validator::CACHE_VALIDATOR_LABELS_TRANSIENT_NAME,
			]
		],
		SettingsCaptcha::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsCaptcha::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'use' => SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY,
		],
		SettingsGeolocation::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGeolocation::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'use' => SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY,
		],
		SettingsEnrichment::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsEnrichment::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'use' => SettingsEnrichment::SETTINGS_ENRICHMENT_USE_KEY,
		],
		SettingsBlocks::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsBlocks::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsBlocks::FILTER_SETTINGS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'countryOutput' => SettingsBlocks::FILTER_SETTINGS_BLOCK_COUNTRY_DATASET_VALUE_NAME,
			'cache' => [
				SettingsBlocks::CACHE_BLOCK_COUNTRY_DATE_SET_NAME,
			],
		],
		SettingsSettings::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsSettings::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
		],
		SettingsSecurity::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsSecurity::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_GENERAL,
			'use' => SettingsSecurity::SETTINGS_SECURITY_USE_KEY,
		],
		SettingsMailer::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMailer::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailer::FILTER_SETTINGS_NAME,
			'valid' => SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsMailer::SETTINGS_MAILER_USE_KEY,
		],
		SettingsMailchimp::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMailchimp::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsMailchimp::FILTER_SETTINGS_NAME,
			'fields' => Mailchimp::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY,
			'cache' => [
				MailchimpClient::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME,
			],
		],
		SettingsGreenhouse::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGreenhouse::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Greenhouse::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsGreenhouse::SETTINGS_GREENHOUSE_USE_KEY,
			'cache' => [
				GreenhouseClient::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME,
			],
		],
		SettingsHubspot::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsHubspot::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsHubspot::FILTER_SETTINGS_NAME,
			'fields' => Hubspot::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY,
			'cache' => [
				HubspotClient::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME,
				HubspotClient::CACHE_HUBSPOT_CONTACT_PROPERTIES_TRANSIENT_NAME,
			],
		],
		SettingsMailerlite::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMailerlite::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Mailerlite::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsMailerlite::SETTINGS_MAILERLITE_USE_KEY,
			'cache' => [
				MailerliteClient::CACHE_MAILERLITE_ITEMS_TRANSIENT_NAME,
			],
		],
		SettingsGoodbits::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsGoodbits::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY,
		],
		SettingsClearbit::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsClearbit::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
			'integration' => [
				SettingsHubspot::SETTINGS_TYPE_KEY => [
					'use' => SettingsHubspot::SETTINGS_HUBSPOT_USE_CLEARBIT_KEY,
					'map' => SettingsHubspot::SETTINGS_HUBSPOT_CLEARBIT_MAP_KEYS_KEY,
				],
			],
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsClearbit::SETTINGS_CLEARBIT_USE_KEY,
		],
		SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsActiveCampaign::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => ActiveCampaign::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY,
			'cache' => [
				ActiveCampaignClient::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME,
			],
		],
		SettingsAirtable::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsAirtable::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Airtable::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsAirtable::SETTINGS_AIRTABLE_USE_KEY,
			'cache' => [
				AirtableClient::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME,
			],
		],
		SettingsMoments::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMoments::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Moments::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsMoments::SETTINGS_MOMENTS_USE_KEY,
			'cache' => [
				MomentsClient::CACHE_MOMENTS_ITEMS_TRANSIENT_NAME,
				MomentsClient::CACHE_MOMENTS_TOKEN_TRANSIENT_NAME,
			],
		],
		SettingsWorkable::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsWorkable::FILTER_SETTINGS_GLOBAL_NAME,
			'fields' => Workable::FILTER_FORM_FIELDS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsWorkable::SETTINGS_WORKABLE_USE_KEY,
			'cache' => [
				WorkableClient::CACHE_WORKABLE_ITEMS_TRANSIENT_NAME,
			],
		],
		SettingsJira::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsJira::FILTER_SETTINGS_GLOBAL_NAME,
			'settings' => SettingsJira::FILTER_SETTINGS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
			'use' => SettingsJira::SETTINGS_JIRA_USE_KEY,
			'cache' => [
				JiraClient::CACHE_JIRA_PROJECTS_TRANSIENT_NAME,
				JiraClient::CACHE_JIRA_ISSUE_TYPE_TRANSIENT_NAME,
			],
			'emailTemplateTags' => [
				'jiraIssueId',
				'jiraIssueKey',
				'jiraIssueUrl',
			]
		],
		SettingsCloudflare::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsCloudflare::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_MISCELLANEOUS,
			'use' => SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY,
		],
		SettingsCache::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsCache::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
		SettingsLocation::SETTINGS_TYPE_KEY => [
			'settings' => SettingsLocation::FILTER_SETTINGS_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
		SettingsFallback::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsFallback::FILTER_SETTINGS_GLOBAL_NAME,
			'valid' => SettingsFallback::FILTER_SETTINGS_IS_VALID_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
			'use' => SettingsFallback::SETTINGS_FALLBACK_USE_KEY,
		],
		SettingsMigration::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsMigration::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
			'use' => SettingsMigration::SETTINGS_MIGRATION_USE_KEY,
		],
		SettingsTransfer::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsTransfer::FILTER_SETTINGS_GLOBAL_NAME,
			'valid' => SettingsTransfer::FILTER_SETTINGS_IS_VALID_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
			'use' => SettingsTransfer::SETTINGS_TRANSFER_USE_KEY,
		],
		SettingsDebug::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsDebug::FILTER_SETTINGS_GLOBAL_NAME,
			'valid' => SettingsDebug::FILTER_SETTINGS_IS_VALID_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
			'use' => SettingsDebug::SETTINGS_DEBUG_USE_KEY,
		],
		SettingsDocumentation::SETTINGS_TYPE_KEY => [
			'settingsGlobal' => SettingsDocumentation::FILTER_SETTINGS_GLOBAL_NAME,
			'type' => Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
		],
	];

	/**
	 * All public filters.
	 */
	public const ALL_PUBLIC = [
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
			'customData' => [
				'options',
				'data',
			],
			'submit' => [
				'component',
				'additionalContent',
			],
			'step' => [
				'componentPrev',
				'componentNext',
			],
		],
		'blocks' => [
			'additionalBlocks',
			'mediaBreakpoints',
		],
		'general' => [
			'httpRequestTimeout',
			'locale',
			'scriptsDependency',
		],
		'geolocation' => [
			'countriesList',
			'dbLocation',
			'pharLocation',
		],
		'integrations' => [
			SettingsMailchimp::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
			],
			SettingsGreenhouse::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
			],
			SettingsHubspot::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
				'filesOptions',
			],
			SettingsMailerlite::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
			],
			SettingsGoodbits::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
			],
			SettingsClearbit::SETTINGS_TYPE_KEY => [
				'map',
			],
			SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
			],
			SettingsAirtable::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
			],
			SettingsMoments::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
			],
			SettingsWorkable::SETTINGS_TYPE_KEY => [
				'data',
				'prePostParams',
				'order',
			],
		],
		'enrichment' => [
			'manualMap',
		],
		'validation' => [
			'forceMimetypeFromFs',
		],
		'migration' => [
			'twoToThree',
			'threeToFour',
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
				'desc' => \__('Choose the features you want to use in your project.', 'eightshift-forms'),
			],
			SettingsGeneral::SETTINGS_TYPE_KEY => [
				'title' => \__('General', 'eightshift-forms'),
			],
			SettingsValidation::SETTINGS_TYPE_KEY => [
				'title' => \__('Validation', 'eightshift-forms'),
			],
			SettingsCaptcha::SETTINGS_TYPE_KEY => [
				'title' => \__('Spam prevention', 'eightshift-forms'),
				'externalLink' => 'https://www.google.com/recaptcha/about/',
			],
			SettingsGeolocation::SETTINGS_TYPE_KEY => [
				'title' => \__('Geolocation', 'eightshift-forms'),
				'desc' => \__('Render different forms based on the user\'s location, using an internal geolocation API.', 'eightshift-forms'),
			],
			SettingsEnrichment::SETTINGS_TYPE_KEY => [
				'title' => \__('Enrichment', 'eightshift-forms'),
				'desc' => \__('Using saved URL parameters and cookies track users even when they leave the site.', 'eightshift-forms'),
			],
			SettingsBlocks::SETTINGS_TYPE_KEY => [
				'title' => \__('Blocks', 'eightshift-forms'),
			],
			SettingsSettings::SETTINGS_TYPE_KEY => [
				'title' => \__('Settings', 'eightshift-forms'),
				'desc' => \__('Disable default scripts and styles, configure behaviors after form submission.', 'eightshift-forms'),
			],
			SettingsSecurity::SETTINGS_TYPE_KEY => [
				'title' => \__('Security', 'eightshift-forms'),
				'desc' => \__('Prevent your forms from being misused and your website from being exploited.', 'eightshift-forms'),
			],
			SettingsMailer::SETTINGS_TYPE_KEY => [
				'title' => \__('Mailer', 'eightshift-forms'),
				'detail' => \__('A basic e-mail sender.', 'eightshift-forms'),
				'icon' => 'mailer',
			],
			SettingsMailchimp::SETTINGS_TYPE_KEY => [
				'title' => \__('Mailchimp', 'eightshift-forms'),
				'detail' => \__('Comprehensive email marketing platform that fulfills all your requirements of email correspondence with customers, affiliates, and more.', 'eightshift-forms'),
				'externalLink' => 'https://mailchimp.com/',
				'icon' => 'mailchimp',
			],
			SettingsGreenhouse::SETTINGS_TYPE_KEY => [
				'title' => \__('Greenhouse', 'eightshift-forms'),
				'detail' => \__('Sourcing automation tool to help hiring teams find, reach and engage top talent quickly and effectively.', 'eightshift-forms'),
				'externalLink' => 'https://www.greenhouse.io/',
				'icon' => 'greenhouse',
			],
			SettingsHubspot::SETTINGS_TYPE_KEY => [
				'title' => \__('HubSpot', 'eightshift-forms'),
				'detail' => \__('CRM platform that connects everything scaling companies need to deliver a best-in-class customer experience into one place.', 'eightshift-forms'),
				'externalLink' => 'https://www.hubspot.com/',
				'icon' => 'hubspot',
			],
			SettingsMailerlite::SETTINGS_TYPE_KEY => [
				'title' => \__('MailerLite', 'eightshift-forms'),
				'detail' => \__('Email service provider that makes it easier to plan email marketing campaigns for any growing business.', 'eightshift-forms'),
				'externalLink' => 'https://www.mailerlite.com/',
				'icon' => 'mailerlite',
			],
			SettingsGoodbits::SETTINGS_TYPE_KEY => [
				'title' => \__('Goodbits', 'eightshift-forms'),
				'detail' => \__('Helps you and your business create stellar newsletters from the best links your team and customers are reading.', 'eightshift-forms'),
				'externalLink' => 'https://goodbits.io/',
				'icon' => 'goodbits',
			],
			SettingsClearbit::SETTINGS_TYPE_KEY => [
				'title' => \__('Clearbit', 'eightshift-forms'),
				'detail' => \__('Marketing intelligence tool that you can use to effectively get quality B2B data for understanding customers, identifying prospects, and creating personalised marketing and sales exchanges.', 'eightshift-forms'),
				'externalLink' => 'https://clearbit.com/',
				'icon' => 'clearbit',
			],
			SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
				'title' => \__('ActiveCampaign', 'eightshift-forms'),
				'detail' => \__('Integrated email marketing, automation, sales software, and CRM platform.', 'eightshift-forms'),
				'externalLink' => 'https://www.activecampaign.com/',
				'icon' => 'activeCampaign',
			],
			SettingsAirtable::SETTINGS_TYPE_KEY => [
				'title' => \__('Airtable', 'eightshift-forms'),
				'detail' => \__('Platform that makes it easy to build powerful, custom applications.', 'eightshift-forms'),
				'externalLink' => 'https://airtable.com/',
				'icon' => 'airtable',
			],
			SettingsMoments::SETTINGS_TYPE_KEY => [
				'title' => \__('Moments', 'eightshift-forms'),
				'detail' => \__('Allows you to easily send relevant content derived from important customer information, their interests, and activities.', 'eightshift-forms'),
				'externalLink' => 'https://www.infobip.com/moments/',
				'icon' => 'moments',
			],
			SettingsWorkable::SETTINGS_TYPE_KEY => [
				'title' => \__('Workable', 'eightshift-forms'),
				'detail' => \__('Sourcing automation tool to help hiring teams find, reach and engage top talent quickly and effectively.', 'eightshift-forms'),
				'externalLink' => 'https://www.workable.com/',
				'icon' => 'workable',
			],
			SettingsJira::SETTINGS_TYPE_KEY => [
				'title' => \__('Jira', 'eightshift-forms'),
				'desc' => \__('Jira integration settings.', 'eightshift-forms'),
				'detail' => \__('Jira is a marketing intelligence tool that you can use to effectively get quality B2B data for understanding customers, identifying prospects, and creating personalised marketing and sales exchanges.', 'eightshift-forms'),
				'externalLink' => 'https://jira.atlassian.com/',
				'icon' => 'jira',
			],
			SettingsCache::SETTINGS_TYPE_KEY => [
				'title' => \__('Cache', 'eightshift-forms'),
				'desc' => \__('Force data re-fetch for certain integrations.', 'eightshift-forms'),
			],
			SettingsLocation::SETTINGS_TYPE_KEY => [
				'title' => \__('Locations', 'eightshift-forms'),
				'desc' => \__('See where on the site a form is used.', 'eightshift-forms'),
			],
			SettingsFallback::SETTINGS_TYPE_KEY => [
				'title' => \__('Fallback e-mails', 'eightshift-forms'),
				'desc' => \__('Send a plain-text e-mail in case a form submission fails to complete.', 'eightshift-forms'),
			],
			SettingsDebug::SETTINGS_TYPE_KEY => [
				'title' => \__('Debug', 'eightshift-forms'),
				'desc' => \__('If a form is not working correctly, use these settings to solve problems more easily.', 'eightshift-forms'),
			],
			SettingsTransfer::SETTINGS_TYPE_KEY => [
				'title' => \__('Import/export', 'eightshift-forms'),
				'desc' => \__('Easily transfer forms and settings from one enviroment to another.', 'eightshift-forms'),
			],
			SettingsDocumentation::SETTINGS_TYPE_KEY => [
				'title' => \__('Documentation', 'eightshift-forms'),
				'desc' => \__('Need help? Interested in learning more? Find resources here.', 'eightshift-forms'),
			],
			SettingsCloudflare::SETTINGS_TYPE_KEY => [
				'title' => \__('Cloudflare', 'eightshift-forms'),
				'desc' => \__('Cloudflare is a content delivery network (CDN) and cloud security platform that provides website optimization, security, and performance services.', 'eightshift-forms'),
			],
			SettingsMigration::SETTINGS_TYPE_KEY => [
				'title' => \__('Migration', 'eightshift-forms'),
				'desc' => \__('One-click migrate forms from earlier versions of Forms.', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_GENERAL => [
				'title' => \__('General', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION => [
				'title' => \__('Integrations', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING => [
				'title' => \__('Troubleshooting', 'eightshift-forms'),
				'desc' => \__('Settings for various troubleshooting and debugging options.', 'eightshift-forms'),
			],
			Settings::SETTINGS_SIEDBAR_TYPE_MISCELLANEOUS => [
				'title' => \__('Miscellaneous', 'eightshift-forms'),
				'desc' => \__('Settings for various miscellaneous options.', 'eightshift-forms'),
			],
		];

		return isset($data[$type][$key]) ? $data[$type][$key] : '';
	}

	/**
	 * Get the settings labels and details by type and key.
	 * This method is used to provide the ability to translate all strings.
	 *
	 * @param string $type Settings type from the Settings class.
	 *
	 * @return array<string, string>
	 */
	public static function getSpecialConstants(string $type): array
	{
		$data = [
			'tracking' => [
				'{invalidFieldsString}' => \__('comma-separated list of invalid fields', 'eightshift-forms'),
				'{invalidFieldsArray}' => \__('array of invalid fields', 'eightshift-forms'),
			],
		];
		return isset($data[$type]) ? $data[$type] : [];
	}

	/**
	 * Get private filter name.
	 *
	 * @param array<int, string> $names Array of names.
	 *
	 * @return string
	 */
	public static function getFilterName(array $names): string
	{
		$names = \array_map(
			function ($item) {
				return Helper::camelToSnakeCase($item);
			},
			$names
		);

		$names = \implode('_', $names);

		return self::FILTER_PREFIX . "_{$names}";
	}
}
