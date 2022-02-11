<?php

/**
 * Location Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsLocation class.
 */
class SettingsLocation implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_location';

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
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Display locations', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity=".3" d="M7.5 11.75H12m-4.5 3H11m-6.5-6h5" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle opacity=".3" cx="5" cy="11.75" r="1" fill="#29A3A3"/><circle opacity=".3" cx="5" cy="14.75" r="1" fill="#29A3A3"/><path d="M19 14.125c0 2.273-2.5 4.773-2.5 4.773s-2.5-2.5-2.5-4.773a2.5 2.5 0 0 1 5 0z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="16.5" cy="14.125" r=".682" fill="#29A3A3"/><path opacity=".2" fill="#29A3A3" d="M1 1h18v5H1z"/><path d="M19 10V2.5A1.5 1.5 0 0 0 17.5 1h-15A1.5 1.5 0 0 0 1 2.5v15A1.5 1.5 0 0 0 2.5 19H13M4.5 3.75h8" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		];
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
		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Display locations', 'eightshift-forms'),
				'introSubtitle' => __('See where your form appears throughout the website.
					<br /> <br />
					<strong>Note</strong>
					<br />
					The list might take a while to populate if the form is used in a lot of places.', 'eightshift-forms'),
			],
		];

		$locations = $this->getBlockLocations($formId);

		if (!$locations) {
			$output[] = [
				'component' => 'highlighted-content',
				'highlightedContentTitle' => __('Nothing to see here...', 'eightshift-forms'),
				'highlightedContentSubtitle' => __('The form isn\'t used anywhere on this website.', 'eightshift-forms'),
				'highlightedContentIcon' => 'empty',
			];
		}

		$output[] = [
			'component' => 'admin-listing',
			'adminListingForms' => $this->getBlockLocations($formId),
		];

		return $output;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		return [];
	}

	/**
	 * Return all posts where form is assigned.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, mixed>
	 */
	private function getBlockLocations(string $formId): array
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

		return array_map(
			static function ($item) {
				return [
					'id' => $item->ID,
					'postType' => $item->post_type, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
					'title' => $item->post_title, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
					'status' => $item->post_status, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
					'editLink' => Helper::getFormEditPageUrl((string) $item->ID),
					'viewLink' => get_permalink($item->ID),
				];
			},
			$items
		);
	}
}
