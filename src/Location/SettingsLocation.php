<?php

/**
 * Location Settings class.
 *
 * @package EightshiftForms\Location
 */

declare(strict_types=1);

namespace EightshiftForms\Location;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Location\SettingsLocationInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsLocation class.
 */
class SettingsLocation implements SettingInterface, ServiceInterface, SettingsLocationInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_location';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'location';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		$locations = $this->getBlockLocations($formId);

		if (!$locations) {
			return [
				$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
				[
					'component' => 'layout',
					'layoutType' => 'layout-v-stack-card',
					'layoutContent' => [
						[
							'component' => 'highlighted-content',
							'highlightedContentTitle' => \__('The form is not used anywhere', 'eightshift-forms'),
							'highlightedContentIcon' => 'emptyStateLocations',
						],
					],
				],
			];
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'admin-listing',
				'adminListingForms' => $this->getBlockLocations($formId),
				'adminListingType' => 'locations',
			],
		];
	}

	/**
	 * Return all posts where form is assigned.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, mixed>
	 */
	public function getBlockLocations(string $formId): array
	{
		global $wpdb;

		$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT ID, post_type, post_title, post_status
				 FROM $wpdb->posts
				 WHERE post_content
				 LIKE %s
				 AND (post_status='publish' OR post_status='draft')
				",
				"%\"formsFormPostId\":\"{$formId}\"%"
			)
		);

		if (!$items) {
			return [];
		}

		$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		return \array_map(
			function ($item) use ($isDeveloperMode) {
				$id = $item->ID;
				$title = $item->post_title; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
				$title = $isDeveloperMode ? "{$id} - {$title}" : $title;

				return [
					'id' => $id,
					'postType' => $item->post_type, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
					'title' => $title,
					'status' => $item->post_status, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
					'editLink' => Helper::getFormEditPageUrl((string) $id),
					'viewLink' => \get_permalink($id),
					'activeIntegration' => [
						'isActive' => true,
						'isValid' => true,
						'isApiValid' => true,
					]
				];
			},
			$items
		);
	}
}
