<?php

/**
 * Class that holds data for integrations.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\Mailchimp\MailchimpMapper;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;

/**
 * Integrations class.
 */
class Integrations
{
	/**
	 * All settings.
	 */
	public const ALL_INTEGRATIONS = [
		SettingsMailchimp::TYPE_KEY => [
			'global' => SettingsMailchimp::FILTER_GLOBAL_NAME,
			'settings' => SettingsMailchimp::FILTER_NAME,
			'mapper' => MailchimpMapper::FILTER_MAPPER_NAME,
		],
		SettingsGreenhouse::TYPE_KEY => [
			'global' => SettingsGreenhouse::FILTER_GLOBAL_NAME,
			'settings' => SettingsGreenhouse::FILTER_NAME,
		],
		SettingsHubspot::TYPE_KEY => [
			'global' => SettingsHubspot::FILTER_GLOBAL_NAME,
			'settings' => SettingsHubspot::FILTER_NAME,
		],
	];
}
