<?php

/**
 * Class that holds data for admin forms settings.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Validation\SettingsValidation;
use EightshiftForms\Form\AbstractFormBuilder;

/**
 * SettingsAll class.
 */
class SettingsAll extends AbstractFormBuilder implements SettingsAllInterface
{

	/**
	 * All settings.
	 */
	public const SETTINGS = [
		SettingsGeneral::TYPE_KEY    => SettingsGeneral::FILTER_NAME,
		SettingsValidation::TYPE_KEY => SettingsValidation::FILTER_NAME,
		SettingsMailer::TYPE_KEY     => SettingsMailer::FILTER_NAME,
		SettingsMailchimp::TYPE_KEY  => SettingsMailchimp::FILTER_NAME,
		SettingsGreenhouse::TYPE_KEY => SettingsGreenhouse::FILTER_NAME,
		SettingsHubspot::TYPE_KEY    => SettingsHubspot::FILTER_NAME,
	];

	/**
	 * Get all settings sidebar array for building settings page.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsSidebar(string $formId, string $type): array
	{
		$output = [];

		if (!$formId) {
			return [];
		}

		// Loop all settings.
		foreach (self::SETTINGS as $key => $filter) {
			// Determin if there is a filter for settings page.
			if (!has_filter($filter)) {
				continue;
			}

			// Get filter data.
			$data = apply_filters($filter, $formId);

			// If empty array skip.
			if (!$data) {
				continue;
			}

			// Check sidebar value for type.
			$value = $data['sidebar']['value'] ?? '';

			// Populate sidebar data.
			$output[$value] = $data['sidebar'] ?? [];
		}

		// Return all settings data.
		return $output;
	}

	/**
	 * Get all settings array for building settings page.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type to show.
	 *
	 * @return string
	 */
	public function getSettingsForm(string $formId, string $type): string
	{
		// Bailout if form id is wrong or empty.
		if (empty($formId)) {
			return '';
		}

		// Check if type is set if not use general settings page.
		if (empty($type)) {
			$type = SettingsGeneral::TYPE_KEY;
		}

		// Fiund settings page.
		$filter = self::SETTINGS[$type] ?? '';

		// Determin if there is a filter for settings page.
		if (!has_filter($filter)) {
			return '';
		}

		// Get filter data.
		$data = apply_filters($filter, $formId);

		// Populate and build form.
		return $this->buildSettingsForm(
			$data['form'] ?? [],
			[
				'formPostId' => $formId
			]
		);
	}
}
