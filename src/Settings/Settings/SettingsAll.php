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
	 * Set all settings.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsAll(string $formId, string $type): array
	{
		$output = [];

		foreach (self::SETTINGS as $key => $filter) {
			if (!has_filter($filter)) {
				continue;
			}

			$data = apply_filters($filter, $formId);
			$value = $data['sidebar']['value'] ?? '';
			$isRequired = $data['isRequired'] ?? false;

			$output['sidebar'][$value] = $data['sidebar'] ?? [];
			$output['forms'][$value] = $this->buildForm(
				$data['form'] ?? [],
				$formId,
				$type === SettingsGeneral::TYPE_KEY
			);

			$isUsed = \get_post_meta($formId, $this->getSettingsName("{$key}Use"), true) ?? false;

			if (!$isUsed && !$isRequired) {
				unset($output['sidebar'][$value]);
			}
		}

		if (empty($type)) {
			$type = SettingsGeneral::TYPE_KEY;
		}
		// var_dump($output);

		return [
			'sidebar' => $output['sidebar'],
			'form' => $output['forms'][$type],
		];
	}
}
