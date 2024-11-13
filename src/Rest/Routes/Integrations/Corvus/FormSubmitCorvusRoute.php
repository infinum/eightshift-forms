<?php

/**
 * The class register route for public form submiting endpoint - Corvus
 *
 * @package EightshiftForms\Rest\Route\Integrations\Corvus
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Corvus;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Corvus\SettingsCorvus;
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
 * Class FormSubmitCorvusRoute
 */
class FormSubmitCorvusRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsCorvus::SETTINGS_TYPE_KEY;

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

		if (!\apply_filters(SettingsCorvus::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$this->labels->getLabel('corvusMissingConfig', $formId)
				)
			);
		}

		$mapParams = UtilsSettingsHelper::getSettingValueGroup(SettingsCorvus::SETTINGS_CORVUS_PARAMS_MAP_KEY, $formId);

		$params = $this->prepareParams($mapParams, $formDetails['paramsRaw'], $formId);

		$reqParams = [
			'store_id',
			'amount',
			'language',
			'require_complete',
			'currency',
			'order_number',
			'cart',
		];

		$missingOrEmpty = \array_filter($reqParams, fn($param) => empty($params[$param] ?? null));

		if ($missingOrEmpty) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$this->labels->getLabel('corvusMissingReqParams', $formId)
				)
			);
		}

		// Add signature.
		$params['signature'] = \hash_hmac(
			'sha256',
			\array_reduce(\array_keys($params), fn($carry, $key) => $carry . $key . $params[$key], ''),
			UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyCorvus(), SettingsCorvus::SETTINGS_CORVUS_API_KEY_KEY)['value']
		);


		// Finish.
		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				$this->labels->getLabel('corvusSuccess', $formId),
				[
					UtilsHelper::getStateResponseOutputKey('postExternallyData') => [
						'url' => $this->getUrl($formId),
						'params' => $params,
					],
				]
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

		$output['store_id'] = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_STORE_ID, $formId);
		$output['version'] = '1.4'; // Corvus API version.
		$output['language'] = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_LANG_KEY, $formId);
		$output['require_complete'] = (string) UtilsSettingsHelper::isSettingCheckboxChecked(SettingsCorvus::SETTINGS_CORVUS_REQ_COMPLETE_KEY, SettingsCorvus::SETTINGS_CORVUS_REQ_COMPLETE_KEY, $formId);
		$output['currency'] = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_CURRENCY_KEY, $formId);
		$output['order_number'] = 'order_' . \time();
		$output['cart'] = UtilsSettingsHelper::getSettingValue(SettingsCorvus::SETTINGS_CORVUS_CART_DESC_KEY, $formId);

		\ksort($output);

		return $output;
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
