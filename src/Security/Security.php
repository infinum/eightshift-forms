<?php

/**
 * Security class.
 *
 * @package EightshiftForms\Security
 */

declare(strict_types=1);

namespace EightshiftForms\Security;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;

/**
 * Security class.
 */
class Security implements SecurityInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	public function isRequestValid(): bool
	{
		$data = $this->getOptionValue(SettingsSecurity::SETTINGS_SECURITY_DATA_KEY) ?? [];
		$ip = Helper::getIpAddress();

		error_log( print_r( ( $data ), true ) );

		if (isset($data[$ip])) {
			$data[$ip] = $data[$ip] + 1;
		}
		
		error_log( print_r( ( Helper::getIpAddress() ), true ) );
		
		return false;
	}
}
