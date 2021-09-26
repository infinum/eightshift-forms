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
		SettingsGreenhouse::TYPE_KEY => SettingsGreenhouse::FILTER_NAME,
		SettingsHubspot::TYPE_KEY    => SettingsHubspot::FILTER_NAME,
		SettingsMailchimp::TYPE_KEY  => SettingsMailchimp::FILTER_NAME,
	];

	/**
	 * Get all settings array for building settings page.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsAll(string $formId, string $type): array
	{
		$output = [];

		// Loop all settings.
		foreach (self::SETTINGS as $key => $filter) {

			// Determin if there is a filter for settings page.
			if (!has_filter($filter)) {
				continue;
			}

			// Get filter data.
			$data = apply_filters($filter, $formId);

			// Check sidebar value for type.
			$value = $data['sidebar']['value'] ?? '';

			// Check required field settings page that always stays on the page.
			$isRequired = $data['isRequired'] ?? false;

			// Populate sidebar data.
			$output['sidebar'][$value] = $data['sidebar'] ?? [];

			// Populate and build form.
			$output['forms'][$value] = $this->buildSettingsForm(
				$data['form'] ?? [],
				[
					'formPostId' => $formId,
					'formSuccessRedirect' => $type === SettingsGeneral::TYPE_KEY,
				]
			);

			// Check if settings is set to be used if not hide options page.
			$isUsed = \get_post_meta($formId, $this->getSettingsName("{$key}Use"), true) ?? false;

			// Always leave required settings on the page.
			if (!$isUsed && !$isRequired) {
				unset($output['sidebar'][$value]);
			}
		}

		// If type key is empty use general settings.
		if (empty($type)) {
			$type = SettingsGeneral::TYPE_KEY;
		}

		// Return all settings data.
		return [
			'sidebar' => $output['sidebar'],
			'form' => $output['forms'][$type],
		];
	}
}
