<?php

/**
 * Class that holds data for global forms settings.
 *
 * @package EightshiftForms\Settings\GlobalSettings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\GlobalSettings;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\GlobalSettings\SettingsGlobalInterface;

/**
 * SettingsGlobal class.
 */
class SettingsGlobal extends AbstractFormBuilder implements SettingsGlobalInterface
{

	/**
	 * All settings.
	 */
	public const SETTINGS = [
		SettingsGeneral::TYPE_KEY    => SettingsGeneral::FILTER_GLOBAL_NAME,
		SettingsMailchimp::TYPE_KEY  => SettingsMailchimp::FILTER_GLOBAL_NAME,
	];

	/**
	 * Get all settings array for building settings page.
	 *
	 * @param string $type Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsGlobal(string $type): array
	{
		$output = [];

		// Loop all settings.
		foreach (self::SETTINGS as $key => $filter) {
			// Determin if there is a filter for settings page.
			if (!has_filter($filter)) {
				continue;
			}

			// Get filter data.
			$data = apply_filters($filter, '');

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
					'formSuccessRedirect' => $type === SettingsGeneral::TYPE_KEY,
				]
			);

			// Check if settings is set to be used if not hide options page.
			$isUsed = \get_option($this->getSettingsName("{$value}Use"), true) ?? false;

			// Always leave required settings on the page.
			if (!$isUsed && !$isRequired) {
				unset($output['sidebar'][$value]);
				unset($output['forms'][$value]);
			}
		}

		// Return all settings data.
		return [
			'active' => isset($output['forms'][$type]) ? $type : SettingsGeneral::TYPE_KEY,
			'sidebar' => $output['sidebar'],
			'form' => $output['forms'][$type] ?? $output['forms'][SettingsGeneral::TYPE_KEY],
		];
	}
}
