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
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use EightshiftFormsVendor\EightshiftLibs\Rest\CallableRouteInterface;
use EightshiftForms\Validation\Validator; // phpcs:ignore

/**
 * Class FormSubmitRoute
 *
 * @property Validator $validator
 */
abstract class AbstractBaseRoute extends AbstractRoute implements CallableRouteInterface
{
	/**
	 * List of all custom form params used.
	 */
	public const CUSTOM_FORM_PARAMS = [
		'postId' => 'es-form-post-id',
		'type' => 'es-form-type',
		'singleSubmit' => 'es-form-single-submit',
		'storage' => 'es-form-storage',
		'action' => 'es-form-action',
		'actionExternal' => 'es-form-action-external',
		'conditionalTags' => 'es-form-conditional-tags',
		'hubspotCookie' => 'es-form-hubspot-cookie',
		'hubspotPageName' => 'es-form-hubspot-page-name',
		'hubspotPageUrl' => 'es-form-hubspot-page-url',
		'mailchimpTags' => 'es-form-mailchimp-tags',
	];

	/**
	 * List of all custom form data attributes used.
	 */
	public const CUSTOM_FORM_DATA_ATTRIBUTES = [
		'formType' => 'data-form-type',
		'formPostId' => 'data-form-post-id',
		'fieldId' => 'data-field-id',
		'trackingEventName' => 'data-tracking-event-name',
		'tracking' => 'data-tracking',
		'trackingSelectLabel' => 'data-tracking-select-label',
		'successRedirect' => 'data-success-redirect',
		'conditionalTags' => 'data-conditional-tags',
		'typeSelector' => 'data-type-selector',
		'actionExternal' => 'data-action-external',
		'fieldTypeInternal' => 'data-type-internal',
		'fieldUncheckedValue' => 'data-unchecked-value',
	];

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
	 * @param array<string|int, mixed> $params Array of params.
	 *
	 * @return array<string|int, mixed>
	 */
	protected function sanitizeFields(array $params)
	{
		foreach ($params as $key => $param) {
			$type = $param['type'] ?? '';

			if (\array_values($param) === $param) {
				$params[$key] = $this->sanitizeFields($param);
			} else {
				if ($type === 'textarea') {
					$params[$key]['value'] = \sanitize_textarea_field($param['value']);
				} else {
					$params[$key]['value'] = \sanitize_text_field($param['value']);
				}
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
	 * Verifies everything is ok with request.
	 *
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<string, bool|string>> $files Files array.
	 * @param string $formId Form Id.
	 * @param array<string, mixed> $formData Form data to validate.
	 *
	 * @throws UnverifiedRequestException When we should abort the request for some reason.
	 *
	 * @return void
	 */
	protected function verifyRequest(array $params, array $files = [], string $formId = '', array $formData = []): void
	{
		// Sanitize Fields.
		$params = $this->sanitizeFields($params);

		// Verify nonce if submitted.
		if ($this->requiresNonceVerification()) {
			if (
				! isset($params['nonce']) ||
				! isset($params['form-unique-id']) ||
				! \wp_verify_nonce($params['nonce'], $params['form-unique-id'])
			) {
				throw new UnverifiedRequestException(
					\esc_html__('Invalid nonce.', 'eightshift-forms')
				);
			}
		}

		// Validate Params.
		$validate = $this->validator->validate($params, $files, $formId, $formData);
		if (!empty($validate)) {
			throw new UnverifiedRequestException(
				\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
				$validate
			);
		}
	}

	/**
	 * Convert JS FormData object to usable data in php.
	 * Check if array then output only value that is not empty.
	 *
	 * @param array<string, mixed> $params Params to convert.
	 *
	 * @return array<string, mixed>
	 */
	protected function prepareParams(array $params): array
	{
		return \array_map(
			static function ($item) {
				// Check if array then output only value that is not empty.
				if (\is_array($item)) {
					// Loop all items and decode.
					$inner = \array_map(
						static function ($item) {
							return \json_decode($item, true);
						},
						$item
					);

					// Find all items where value is not empty.
					$innerNotEmpty = \array_values(
						\array_filter(
							$inner,
							static function ($innerItem) {
								return !empty($innerItem['value']);
							}
						)
					);

					// Fallback if everything is empty.
					if (!$innerNotEmpty) {
						return $inner[0];
					}

					// If multiple values this is checkbox or select multiple.
					if (\count($innerNotEmpty) > 1) {
						$multiple = \array_values(
							\array_map(
								static function ($item) {
									return $item['value'];
								},
								$innerNotEmpty
							)
						);

						// Append values to the first value.
						$innerNotEmpty[0]['value'] = \implode(", ", $multiple);

						return $innerNotEmpty[0];
					}

					// If one item then this is probably radio.
					return $innerNotEmpty[0];
				}

				// Just decode value.
				return \json_decode($item, true);
			},
			$params
		);
	}

	/**
	 * Return form Type from form params.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @throws UnverifiedRequestException Wrong request response.
	 *
	 * @return string
	 */
	protected function getFormType(array $params): string
	{
		$formType = $params[self::CUSTOM_FORM_PARAMS['type']] ?? '';

		if (!$formType) {
			throw new UnverifiedRequestException(
				\__('Something went wrong while submitting your form. Please try again.', 'eightshift-forms')
			);
		}

		return $formType['value'] ?? '';
	}

	/**
	 * Return form sender details from form params.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @return array<string, mixed>
	 */
	protected function getSenderDetails(array $params): array
	{
		$output = [];

		foreach ($params as $param) {
			$name = $param['name'] ?? '';
			$value = $param['value'] ?? '';

			if (!$name) {
				continue;
			}

			if (($name === 'sender-email') && !empty($value)) {
				$output[$name] = $value;
			}
		}

		return $output;
	}

	/**
	 * Return form ID from form params and determins if ID needs decrypting.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 * @param bool $throwError Throw error if missing post Id.
	 *
	 * @throws UnverifiedRequestException Wrong request response.
	 *
	 * @return string
	 */
	protected function getFormId(array $params, bool $throwError = true): string
	{
		$formId = $params[self::CUSTOM_FORM_PARAMS['postId']] ?? '';

		if (!$formId) {
			return '';
		}

		$formId = $formId['value'] ?? '';

		if (!$formId && $throwError) {
			throw new UnverifiedRequestException(
				\__('Something went wrong while submitting your form. Please try again.', 'eightshift-forms')
			);
		}

		return $formId;
	}

	/**
	 * Extract storage parameters from params.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @return array<string, mixed>
	 */
	protected function extractStorageParams(array $params): array
	{
		if (!isset($params[self::CUSTOM_FORM_PARAMS['storage']])) {
			return $params;
		}

		$storage = $params[self::CUSTOM_FORM_PARAMS['storage']]['value'] ?? [];

		if (!$storage) {
			return $params;
		}

		$storage = \json_decode($storage, true);

		$params[self::CUSTOM_FORM_PARAMS['storage']]['value'] = $storage;

		return $params;
	}
}
