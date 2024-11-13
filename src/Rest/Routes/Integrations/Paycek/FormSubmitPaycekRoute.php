<?php

/**
 * The class register route for public form submiting endpoint - Paycek
 *
 * @package EightshiftForms\Rest\Route\Integrations\Paycek
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Paycek;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Paycek\SettingsPaycek;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

/**
 * Class FormSubmitPaycekRoute
 */
class FormSubmitPaycekRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsPaycek::SETTINGS_TYPE_KEY;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
	}

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

		if (!\apply_filters(SettingsPaycek::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$this->labels->getLabel('paycekMissingConfig', $formId)
				)
			);
		}

		$mapParams = UtilsSettingsHelper::getSettingValueGroup(SettingsPaycek::SETTINGS_PAYCEK_PARAMS_MAP_KEY, $formId);

		$params = $this->prepareParams($mapParams, $formDetails['paramsRaw'], $formId);

		$reqParams = [
			'profileCode',
			'secretKey',
			'paymentId',
			'amount',
		];

		$missingOrEmpty = \array_filter($reqParams, fn($param) => empty($params[$param] ?? null));

		if ($missingOrEmpty) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$this->labels->getLabel('paycekMissingReqParams', $formId)
				)
			);
		}

		// Finish.
		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				$this->labels->getLabel('paycekSuccess', $formId),
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
			$param = $params[$value] ?? '';
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

		$output['secretKey'] = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyPaycek(), SettingsPaycek::SETTINGS_PAYCEK_API_KEY_KEY)['value'];
		$output['profileCode'] = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiProfileKeyPaycek(), SettingsPaycek::SETTINGS_PAYCEK_API_PROFILE_KEY)['value'];
		$output['language'] = UtilsSettingsHelper::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_LANG_KEY, $formId);
		$output['paymentId'] = 'order_' . \time();
		$output['description'] = UtilsSettingsHelper::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_CART_DESC_KEY, $formId);
		$output['urlSuccess'] = UtilsSettingsHelper::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_URL_SUCCESS, $formId);
		$output['urlFail'] = UtilsSettingsHelper::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_URL_FAIL, $formId);
		$output['urlCancel'] = UtilsSettingsHelper::getSettingValue(SettingsPaycek::SETTINGS_PAYCEK_URL_CANCEL, $formId);

		\ksort($output);

		return $output;
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
	 * @param string $paymentId unique payment id (id that you are using on your website to uniquely describe the purchase).
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
}
