<?php

/**
 * The class register route for Base endpoint used on all forms.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Config\Config;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use EightshiftFormsVendor\EightshiftLibs\Rest\CallableRouteInterface;

/**
 * Class FormSubmitRoute
 *
 * @property \EightshiftForms\Validation\Validator $validator
 */
abstract class AbstractBaseRoute extends AbstractRoute implements CallableRouteInterface
{
	/**
	 * Method that returns project Route namespace.
	 *
	 * @return string Project namespace EightshiftFormsVendor\for REST route.
	 */
	protected function getNamespace(): string
	{
		return Config::getProjectRoutesNamespace();
	}

	/**
	 * Method that returns project route version.
	 *
	 * @return string Route version as a string.
	 */
	protected function getVersion(): string
	{
		return Config::getProjectRoutesVersion();
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::CREATABLE;
	}

	/**
	 * By default allow public access to route.
	 *
	 * @return bool
	 */
	public function permissionCallback(): bool
	{
		return true;
	}

	/**
	 * Sanitizes all received fields recursively. If a field is something we don't need to
	 * sanitize then we don't touch it.
	 *
	 * @param array<string, mixed> $params Array of params.
	 *
	 * @return array<string, mixed>
	 */
	protected function sanitizeFields(array $params)
	{
		foreach ($params as $key => $param) {
			if (is_string($param)) {
				$type = json_decode($param, true)['type'];

				if ($type === 'textarea') {
					$params[$key] = \sanitize_textarea_field($param);
				} else {
					$params[$key] = \sanitize_text_field($param);
				}
			} elseif (is_array($param)) {
				$params[$key] = $this->sanitizeFields($param);
			}
		}

		return $params;
	}

	/**
	 * Toggle if this route requires nonce verification.
	 *
	 * @return bool
	 */
	protected function requiresNonceVerification(): bool
	{
		return false;
	}

	/**
	 * WordPress replaces dots with underscores for some reason. This is undesired behavior when we need to map
	 * need record field values to existing lookup fields (we need to use @odata.bind in field's key).
	 *
	 * Quick and dirty fix is to replace these values back to dots after receiving them.
	 *
	 * @param array<string, mixed> $params Request params.
	 *
	 * @return array<string, mixed>
	 */
	protected function fixDotUnderscoreReplacement(array $params): array
	{
		foreach ($params as $key => $value) {
			if (strpos($key, '@odata_bind') !== false) {
				$newKey = str_replace('@odata_bind', '@odata.bind', $key);
				unset($params[$key]);
				$params[$newKey] = $value;
			}
		}

		return $params;
	}

	/**
	 * Verifies everything is ok with request.
	 *
	 * @param \WP_REST_Request $request WP_REST_Request object.
	 * @param string $formId Form Id.
	 *
	 * @throws UnverifiedRequestException When we should abort the request for some reason.
	 *
	 * @return array<string, mixed> Filtered request params.
	 */
	protected function verifyRequest(\WP_REST_Request $request, string $formId = ''): array
	{
		$params = $this->sanitizeFields($request->get_query_params());
		$params = $this->fixDotUnderscoreReplacement($params);
		$postParams = $this->sanitizeFields($request->get_body_params());
		$files = $request->get_file_params();

		// Quick hack for nested params like checkboxes and radios.
		$params = $this->fixNestedParams($params);
		$postParams = $this->fixNestedParams($postParams);

		// Verify nonce if submitted.
		if ($this->requiresNonceVerification()) {
			if (
				! isset($params['nonce']) ||
				! isset($params['form-unique-id']) ||
				! wp_verify_nonce($params['nonce'], $params['form-unique-id'])
			) {
				throw new UnverifiedRequestException(
					\esc_html__('Invalid nonce.', 'eightshift-forms')
				);
			}
		}

		// Validate GET Params.
		$validateParams = $this->validator->validate($params, [], $formId);
		if (!empty($validateParams)) {
			throw new UnverifiedRequestException(
				\esc_html__('Missing one or more required GET parameters to process the request.', 'eightshift-forms'),
				$validateParams
			);
		}

		// Validate POST Params.
		$validatePostParams = $this->validator->validate($postParams, [], $formId);
		if (!empty($validatePostParams)) {
			throw new UnverifiedRequestException(
				\esc_html__('Missing one or more required POST parameters to process the request.', 'eightshift-forms'),
				$validatePostParams
			);
		}

		$validatePostParams = $this->validator->validate($postParams, $files, $formId);
		if (!empty($validatePostParams)) {
			throw new UnverifiedRequestException(
				\esc_html__('Missing one or more required POST parameters to process the request.', 'eightshift-forms'),
				$validatePostParams
			);
		}

		return [
			'get' => $params,
			'post' => $postParams,
			'files' => $files,
		];
	}

	/**
	 * Return form Type from form params.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @return string
	 */
	protected function getFormType(array $params): string
	{
		$formType = $params['es-form-type'] ?? '';

		if (!$formType) {
			return '';
		}

		$formType = json_decode($formType, true)['value'];

		return $formType;
	}

	/**
	 * Return form ID from form params and determins if ID needs decrypting.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 * @param bool $decrypt Use decryption on post ID.
	 *
	 * @return string
	 */
	protected function getFormId(array $params, bool $decrypt = false): string
	{
		$formId = $params['es-form-post-id'] ?? '';

		if (!$formId) {
			return '';
		}

		$formId = json_decode($formId, true)['value'];

		if ($decrypt) {
			return (string) Helper::encryptor('decrypt', $formId);
		}

		return $formId;
	}

	/**
	 * Remove uncesesery params before submitting data to validation.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @return array<string, mixed>
	 */
	protected function removeUneceseryParams(array $params): array
	{
		foreach ($params as $key => $value) {
			if ($key === 'es-form-type') {
				unset($params['es-form-type']);
			}

			if ($key === 'es-form-submit') {
				unset($params['es-form-submit']);
			}

			if ($key === 'es-form-post-id') {
				unset($params['es-form-post-id']);
			}
		}

		return $params;
	}

	/**
	 * Quick hack for nested params like checkboxes and radios.
	 *
	 * @param array<string, mixed> $params Prams array.
	 *
	 * @return array<string, mixed>
	 */
	protected function fixNestedParams(array $params): array
	{
		foreach ($params as $paramKey => $paramValue) {
			if (is_array($paramValue)) {
				foreach ($paramValue as $itemsKey => $itemsValue) {
					foreach ($itemsValue as $itemKey => $itemValue) {
						$params["{$paramKey}[{$itemsKey}][{$itemKey}]"] = $itemValue;
					}
				}
				unset($params[$paramKey]);
			}
		}

		return $params;
	}
}
