<?php

/**
 * Moments Abstract class for shared functionality.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

/**
 * AbstractMoments  class.
 */
abstract class AbstractMoments
{
	/**
	 * Set headers used for fetching data.
	 *
	 * @return array<string, mixed>
	 */
	protected function getHeaders(): array
	{
		return [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => "App {$this->getApiKey()}",
		];
	}

	/**
	 * Return Moments base url.
	 *
	 * @return string
	 */
	protected function getBaseUrl(): string
	{
		$url = \rtrim($this->getApiUrl(), '/');

		return "{$url}/";
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	protected function getApiKey(): string
	{
		return UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyMoments(), SettingsMoments::SETTINGS_MOMENTS_API_KEY_KEY)['value'];
	}

	/**
	 * Return Api Url from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiUrl(): string
	{
		return UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiUrlMoments(), SettingsMoments::SETTINGS_MOMENTS_API_URL_KEY)['value'];
	}
}
