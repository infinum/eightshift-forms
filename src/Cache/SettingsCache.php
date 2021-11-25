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
		],
		'greenhouse' => [
			GreenhouseClient::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME,
			GreenhouseClient::CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME,
		],
		'hubspot' => [
			HubspotClient::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME,
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
			'label' => __('Cache', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><g fill-rule="nonzero" fill="none"><path fill="#AF7AAE" d="M27 25.737H9.316L8.053 11.211h20.21z"/><path fill="#8E5B8E" d="M28.263 9.947v1.264l-.11 1.263H8.162l-.109-1.263V9.947z"/><path d="M29.526 8.684v1.263c0 .698-.565 1.264-1.263 1.264H8.053a1.263 1.263 0 0 1-1.264-1.264V8.684c0-.697.566-1.263 1.264-1.263h20.21c.698 0 1.263.566 1.263 1.263Z" fill="#D199D1"/><path d="M27.632 25.105A1.895 1.895 0 0 1 25.737 27h-3.898a1.895 1.895 0 0 1-1.787 2.526H2.369a1.895 1.895 0 1 1 0-3.79h3.898a1.895 1.895 0 0 1 1.786-2.526h17.685c1.046 0 1.895.849 1.895 1.895ZM22.454 9.31c.278.027.537-.038.757-.165v2.667c0 .685.532 1.268 1.217 1.292.718.026 1.309-.549 1.309-1.262V7.421a.632.632 0 0 0-.632-.632h-3.158a.632.632 0 0 0-.631.632v.603c0 .655.487 1.222 1.138 1.286Z" fill="#A7E5CB"/><path d="M10.579 25.105a.632.632 0 0 1-.632.632H8.053a.632.632 0 0 1 0-1.263h1.894c.35 0 .632.282.632.631ZM7.42 27H2.368a.632.632 0 0 0 0 1.263h5.053a.632.632 0 0 0 0-1.263Z" fill="#D7F3FF"/><path d="M20.684 21.947h3.79A5.053 5.053 0 0 1 29.526 27v1.895a.632.632 0 0 1-.631.631h-12a5.053 5.053 0 0 1-5.053-5.052v-4.421c0-.698.566-1.264 1.263-1.264h5.053c.698 0 1.263.566 1.263 1.264v.631c0 .698.566 1.263 1.263 1.263Z" fill="#A89B80"/><path d="M29.526 27v2.526H19.421a5.053 5.053 0 0 1-5.053-5.052v-4.421c0-.698.566-1.264 1.264-1.264h2.526c.698 0 1.263.566 1.263 1.264v.631c0 .698.566 1.263 1.263 1.263h3.79A5.053 5.053 0 0 1 29.526 27Z" fill="#C1B291"/><path d="M29.526 27v2.526h-7.579a5.053 5.053 0 0 1-5.052-5.052v-2.527A5.053 5.053 0 0 0 21.947 27h7.58Z" fill="#A89B80"/><path d="M28.849 24.474h-6.902a5.053 5.053 0 0 1-5.052-5.053v-.632h1.263c.697 0 1.263.566 1.263 1.264v.631c0 .698.566 1.263 1.263 1.263h3.79a5.05 5.05 0 0 1 4.375 2.527Z" fill="#DBCBA1"/><path d="M16.895 1.737v15.158h-2.527V1.737a1.263 1.263 0 1 1 2.527 0Z" fill="#91F18B"/><path d="M15.632 1.737v15.158h-1.264V1.737A1.263 1.263 0 0 1 16.263.644a1.262 1.262 0 0 0-.631 1.093Z" fill="#6BAF6E"/><path d="M20.053 18.158c0 .698-.566 1.263-1.264 1.263h-6.315a1.263 1.263 0 1 1 0-2.526h1.263v-.632c0-.349.283-.631.631-.631h2.527c.349 0 .631.282.631.631v.632h1.263c.698 0 1.264.565 1.264 1.263Z" fill="#FFC248"/><path d="M20.053 18.158H11.21c0-.698.565-1.263 1.263-1.263h6.315c.698 0 1.264.565 1.264 1.263Z" fill="#FFFF63"/><path d="M12.947 29.526a.474.474 0 0 1-.473.474H2.368A2.371 2.371 0 0 1 0 27.632a2.371 2.371 0 0 1 2.368-2.369h3.18a.126.126 0 0 0 .092-.038.155.155 0 0 0 .044-.111v-.009a2.371 2.371 0 0 1 2.369-2.368h1.894a.474.474 0 1 1 0 .947H8.053c-.784 0-1.421.638-1.421 1.421v.007c0 .295-.113.573-.32.782a1.07 1.07 0 0 1-.764.317h-3.18c-.783 0-1.42.637-1.42 1.42 0 .784.637 1.422 1.42 1.422h10.106c.261 0 .473.212.473.473Zm8.369-17.842a.474.474 0 1 0 0-.947h-2.527a.474.474 0 1 0 0 .947h2.527Zm7.954 12.574c.464.808.73 1.745.73 2.742v1.895c0 .61-.496 1.105-1.105 1.105h-12a5.533 5.533 0 0 1-5.527-5.526v-4.421c0-.17.025-.338.073-.5a1.736 1.736 0 0 1 1.033-3.132h.79v-.158c0-.44.258-.82.63-.999v-3.58H8.57l.834 9.59a.474.474 0 1 1-.944.083l-.846-9.73a1.74 1.74 0 0 1-1.297-1.68V8.684c0-.958.779-1.737 1.737-1.737h5.842v-5.21c0-.958.779-1.737 1.737-1.737.957 0 1.736.78 1.736 1.737v13.528c.374.177.632.558.632.998v.158h.79a1.736 1.736 0 0 1 1.032 3.133c.048.161.073.33.073.499v.631c0 .436.354.79.79.79h3.789a5.53 5.53 0 0 1 4.796 2.784Zm-14.428-9.1h1.579V1.737a.79.79 0 0 0-1.579 0v13.42Zm-.947-4.421V7.895H8.053a.79.79 0 0 0-.79.79v1.262a.79.79 0 0 0 .795.79h5.837Zm15.158 16.737h-7.106a5.531 5.531 0 0 1-2.677-.691.474.474 0 1 1 .46-.829 4.581 4.581 0 0 0 2.217.572h7.081a4.543 4.543 0 0 0-.462-1.578h-6.619a5.532 5.532 0 0 1-2.678-.692.474.474 0 1 1 .46-.828 4.582 4.582 0 0 0 2.218.572h5.983a4.57 4.57 0 0 0-3.456-1.579h-3.79c-.958 0-1.737-.779-1.737-1.736v-.632a.792.792 0 0 0-.017-.164 1.724 1.724 0 0 1-.14.006h-.632a.474.474 0 1 1 0-.947h.631a.79.79 0 0 0 0-1.58h-1.263a.474.474 0 0 1-.473-.473v-.632a.158.158 0 0 0-.158-.158h-2.527a.158.158 0 0 0-.158.158v.632a.474.474 0 0 1-.473.474h-1.263a.79.79 0 0 0 0 1.579h3.79a.474.474 0 1 1 0 .947h-3.79c-.048 0-.095-.002-.141-.006a.796.796 0 0 0-.017.164v4.42a4.586 4.586 0 0 0 3.952 4.537 5.525 5.525 0 0 1-2.373-4.536v-3.158a.474.474 0 1 1 .947 0v3.158a4.586 4.586 0 0 0 3.952 4.536 5.524 5.524 0 0 1-2.373-4.536v-3.158a.474.474 0 1 1 .947 0v3.158a4.584 4.584 0 0 0 4.58 4.579h6.947c.087 0 .157-.071.157-.158v-1.421ZM30 8.684v1.263a1.74 1.74 0 0 1-1.298 1.681l-.846 9.729a.474.474 0 0 1-.943-.082l.834-9.59H26.21v.157c0 .476-.189.92-.531 1.25a1.73 1.73 0 0 1-1.269.486c-.939-.034-1.674-.81-1.674-1.766v-2.03c-.108.01-.218.01-.328 0-.893-.088-1.567-.843-1.567-1.758v-.13H18.79a.474.474 0 1 1 0-.946h2.159c.178-.374.558-.632.998-.632h3.158c.44 0 .821.258.999.632h2.16c.957 0 1.736.779 1.736 1.736Zm-4.737-1.263a.158.158 0 0 0-.158-.158h-3.158a.158.158 0 0 0-.158.158v.603c0 .418.313.776.711.814a.786.786 0 0 0 .473-.102.474.474 0 0 1 .711.41v2.666c0 .437.341.804.76.82a.79.79 0 0 0 .82-.79V7.421Zm3.79 1.263a.79.79 0 0 0-.79-.79h-2.052v2.843h2.056a.79.79 0 0 0 .786-.79V8.684Z" fill="#000"/></g></svg>',
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
				'introTitle' => __('Cache settings', 'eightshift-forms'),
				'introSubtitle' => __('Cache settings in one place.', 'eightshift-forms'),
			]
		];

		$manifestForm = Components::getManifest(dirname(__DIR__, 1) . '/Blocks/components/form');

		foreach (self::ALL_CACHE as $key => $value) {
			$output[] = [
				'component' => 'submit',
				'submitFieldWidthLarge' => 6,
				'submitValue' => "Delete " . ucfirst($key) . ' cache',
				'submitAttrs' => [
					'data-type' => $key,
				],
				'additionalClass' => $manifestForm['componentCacheJsClass'],
			];
		};

		return $output;
	}
}
