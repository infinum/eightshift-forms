<?php

/**
 * Mapper integration class.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * abstract Mapper integration class.
 */
abstract class AbstractMapper implements ServiceInterface
{
	/**
	 * Get remote form by url
	 *
	 * @param string $url Url to check.
	 *
	 * @return string
	 */
	public function getRmoteForm(string $url)
	{
		$form = wp_remote_get($url);

		return $form;
	}
}
