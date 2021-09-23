<?php

/**
 * Class that holds all settings for form.
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
	 * Set all settings.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsAll(string $formId, string $type): array
	{
		$filters = [
			SettingsGeneral::FILTER_NAME,
			SettingsValidation::FILTER_NAME,
			SettingsMailer::FILTER_NAME,
			SettingsGreenhouse::FILTER_NAME,
			SettingsHubspot::FILTER_NAME,
			SettingsMailchimp::FILTER_NAME,
		];

		$output = [];

		foreach ($filters as $filter) {
			if (!has_filter($filter)) {
				continue;
			}

			$data = apply_filters($filter, $formId);
			$value = $data['sidebar']['value'] ?? '';

			$output['sidebar'][] = $data['sidebar'];
			$output['forms'][$value] = $this->buildForm(
				$data['form'],
				$formId,
				$type === SettingsGeneral::TYPE_KEY
			);
		}

		return [
			'sidebar' => $output['sidebar'],
			'form' => $output['forms'][$type],
		];
	}
}
