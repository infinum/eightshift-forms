<?php

/**
 * Cache Settings class.
 *
 * @package EightshiftForms\Cache
 */

declare(strict_types=1);

namespace EightshiftForms\Cache;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Integrations\Greenhouse\GreenhouseClient;
use EightshiftForms\Integrations\Hubspot\HubspotClient;
use EightshiftForms\Integrations\Mailchimp\MailchimpClient;
use EightshiftForms\Integrations\Mailerlite\MailerliteClient;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Settings\GlobalSettings\SettingsGlobalDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCache class.
 */
class SettingsCache implements SettingsGlobalDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_cache';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_cache';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'cache';

	/**
	 * List all cache options in the project.
	 */
	public const ALL_CACHE = [
		'mailchimp' => [
			MailchimpClient::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME,
			MailchimpClient::CACHE_MAILCHIMP_ITEM_TRANSIENT_NAME,
			MailchimpClient::CACHE_MAILCHIMP_ITEM_TAGS_TRANSIENT_NAME,
		],
		'greenhouse' => [
			GreenhouseClient::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME,
			GreenhouseClient::CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME,
		],
		'hubspot' => [
			HubspotClient::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME,
			HubspotClient::CACHE_HUBSPOT_CONTACT_PROPERTIES_TRANSIENT_NAME,
		],
		'mailerlite' => [
			MailerliteClient::CACHE_MAILERLITE_ITEMS_TRANSIENT_NAME,
			MailerliteClient::CACHE_MAILERLITE_ITEM_TRANSIENT_NAME,
		],
	];

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Clear cache', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.5 2.778v7.11c0 .983 1.79 1.779 4 1.779.45 0 .883-.033 1.286-.094M1.5 2.778c0 .982 1.79 1.778 4 1.778s4-.796 4-1.778m-8 0C1.5 1.796 3.29 1 5.5 1s4 .796 4 1.778m0 0V9m-8-2.667c0 .982 1.79 1.778 4 1.778s4-.796 4-1.778" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M10.24 10.877a.75.75 0 1 0-1.48.246l1.48-.246zM10.833 19l-.74.123a.75.75 0 0 0 .74.627V19zm5.334 0v.75a.75.75 0 0 0 .74-.627l-.74-.123zm2.073-7.877a.75.75 0 1 0-1.48-.246l1.48.246zM8.5 10.25a.75.75 0 0 0 0 1.5v-1.5zm10 1.5a.75.75 0 0 0 0-1.5v1.5zM10.75 11a.75.75 0 0 0 1.5 0h-1.5zm4 0a.75.75 0 0 0 1.5 0h-1.5zm-1.5 2a.75.75 0 0 0-1.5 0h1.5zm-1.5 4a.75.75 0 0 0 1.5 0h-1.5zm3.5-4a.75.75 0 0 0-1.5 0h1.5zm-1.5 4a.75.75 0 0 0 1.5 0h-1.5zm-4.99-5.877 1.334 8 1.48-.246-1.334-8-1.48.246zm2.073 8.627h5.334v-1.5h-5.334v1.5zm6.074-.627 1.333-8-1.48-.246-1.333 8 1.48.246zM8.5 11.75h10v-1.5h-10v1.5zm3.75-.75c0-.69.56-1.25 1.25-1.25v-1.5A2.75 2.75 0 0 0 10.75 11h1.5zm1.25-1.25c.69 0 1.25.56 1.25 1.25h1.5a2.75 2.75 0 0 0-2.75-2.75v1.5zM11.75 13v4h1.5v-4h-1.5zm2 0v4h1.5v-4h-1.5z" fill="#29A3A3"/></svg>',
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Clear cache', 'eightshift-forms'),
				'introSubtitle' => __('Use the buttons below to clear the cache if the entry you\'re looking for isn\'t available or has changed.', 'eightshift-forms'),
			]
		];

		$manifestForm = Components::getManifest(dirname(__DIR__, 1) . '/Blocks/components/form');

		foreach (self::ALL_CACHE as $key => $value) {
			$output[] = [
				'component' => 'submit',
				'submitFieldWidthLarge' => 2,
				'submitValue' => "Clear " . ucfirst($key) . ' cache',
				'submitIcon' => $key,
				'submitAttrs' => [
					'data-type' => $key,
				],
				'additionalClass' => $manifestForm['componentCacheJsClass'] . ' es-submit--cache-clear',
			];
		};

		return $output;
	}
}
