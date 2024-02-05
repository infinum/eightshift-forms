<?php

/**
 * The FiltersSettingsBuilder class, used for defining settings and project settings data.
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Geolocation\SettingsGeolocation;
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
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\Entries\SettingsEntries;
use EightshiftForms\Integrations\Pipedrive\PipedriveClient;
use EightshiftForms\Integrations\Pipedrive\SettingsPipedrive;
use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftForms\Misc\SettingsWpml;
use EightshiftForms\Security\SettingsSecurity;
use EightshiftForms\Validation\SettingsValidation;
use EightshiftForms\Validation\Validator;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * The FilFiltersSettingsBuilderters class, used for defining settings and integrations filter variables.
 */
class FiltersSettingsBuilder implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(UtilsConfig::FILTER_SETTINGS_DATA, [$this, 'getSettingsFiltersData']);
	}

	/**
	 * Get the settings filter data.
	 * Order of the filters is important as it determines the order of the items on the frontend side.
	 *
	 * @return array<int|string, mixed>
	 */
	public function getSettingsFiltersData(): array
	{
		$data = [
			// ------------------------------
			// GENERAL.
			// ------------------------------
			UtilsConfig::SETTINGS_INTERNAL_TYPE_GENERAL => [
				'order' => 1,
				'labels' => [
					'title' => \__('General', 'eightshift-forms'),
				],
			],
			SettingsDashboard::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsDashboard::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_GENERAL,
				'labels' => [
					'title' => \__('Dashboard', 'eightshift-forms'),
					'desc' => \__('Choose the features you want to use in your project.', 'eightshift-forms'),
				],
			],
			SettingsGeneral::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsGeneral::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsGeneral::FILTER_SETTINGS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_GENERAL,
				'labels' => [
					'title' => \__('General', 'eightshift-forms'),
					'desc' => \__('General form settings.', 'eightshift-forms'),
				],
			],
			SettingsSettings::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsSettings::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_GENERAL,
				'labels' => [
					'title' => \__('Settings', 'eightshift-forms'),
					'desc' => \__('Disable default scripts and styles, configure behaviors after form submission.', 'eightshift-forms'),
				],
			],
			SettingsValidation::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsValidation::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsValidation::FILTER_SETTINGS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_GENERAL,
				'cache' => [
					Validator::CACHE_VALIDATOR_LABELS_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('Validation', 'eightshift-forms'),
					'desc' => \__('Settings for all forms validation options.', 'eightshift-forms'),
				],
			],
			// ------------------------------
			// ADVANCED.
			// ------------------------------
			UtilsConfig::SETTINGS_INTERNAL_TYPE_ADVANCED => [
				'order' => 2,
				'labels' => [
					'title' => \__('Advanced', 'eightshift-forms'),
				],
			],
			SettingsCaptcha::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsCaptcha::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_ADVANCED,
				'use' => SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY,
				'labels' => [
					'title' => \__('Spam prevention', 'eightshift-forms'),
					'desc' => \__('Prevent misuse of your forms by adding Google ReCaptcha.', 'eightshift-forms'),
					'externalLink' => 'https://www.google.com/recaptcha/about/',
				],
			],
			SettingsGeolocation::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsGeolocation::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_ADVANCED,
				'use' => SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY,
				'labels' => [
					'title' => \__('Geolocation', 'eightshift-forms'),
					'desc' => \__('Render different forms based on the user\'s location, using an internal geolocation API.', 'eightshift-forms'),
				],
			],
			SettingsEnrichment::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsEnrichment::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_ADVANCED,
				'use' => SettingsEnrichment::SETTINGS_ENRICHMENT_USE_KEY,
				'labels' => [
					'title' => \__('Enrichment', 'eightshift-forms'),
					'desc' => \__('Track users when they leave the site by using saved URL parameters and cookies.', 'eightshift-forms'),
				],
			],
			SettingsBlocks::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsBlocks::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsBlocks::FILTER_SETTINGS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_GENERAL,
				'countryOutput' => SettingsBlocks::FILTER_SETTINGS_BLOCK_COUNTRY_DATASET_VALUE_NAME,
				'cache' => [
					SettingsBlocks::CACHE_BLOCK_COUNTRY_DATE_SET_NAME,
				],
				'labels' => [
					'title' => \__('Blocks', 'eightshift-forms'),
					'desc' => \__('Settings specific to forms blocks.', 'eightshift-forms'),
				],
			],
			SettingsSecurity::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsSecurity::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_ADVANCED,
				'use' => SettingsSecurity::SETTINGS_SECURITY_USE_KEY,
				'labels' => [
					'title' => \__('Security', 'eightshift-forms'),
					'desc' => \__('Prevent your forms from being misused and your website from being exploited.', 'eightshift-forms'),
				],
			],
			SettingsEntries::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsEntries::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsEntries::FILTER_SETTINGS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_ADVANCED,
				'use' => SettingsEntries::SETTINGS_ENTRIES_USE_KEY,
				'labels' => [
					'title' => \__('Entries', 'eightshift-forms'),
					'desc' => \__('Collect form entries in your project database.', 'eightshift-forms'),
				],
			],
			// ------------------------------
			// INTEGRATIONS.
			// ------------------------------
			UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION => [
				'order' => 3,
				'labels' => [
					'title' => \__('Integrations', 'eightshift-forms'),
				],
			],
			SettingsMailer::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsMailer::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsMailer::FILTER_SETTINGS_NAME,
				'valid' => SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_NO_BUILDER,
				'use' => SettingsMailer::SETTINGS_MAILER_USE_KEY,
				'labels' => [
					'title' => \__('Mailer', 'eightshift-forms'),
					'desc' => \__('Basic mailer system settings.', 'eightshift-forms'),
					'detail' => \__('A basic e-mail sender.', 'eightshift-forms'),
				],
			],
			SettingsMailchimp::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsMailchimp::FILTER_SETTINGS_GLOBAL_NAME,
				'fields' => Mailchimp::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_DEFAULT,
				'use' => SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY,
				'cache' => [
					MailchimpClient::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('Mailchimp', 'eightshift-forms'),
					'desc' => \__('Mailchimp integration settings.', 'eightshift-forms'),
					'detail' => \__('Comprehensive email marketing platform that fulfills all your requirements of email correspondence with customers, affiliates, and more.', 'eightshift-forms'),
					'externalLink' => 'https://mailchimp.com/',
				],
			],
			SettingsGreenhouse::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsGreenhouse::FILTER_SETTINGS_GLOBAL_NAME,
				'fields' => Greenhouse::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_DEFAULT,
				'use' => SettingsGreenhouse::SETTINGS_GREENHOUSE_USE_KEY,
				'cache' => [
					GreenhouseClient::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('Greenhouse', 'eightshift-forms'),
					'desc' => \__('Greenhouse integration settings.', 'eightshift-forms'),
					'detail' => \__('Sourcing automation tool to help hiring teams find, reach and engage top talent quickly and effectively.', 'eightshift-forms'),
					'externalLink' => 'https://www.greenhouse.io/',
				],
			],
			SettingsHubspot::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsHubspot::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsHubspot::FILTER_SETTINGS_NAME,
				'fields' => Hubspot::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_DEFAULT,
				'use' => SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY,
				'cache' => [
					HubspotClient::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME,
					HubspotClient::CACHE_HUBSPOT_CONTACT_PROPERTIES_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('HubSpot', 'eightshift-forms'),
					'desc' => \__('HubSpot integration settings.', 'eightshift-forms'),
					'detail' => \__('CRM platform that connects everything scaling companies need to deliver a best-in-class customer experience into one place.', 'eightshift-forms'),
					'externalLink' => 'https://www.hubspot.com/',
				],
			],
			SettingsMailerlite::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsMailerlite::FILTER_SETTINGS_GLOBAL_NAME,
				'fields' => Mailerlite::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_DEFAULT,
				'use' => SettingsMailerlite::SETTINGS_MAILERLITE_USE_KEY,
				'cache' => [
					MailerliteClient::CACHE_MAILERLITE_ITEMS_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('MailerLite', 'eightshift-forms'),
					'desc' => \__('MailerLite integration settings.', 'eightshift-forms'),
					'detail' => \__('Email service provider that makes it easier to plan email marketing campaigns for any growing business.', 'eightshift-forms'),
					'externalLink' => 'https://www.mailerlite.com/',
				],
			],
			SettingsGoodbits::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsGoodbits::FILTER_SETTINGS_GLOBAL_NAME,
				'fields' => Goodbits::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_DEFAULT,
				'use' => SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY,
				'labels' => [
					'title' => \__('Goodbits', 'eightshift-forms'),
					'desc' => \__('Goodbits integration settings.', 'eightshift-forms'),
					'detail' => \__('Helps you and your business create stellar newsletters from the best links your team and customers are reading.', 'eightshift-forms'),
					'externalLink' => 'https://goodbits.io/',
				],
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
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'use' => SettingsClearbit::SETTINGS_CLEARBIT_USE_KEY,
				'labels' => [
					'title' => \__('Clearbit', 'eightshift-forms'),
					'desc' => \__('Clearbit integration settings.', 'eightshift-forms'),
					'detail' => \__('Marketing intelligence tool that you can use to effectively get quality B2B data for understanding customers, identifying prospects, and creating personalised marketing and sales exchanges.', 'eightshift-forms'),
					'externalLink' => 'https://clearbit.com/',
				],
			],
			SettingsActiveCampaign::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsActiveCampaign::FILTER_SETTINGS_GLOBAL_NAME,
				'fields' => ActiveCampaign::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_COMPLEX,
				'use' => SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY,
				'cache' => [
					ActiveCampaignClient::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('ActiveCampaign', 'eightshift-forms'),
					'desc' => \__('ActiveCampaign integration settings.', 'eightshift-forms'),
					'detail' => \__('Integrated email marketing, automation, sales software, and CRM platform.', 'eightshift-forms'),
					'externalLink' => 'https://www.activecampaign.com/',
				],
			],
			SettingsAirtable::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsAirtable::FILTER_SETTINGS_GLOBAL_NAME,
				'fields' => Airtable::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_DEFAULT,
				'use' => SettingsAirtable::SETTINGS_AIRTABLE_USE_KEY,
				'cache' => [
					AirtableClient::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('Airtable', 'eightshift-forms'),
					'desc' => \__('Airtable integration settings.', 'eightshift-forms'),
					'detail' => \__('Platform that makes it easy to build powerful, custom applications.', 'eightshift-forms'),
					'externalLink' => 'https://airtable.com/',
				],
			],
			SettingsMoments::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsMoments::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsMoments::FILTER_SETTINGS_NAME,
				'fields' => Moments::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_DEFAULT,
				'use' => SettingsMoments::SETTINGS_MOMENTS_USE_KEY,
				'cache' => [
					MomentsClient::CACHE_MOMENTS_ITEMS_TRANSIENT_NAME,
					MomentsClient::CACHE_MOMENTS_TOKEN_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('Moments', 'eightshift-forms'),
					'desc' => \__('Moments integration settings.', 'eightshift-forms'),
					'detail' => \__('Allows you to easily send relevant content derived from important customer information, their interests, and activities.', 'eightshift-forms'),
					'externalLink' => 'https://www.infobip.com/moments/',
				],
			],
			SettingsWorkable::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsWorkable::FILTER_SETTINGS_GLOBAL_NAME,
				'fields' => Workable::FILTER_FORM_FIELDS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_DEFAULT,
				'use' => SettingsWorkable::SETTINGS_WORKABLE_USE_KEY,
				'cache' => [
					WorkableClient::CACHE_WORKABLE_ITEMS_TRANSIENT_NAME,
				],
				'labels' => [
					'title' => \__('Workable', 'eightshift-forms'),
					'desc' => \__('Workable integration settings.', 'eightshift-forms'),
					'detail' => \__('Sourcing automation tool to help hiring teams find, reach and engage top talent quickly and effectively.', 'eightshift-forms'),
					'externalLink' => 'https://www.workable.com/',
				],
			],
			SettingsJira::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsJira::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsJira::FILTER_SETTINGS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_NO_BUILDER,
				'use' => SettingsJira::SETTINGS_JIRA_USE_KEY,
				'cache' => [
					JiraClient::CACHE_JIRA_PROJECTS_TRANSIENT_NAME,
					JiraClient::CACHE_JIRA_ISSUE_TYPE_TRANSIENT_NAME,
				],
				'emailTemplateTags' => [
					'jiraIssueId' => 'id',
					'jiraIssueKey' => 'key',
					'jiraIssueUrl' => 'self',
				],
				'labels' => [
					'title' => \__('Jira', 'eightshift-forms'),
					'desc' => \__('Jira integration settings.', 'eightshift-forms'),
					'detail' => \__('Jira is a marketing intelligence tool that you can use to effectively get quality B2B data for understanding customers, identifying prospects, and creating personalised marketing and sales exchanges.', 'eightshift-forms'),
					'externalLink' => 'https://jira.atlassian.com/',
				],
			],
			SettingsPipedrive::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsPipedrive::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsPipedrive::FILTER_SETTINGS_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => UtilsConfig::INTEGRATION_TYPE_NO_BUILDER,
				'use' => SettingsPipedrive::SETTINGS_PIPEDRIVE_USE_KEY,
				'cache' => [
					PipedriveClient::CACHE_PIPEDRIVE_PERSON_FIELDS_TRANSIENT_NAME,
					PipedriveClient::CACHE_PIPEDRIVE_LEADS_FIELDS_TRANSIENT_NAME,
				],
				'emailTemplateTags' => [
					'pipedrivePersonId' => 'id',
					'pipedriveOrganizationName' => 'org_name',
				],
				'labels' => [
					'title' => \__('Pipedrive', 'eightshift-forms'),
					'desc' => \__('Pipedrive integration settings.', 'eightshift-forms'),
					'detail' => \__('Pipedrive is a web-based Sales CRM and pipeline management solution that enables businesses to plan their sales activities and monitor deals.', 'eightshift-forms'),
					'externalLink' => 'https://www.pipedrive.com/',
				],
			],
			// ------------------------------
			// MISCELLANEOUS.
			// ------------------------------
			UtilsConfig::SETTINGS_INTERNAL_TYPE_MISCELLANEOUS => [
				'order' => 4,
				'labels' => [
					'title' => \__('Miscellaneous', 'eightshift-forms'),
					'desc' => \__('Settings for various miscellaneous options.', 'eightshift-forms'),
				],
			],
			SettingsWpml::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsWpml::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_MISCELLANEOUS,
				'use' => SettingsWpml::SETTINGS_WPML_USE_KEY,
				'labels' => [
					'title' => \__('WPML', 'eightshift-forms'),
					'desc' => \__('WPML is a WordPress plugin, which allows building and running multilingual sites. It integrates with almost all popular WordPress themes and plugins and allows building anything from multilingual blogs to complex e-commerce and corporate sites.', 'eightshift-forms'),
				],
			],
			SettingsCloudflare::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsCloudflare::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_MISCELLANEOUS,
				'use' => SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY,
				'labels' => [
					'title' => \__('Cloudflare', 'eightshift-forms'),
					'desc' => \__('Cloudflare is a content delivery network (CDN) and cloud security platform that provides website optimization, security, and performance services.', 'eightshift-forms'),
				],
			],
			// ------------------------------
			// ADD-ONS.
			// ------------------------------
			UtilsConfig::SETTINGS_INTERNAL_TYPE_ADDON => [
				'order' => 5,
				'labels' => [
					'title' => \__('Add-ons', 'eightshift-forms'),
					'desc' => \__('Settings for various add-on plugins.', 'eightshift-forms'),
				],
			],
			// ------------------------------
			// TROUBLESHOOTING.
			// ------------------------------
			UtilsConfig::SETTINGS_INTERNAL_TYPE_TROUBLESHOOTING => [
				'order' => 6,
				'labels' => [
					'title' => \__('Troubleshooting', 'eightshift-forms'),
					'desc' => \__('Settings for various troubleshooting and debugging options.', 'eightshift-forms'),
				],
			],
			SettingsCache::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsCache::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_TROUBLESHOOTING,
				'labels' => [
					'title' => \__('Cache', 'eightshift-forms'),
					'desc' => \__('Force data re-fetch for certain integrations.', 'eightshift-forms'),
				],
			],
			SettingsFallback::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsFallback::FILTER_SETTINGS_GLOBAL_NAME,
				'valid' => SettingsFallback::FILTER_SETTINGS_IS_VALID_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_TROUBLESHOOTING,
				'use' => SettingsFallback::SETTINGS_FALLBACK_USE_KEY,
				'labels' => [
					'title' => \__('Fallback e-mails', 'eightshift-forms'),
					'desc' => \__('Send a plain-text e-mail in case a form submission fails to complete.', 'eightshift-forms'),
				],
			],
			SettingsMigration::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsMigration::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_TROUBLESHOOTING,
				'use' => SettingsMigration::SETTINGS_MIGRATION_USE_KEY,
				'labels' => [
					'title' => \__('Migration', 'eightshift-forms'),
					'desc' => \__('One-click migrate forms from earlier versions of Forms.', 'eightshift-forms'),
				],
			],
			SettingsTransfer::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsTransfer::FILTER_SETTINGS_GLOBAL_NAME,
				'valid' => SettingsTransfer::FILTER_SETTINGS_IS_VALID_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_TROUBLESHOOTING,
				'use' => SettingsTransfer::SETTINGS_TRANSFER_USE_KEY,
				'labels' => [
					'title' => \__('Import/export', 'eightshift-forms'),
					'desc' => \__('Easily transfer forms and settings from one enviroment to another.', 'eightshift-forms'),
				],
			],
			SettingsDebug::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsDebug::FILTER_SETTINGS_GLOBAL_NAME,
				'valid' => SettingsDebug::FILTER_SETTINGS_IS_VALID_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_TROUBLESHOOTING,
				'use' => SettingsDebug::SETTINGS_DEBUG_USE_KEY,
				'labels' => [
					'title' => \__('Debug', 'eightshift-forms'),
					'desc' => \__('If a form is not working correctly, use these settings to solve problems more easily.', 'eightshift-forms'),
				],
			],
			SettingsDocumentation::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsDocumentation::FILTER_SETTINGS_GLOBAL_NAME,
				'type' => UtilsConfig::SETTINGS_INTERNAL_TYPE_TROUBLESHOOTING,
				'labels' => [
					'title' => \__('Documentation', 'eightshift-forms'),
					'desc' => \__('Need help? Interested in learning more? Find resources here.', 'eightshift-forms'),
				],
			],
		];

		// Populate icons form utils list.
		foreach ($data as $keyData => $valueData) {
			$data[$keyData]['labels'] = \array_merge(
				$valueData['labels'],
				[
					'icon' => UtilsHelper::getUtilsIcons($keyData),
				],
			);
		}

		// Populate additional items from filters, used for add-ons.
		$filterName = UtilsHooksHelper::getFilterName(['admin', 'settings', 'data']);

		if (\has_filter($filterName)) {
			foreach (\apply_filters($filterName, []) as $keyItem => $valueItem) {
				if (
					!isset($valueItem['labels']['title']) ||
					!isset($valueItem['labels']['desc']) ||
					!isset($valueItem['labels']['icon']) ||
					!isset($valueItem['type'])
				) {
					continue;
				}

				$data[$keyItem] = $valueItem;
			}
		}

		return $data;
	}
}
