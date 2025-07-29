<?php

/**
 * The class register route for public form submitting endpoint - Paycek
 *
 * @package EightshiftForms\Rest\Route\Integrations\Paycek
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Paycek;

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Paycek\SettingsPaycek;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Helpers\SettingsHelpers;

/**
 * Class FormSubmitPaycekRoute
 */
class FormSubmitPaycekRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsPaycek::SETTINGS_TYPE_KEY;

	/**
	 * Get the base URL of the route.
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . Config::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
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
		$formId = $formDetails[Config::FD_FORM_ID];

		if (!\apply_filters(SettingsPaycek::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					$this->labels->getLabel('paycekMissingConfig', $formId)
				)
			);
		}

		$mapParams = SettingsHelpers::getSettingValueGroup(SettingsPaycek::SETTINGS_PAYCEK_PARAMS_MAP_KEY, $formId);

		$params = $this->prepareParams($mapParams, $formDetails[Config::FD_PARAMS], $formId);

		$reqParams = [
			'profileCode',
			'secretKey',
			'paymentId',
			'amount',
		];

		$missingOrEmpty = \array_intersect_key(\array_flip(\array_filter($reqParams, fn($param) => empty($params[$param] ?? null))), $params);

		if ($missingOrEmpty) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					$this->labels->getLabel('paycekMissingReqParams', $formId)
				)
			);
		}

		// Set validation submit once.
		$this->getValidator()->setValidationSubmitOnce($formId);

		// Located before the sendEmail method so we can utilize common email response tags.
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

		$params = $this->setRealOrderNumber($params, $successAdditionalData, $formId);

		// Finish.
		return \rest_ensure_response(
			ApiHelpers::getApiSuccessPublicOutput(
				$this->labels->getLabel('paycekSuccess', $formId),
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['additional'],
					[
						UtilsHelper::getStateResponseOutputKey('processExternally') => [
							'type' => 'GET',
							'url' => $this->generatePaymentUrl(
								$params['profileCode'],
								$params['secretKey'],
								$params['paymentId'],
								$params['amount'],
								$params['email'],
								$params['description'],
								$params['language'],
								$params['urlSuccess'],
								$params['urlFail'],
								$params['urlCancel']
							),
						],
					]
				),
			)
		);
	}

	/**
	 * Prepare params for Paycek.
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

		foreach ($mapParams as $key => $value) {
			$param = FormsHelper::getParamValue($value, $params);

			if (!$param) {
				continue;
			}

			switch ($key) {
				case 'amount':
					$param = \number_format((float)$param, 2, '.', '');
					break;
			}

			$output[$key] = $param;
		}

		$output['secretKey'] = SettingsHelpers::getOptionWithConstant(Variables::getApiKeyPaycek(), SettingsPaycek::SETTINGS_PAYCEK_API_KEY_KEY);
		$output['profileCode'] = SettingsHelpers::getOptionWithConstant(Variables::getApiProfileKeyPaycek(), SettingsPaycek::SETTINGS_PAYCEK_API_PROFILE_KEY);
		$output['language'] = SettingsHelpers::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_LANG_KEY, $formId);
		$output['paymentId'] = 'temp'; // Temp name, the real one will be set after the increment.
		$output['description'] = SettingsHelpers::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_CART_DESC_KEY, $formId);

		return $output;
	}

	/**
	 * Set real order number after the increment.
	 *
	 * @param array<string, string> $params Form params.
	 * @param array<string, string> $successAdditionalData Additional data.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, string>
	 */
	private function setRealOrderNumber(array $params, array $successAdditionalData, string $formId): array
	{
		$orderId = FormsHelper::getIncrement($formId);

		if (SettingsHelpers::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_ENTRY_ID_USE_KEY, $formId) ?: '') {
			$entryId = $successAdditionalData['private'][UtilsHelper::getStateResponseOutputKey('entry')] ?? '';

			if ($entryId) {
				$orderId = $entryId;
			}
		}

		$params['paymentId'] = $orderId;

		$params['urlSuccess'] = $this->getCallbackUrl($formId, $orderId, SettingsHelpers::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_URL_SUCCESS, $formId));
		$params['urlFail'] = $this->getCallbackUrl($formId, $orderId, SettingsHelpers::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_URL_FAIL, $formId));
		$params['urlCancel'] = $this->getCallbackUrl($formId, $orderId, SettingsHelpers::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_URL_CANCEL, $formId));

		// Set the correct order as it is req by Paycek.
		\ksort($params);

		return $params;
	}

	/**
	 * Generate payment URL for Paycek.
	 *
	 * @param string $data Data to encode.
	 *
	 * @return string
	 */
	private function base64urlEncode(string $data): string
	{
		return \rtrim(\strtr(\base64_encode($data), '+/', '-_'), '='); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Generate payment URL for Paycek.
	 *
	 * @param string $profileCode can be found in profile settings (https://paycek.io).
	 * @param string $secretKey can be found in profile settings (https://paycek.io).
	 * @param string $paymentId unique payment ID (ID that you are using on your website to uniquely describe the purchase).
	 * @param string $totalAmount total price (example "100.00").
	 * @param string $email email of your customer.
	 * @param string $description payment description (max length 100 characters).
	 * @param string $language in which the payment will be shown to the customer ('en', 'hr').
	 * @param string $successUrl URL of a web page to go to after a successful payment.
	 * @param string $failUrl URL of a web page to go to after a failed payment.
	 * @param string $backUrl URL for client to go to if he wants to get back to your shop.
	 *
	 * @return string URL for starting a payment process on https://paycek.io
	 */
	private function generatePaymentUrl(
		string $profileCode,
		string $secretKey,
		string $paymentId,
		string $totalAmount,
		$email = "",
		$description = "",
		$language = "",
		$successUrl = "",
		$failUrl = "",
		$backUrl = "",
	): string {
		$data = [
			'p' => $totalAmount,
			'id' => $paymentId,
			'e' => $email,
			's' => $successUrl,
			'f' => $failUrl,
			'b' => $backUrl,
			'd' => $description,
			'l' => $language
		];

		$dataJson = \wp_json_encode($data);
		$dataBase64 = $this->base64urlEncode($dataJson);

		return \add_query_arg(
			[
				'd' => $dataBase64,
				'c' => $profileCode,
				'h' => $this->base64urlEncode(\hex2bin(\hash("sha256", $dataBase64 . "\x00" . $profileCode . "\x00" . $secretKey)))
			],
			'https://paycek.io/processing/checkout/payment_create'
		);
	}

	/**
	 * Get callback URL.
	 *
	 * @param string $formId Form ID.
	 * @param string $orderId Order ID.
	 * @param string $url URL.
	 *
	 * @return string
	 */
	private function getCallbackUrl(string $formId, string $orderId, string $url = ''): string
	{
		if (!$url) {
			return '';
		}

		return \add_query_arg(
			[
				'formId' => $formId,
				'orderId' => $orderId,
			],
			$url
		);
	}
}
