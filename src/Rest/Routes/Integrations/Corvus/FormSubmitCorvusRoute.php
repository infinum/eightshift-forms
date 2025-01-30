<?php

/**
 * The class register route for public form submiting endpoint - Corvus
 *
 * @package EightshiftForms\Rest\Route\Integrations\Corvus
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Corvus;

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Corvus\SettingsCorvus;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

/**
 * Class FormSubmitCorvusRoute
 */
class FormSubmitCorvusRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsCorvus::SETTINGS_TYPE_KEY;

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . UtilsConfig::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDetails)
	{
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];

		if (!\apply_filters(SettingsCorvus::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$this->labels->getLabel('corvusMissingConfig', $formId)
				)
			);
		}

		$mapParams = UtilsSettingsHelper::getSettingValueGroup(SettingsCorvus::SETTINGS_CORVUS_PARAMS_MAP_KEY, $formId);

		$params = $this->prepareParams($mapParams, $formDetails[UtilsConfig::FD_PARAMS], $formId);

		$reqParams = [
			'store_id',
			'amount',
			'language',
			'require_complete',
			'currency',
			'order_number',
			'cart',
		];

		$missingOrEmpty = \array_intersect_key(\array_flip(\array_filter($reqParams, fn($param) => empty($params[$param] ?? null))), $params);

		// Bail early if the required params are missing.
		if ($missingOrEmpty) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$this->labels->getLabel('corvusMissingReqParams', $formId)
				)
			);
		}

		// Bail early if the API key is missing.
		if (isset($params['store_id']) && empty(Variables::getApiKeyCorvus($params['store_id']))) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$this->labels->getLabel('corvusMissingReqParams', $formId)
				)
			);
		}

		// Set validation submit once.
		$this->validator->setValidationSubmitOnce($formId);

		// Located before the sendEmail mentod so we can utilize common email response tags.
		$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

		// Send email.
		$this->getFormSubmitMailer()->sendEmails(
			$formDetails,
			$this->getCommonEmailResponseTags(
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['private']
				),
				$formDetails
			)
		);

		// Finish.
		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				$this->labels->getLabel('corvusSuccess', $formId),
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['additional'],
					[
						UtilsHelper::getStateResponseOutputKey('processExternally') => [
							'type' => 'POST',
							'url' => $this->getUrl($formId),
							'params' => $this->setRealOrderNumber($params, $successAdditionalData, $formId),
						],
					]
				),
			)
		);
	}

	/**
	 * Prepare params for Corvus.
	 *
	 * @param array<string, string> $mapParams Map of params.
	 * @param array<string, string> $params Form params.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, string>
	 */
	private function prepareParams(array $mapParams, array $params, string $formId): array
	{
		$output = [];
		$storeId = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_STORE_ID, $formId);

		if (!$storeId) {
			return $output;
		}

		foreach ($mapParams as $key => $value) {
			$param = FormsHelper::getParamValue($value, $params);

			if (!$param) {
				continue;
			}

			switch ($key) {
				case 'amount':
					$output['amount'] = \number_format((float)$param, 2, '.', '');
					break;
				case 'subscription':
					$subscriptionValue = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_SUBSCRIPTION_VALUE_KEY, $formId) ?: 'true';
					$output['subscription'] = ($param === $subscriptionValue) ? 'true' : 'false';
					break;
				case 'iban':
					if (UtilsSettingsHelper::isSettingCheckboxChecked(SettingsCorvus::SETTINGS_CORVUS_IBAN_USE_KEY, SettingsCorvus::SETTINGS_CORVUS_IBAN_USE_KEY, $formId)) {
						$ibanValue = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_IBAN_VALUE_KEY, $formId) ?: 'true';
						$output['iban'] = $param === $ibanValue ? 'true' : 'false';
					}
					break;
				default:
					$output[$key] = $param;
					break;
			}
		}

		$output['store_id'] = $storeId;
		$output['version'] = '1.4'; // Corvus API version.
		$output['language'] = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_LANG_KEY, $formId);
		$output['require_complete'] = UtilsSettingsHelper::isSettingCheckboxChecked(SettingsCorvus::SETTINGS_CORVUS_REQ_COMPLETE_KEY, SettingsCorvus::SETTINGS_CORVUS_REQ_COMPLETE_KEY, $formId) ? 'true' : 'false';
		$output['currency'] = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_CURRENCY_KEY, $formId);
		$output['order_number'] = 'temp'; // Temp name, the real one will be set after the increment.
		$output['cart'] = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_CART_DESC_KEY, $formId);

		return $output;
	}

	/**
	 * Set real order number after the increment.
	 *
	 * @param array<string, string> $params Form params.
	 * @param array<string, string> $successAdditionalData Success additional data.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, string>
	 */
	private function setRealOrderNumber(array $params, array $successAdditionalData, string $formId): array
	{
		$orderId = FormsHelper::getIncrement($formId);

		if (UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_ENTRY_ID_USE_KEY, $formId) ?: '') {
			$entryId = $successAdditionalData['private'][UtilsHelper::getStateResponseOutputKey('entry')] ?? '';

			if ($entryId) {
				$orderId = $entryId;
			}
		}

		$params['order_number'] = $orderId;

		if (isset($params['iban']) && $params['iban'] === 'true') {
			$params['creditor_reference'] = "HR00{$orderId}";
			$params['hide_tabs'] = 'checkout';

			if (isset($params['subscription'])) {
				unset($params['subscription']);
			}
		}

		if (isset($params['iban'])) {
			unset($params['iban']);
		}

		// Set the correct order as it is req by Corvus.
		\ksort($params);

		// Add signature.
		$params['signature'] = \hash_hmac(
			'sha256',
			\array_reduce(\array_keys($params), fn($carry, $key) => $carry . $key . $params[$key], ''),
			UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyCorvus($params['store_id']), SettingsCorvus::SETTINGS_CORVUS_API_KEY_KEY)['value']
		);

		return $params;
	}

	/**
	 * Get merchant URL.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	private function getUrl(string $formId): string
	{
		$isTest = UtilsSettingsHelper::isSettingCheckboxChecked(SettingsCorvus::SETTINGS_CORVUS_IS_TEST, SettingsCorvus::SETTINGS_CORVUS_IS_TEST, $formId);

		return $isTest ? 'https://test-wallet.corvuspay.com/checkout/' : 'https://wallet.corvuspay.com/checkout/';
	}
}
