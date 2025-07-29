<?php

/**
 * The class register route for Base endpoint used on all forms.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Helpers\UploadHelpers;
use EightshiftFormsVendor\EightshiftLibs\Rest\CallableRouteInterface;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use WP_REST_Request;

/**
 * Class AbstractBaseRoute
 */
abstract class AbstractBaseRoute extends AbstractRoute implements CallableRouteInterface
{
	public const R_MSG = 'message';
	public const R_CODE = 'code';
	public const R_STATUS = 'status';
	public const R_DATA = 'data';
	public const R_DEBUG = 'debug';
	public const R_DEBUG_KEY = 'debugKey';
	public const R_DEBUG_REQUEST = 'debugRequest';

	/**
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	abstract protected function isRouteAdminProtected(): bool;

	/**
	 * Method that returns project Route namespace
	 *
	 * @return string Project namespace for REST route.
	 */
	protected function getNamespace(): string
	{
		return Config::ROUTE_NAMESPACE;
	}

	/**
	 * Method that returns project route version
	 *
	 * @return string Route version as a string.
	 */
	protected function getVersion(): string
	{
		return Config::ROUTE_VERSION;
	}

	/**
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
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
	 * Toggle if this route requires nonce verification.
	 *
	 * @return bool
	 */
	protected function requiresNonceVerification(): bool
	{
		return false;
	}

	/**
	 * Extract params from request.
	 * Check if array then output only value that is not empty.
	 *
	 * @param WP_REST_Request $request $request Data got from endpoint url.
	 * @param string $type Request type.
	 *
	 * @return array<string, mixed>
	 */
	protected function getRequestParams(WP_REST_Request $request, string $type = self::CREATABLE): array
	{
		// Check type of request and extract params.
		switch ($type) {
			case self::CREATABLE:
				$params = $request->get_body_params();
				break;
			case self::READABLE:
				$params = $request->get_params();
				break;
			default:
				$params = [];
				break;
		}

		// Check if request maybe has json params usually sent by the Block editor.
		if ($request->get_json_params()) {
			$params = \array_merge(
				$params,
				$request->get_json_params(),
			);
		}

		return $params;
	}

	/**
	 * Convert JS FormData object to usable data in php.
	 *
	 * @param WP_REST_Request $request $request Data got from endpoint url.
	 * @param string $type Request type.
	 *
	 * @return array<string, mixed>
	 */
	protected function prepareApiParams(WP_REST_Request $request, string $type = self::CREATABLE): array
	{
		// Get params.
		$params = $this->getRequestParams($request, $type);

		// Bailout if there are no params.
		if (!$params) {
			return [];
		}

		// Skip any manipulations if direct param is set.
		$paramsOutput = \array_map(
			static function ($item) {
				// Check if array then output only value that is not empty.
				if (\is_array($item)) {
					// Loop all items and decode.
					$inner = \array_map(
						static function ($item) {
							return \json_decode(\sanitize_text_field($item), true);
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

					// If multiple values this is checkbox.
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
						$innerNotEmpty[0]['value'] = $multiple;

						return $innerNotEmpty[0];
					}

					// If one item then this is probably radio.
					return $innerNotEmpty[0];
				}

				// Try to clean the string.
				// Parts of the code taken from https://developer.wordpress.org/reference/functions/_sanitize_text_fields/.
				$item = \wp_check_invalid_utf8($item);
				$item = \wp_strip_all_tags($item);

				$filtered = \trim($item);

				// Remove percent-encoded characters.
				$found = false;
				while (\preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
					$filtered = \str_replace($match[0], '', $filtered);
					$found = true;
				}

				if ($found) {
					// Strip out the whitespace that may now exist after removing percent-encoded characters.
					$filtered = \trim(\preg_replace('/ +/', ' ', $filtered));
				}

				// Decode value.
				return \json_decode($filtered, true);
			},
			$params
		);

		$output = [];

		// These are the required keys for each field.
		$reqKeys = [
			'name' => '',
			'value' => '',
			'type' => '',
			'custom' => '',
			'typeCustom' => '',
		];

		$paramsBroken = false;

		// If this route is for public form prepare all params.
		foreach ($paramsOutput as $key => $value) {
			// Check if all required keys are present and bail out if not.
			if (!\is_array($value) || \array_diff_key($reqKeys, $value)) {
				$paramsBroken = true;
				break;
			}

			switch ($key) {
				// Used for direct import from settings.
				case UtilsHelper::getStateParam('direct'):
					$output['directImport'] = (bool) $value['value'];
					break;
				// Used for direct import from settings.
				case UtilsHelper::getStateParam('itemId'):
					$output['itemId'] = $value['value'];
					break;
				// Used for direct import from settings.
				case UtilsHelper::getStateParam('innerId'):
					$output['innerId'] = $value['value'];
					break;
				case UtilsHelper::getStateParam('formId'):
					$output['formId'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('postId'):
					$output['postId'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('type'):
					$output['type'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('secureData'):
					$output['secureData'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('action'):
					$output['action'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('captcha'):
					$output['captcha'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('actionExternal'):
					$output['actionExternal'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('settingsType'):
					$output['settingsType'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('storage'):
					$output['storage'] = $value['value'];
					$value['value'] = (!empty($value['value'])) ? \json_decode($value['value'], true) : [];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('steps'):
					$output['apiSteps'] = [
						'fields' => $value['value'],
						'current' => $value['custom'],
					];
					break;
				default:
					// All other "normal" fields.
					$fieldType = $value['type'] ?? '';
					$fieldValue = $value['value'] ?? '';
					$fieldName = $value['name'] ?? '';

					if (!$fieldName) {
						break;
					}

					// File.
					if ($fieldType === 'file') {
						$output['files'][$key] = $value;

						if (!$fieldValue) {
							$output['files'][$key]['value'] = [];
						} else {
							if (!\is_array($fieldValue)) {
								$fieldValue = [$fieldValue];
							}

							$output['files'][$key]['value'] = \array_map(
								static function (string $file) {
									return UploadHelpers::getFilePath($file);
								},
								$fieldValue
							);
						}
						break;
					}

					// Rating.
					if ($fieldType === 'rating' && $fieldValue === '0') {
						$value['value'] = '';
					}

					// Checkbox.
					if ($fieldType === 'checkbox') {
						if (!$fieldValue) {
							$value['value'] = [];
						} else {
							$value['value'] = \is_string($fieldValue) ? [$fieldValue] : $fieldValue;
						}
					}

					$output['params'][$key] = $value;

					break;
			}
		}

		// Bail out if we have a broken param.
		if ($paramsBroken) {
			return [];
		}

		return $output;
	}

	/**
	 * Convert JS FormData object to usable data in php.
	 *
	 * @param WP_REST_Request $request $request Data got from endpoint url.
	 * @param string $type Request type.
	 *
	 * @return array<string, mixed>
	 */
	protected function prepareSimpleApiParams(WP_REST_Request $request, string $type = self::CREATABLE): array
	{
		// Get params.
		$params = $this->getRequestParams($request, $type);

		// Bailout if there are no params.
		if (!$params) {
			return [];
		}

		return \array_map(
			static function ($item) {
				return \sanitize_text_field($item);
			},
			$params
		);
	}


	/**
	 * Prepare file from request for later usage. Attach custom data to file array.
	 *
	 * @param array<string, mixed> $file File array from request.
	 * @param array<string, mixed> $params Params to use.
	 * @return array<string, mixed>
	 */
	protected function prepareFile(array $file, array $params): array
	{
		$file = $file['file'] ?? [];

		if (!$file) {
			return [];
		}

		return \array_merge(
			$file,
			[
				'id' => $params[UtilsHelper::getStateParam('fileId')]['value'] ?? '',
				'fieldName' => $params[UtilsHelper::getStateParam('name')]['value'] ?? '',
			]
		);
	}

	/**
	 * Prepare form details api data.
	 *
	 * @param mixed $request Data got from endpoint url.
	 *
	 * @return array<string, mixed>
	 */
	protected function getFormDetailsApi($request): array
	{
		$output = [];

		// Get params from request.
		$params = $this->prepareApiParams($request);

		// Get form id from params.
		$formId = $params['formId'] ?? '';

		// Get form type from params.
		$type = $params['type'] ?? '';

		// Get form directImport from params.
		if (isset($params['directImport'])) {
			return $this->getFormDetailsApiDirectImport($params);
		}

		// Get form settings for admin from params.
		$formSettingsType = $params['settingsType'] ?? '';

		// Manual populate output it admin settings our build it from form Id.
		if (
			$type === Config::SETTINGS_TYPE_NAME ||
			$type === Config::SETTINGS_GLOBAL_TYPE_NAME ||
			$type === Config::FILE_UPLOAD_ADMIN_TYPE_NAME
		) {
			// This provides filter name for setting.
			$settingsName = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$formSettingsType][$type] ?? '';

			$output[Config::FD_FORM_ID] = $formId;
			$output[Config::FD_TYPE] = $type;
			$output[Config::FD_ITEM_ID] = '';
			$output[Config::FD_INNER_ID] = '';
			$output[Config::FD_FIELDS_ONLY] = !empty($settingsName) ? \apply_filters($settingsName, $formId) : [];
		} else {
			$formDetails = GeneralHelpers::getFormDetails($formId);

			$output[Config::FD_FORM_ID] = $formId;
			$output[Config::FD_IS_VALID] = $formDetails[Config::FD_IS_VALID] ?? false;
			$output[Config::FD_IS_API_VALID] = $formDetails[Config::FD_IS_API_VALID] ?? false;
			$output[Config::FD_LABEL] = $formDetails[Config::FD_LABEL] ?? '';
			$output[Config::FD_ICON] = $formDetails[Config::FD_ICON] ?? '';
			$output[Config::FD_TYPE] = $formDetails[Config::FD_TYPE] ?? '';
			$output[Config::FD_ITEM_ID] = $formDetails[Config::FD_ITEM_ID] ?? '';
			$output[Config::FD_INNER_ID] = $formDetails[Config::FD_INNER_ID] ?? '';
			$output[Config::FD_FIELDS] = $formDetails[Config::FD_FIELDS] ?? [];
			$output[Config::FD_FIELDS_ONLY] = $formDetails[Config::FD_FIELDS_ONLY] ?? [];
			$output[Config::FD_FIELD_NAMES] = $formDetails[Config::FD_FIELD_NAMES] ?? [];
			$output[Config::FD_STEPS_SETUP] = $formDetails[Config::FD_STEPS_SETUP] ?? [];
		}

		// Populate params.
		$output[Config::FD_PARAMS] = $params['params'] ?? [];

		// Populate files from uploaded ID.
		$output[Config::FD_FILES] = $params['files'] ?? [];

		// Populate files on upload. Only populated on file upload.
		$output[Config::FD_FILES_UPLOAD] = $this->prepareFile($request->get_file_params(), $params['params'] ?? []);

		// Populate action.
		$output[Config::FD_SECURE_DATA] = $params['secureData'] ?? '';

		// Populate action.
		$output[Config::FD_ACTION] = $params['action'] ?? '';

		// Populate action external.
		$output[Config::FD_ACTION_EXTERNAL] = $params['actionExternal'] ?? '';

		// Populate step fields.
		$output[Config::FD_API_STEPS] = $params['apiSteps'] ?? [];

		// Get form captcha from params.
		$output[Config::FD_CAPTCHA] = $params['captcha'] ?? [];

		// Get form post Id from params.
		$output[Config::FD_POST_ID] = $params['postId'] ?? '';

		// Get form storage from params.
		$output[Config::FD_STORAGE] = \json_decode($params['storage'] ?? '', true) ?? [];

		// Set debug original params.
		$output[Config::FD_PARAMS_ORIGINAL] = $this->getParamsOriginal($request);

		return $output;
	}

	/**
	 * Prepare form details api data for direct import.
	 *
	 * @param array<string, mixed> $params Params to use.
	 *
	 * @return array<string, mixed>
	 */
	private function getFormDetailsApiDirectImport(array $params): array
	{
		// Get form id from params.
		$formId = $params['formId'] ?? '';

		// Get form type from params.
		$type = $params['type'] ?? '';

		// Get form directImport from params.
		$output[Config::FD_DIRECT_IMPORT] = true;
		$output[Config::FD_TYPE] = $type;
		$output[Config::FD_FORM_ID] = $formId;
		$output[Config::FD_ITEM_ID] = $params['itemId'] ?? '';
		$output[Config::FD_INNER_ID] = $params['innerId'] ?? '';
		$output[Config::FD_POST_ID] = $params['postId'] ?? '';
		$output[Config::FD_PARAMS] = $params['params'] ?? [];
		$output[Config::FD_FILES] = $params['files'] ?? [];

		return $output;
	}

	/**
	 * Get params original.
	 *
	 * @param mixed $request Data got from endpoint url.
	 *
	 * @return string
	 */
	private function getParamsOriginal($request): string
	{
		return \sanitize_text_field(\wp_json_encode($this->getRequestParams($request)));
	}

	/**
	 * Get debug output.
	 *
	 * @param array<string, mixed> $data Data to use.
	 * @param array<string, mixed> $debug Debug data to use.
	 * @param WP_REST_Request $request Request to use.
	 *
	 * @return array<string, mixed>
	 */
	protected function getResponseDataOutput(
		array $data,
		array $debug,
		WP_REST_Request $request
	): array {
		$output = [];

		$isDeveloperMode = DeveloperHelpers::isDeveloperModeActive();

		if ($isDeveloperMode) {
			$output[self::R_DEBUG] = [
				self::R_DEBUG => $debug[self::R_DEBUG] ?? [],
				self::R_DEBUG_KEY => $debug[self::R_DEBUG_KEY] ?? '',
				self::R_DEBUG_REQUEST => [
					'body' => $request->get_body(),
					'params' => $request->get_params(),
					'method' => $request->get_method(),
					'headers' => $request->get_headers(),
					'bodyParams' => $request->get_body_params(),
					'queryParams' => $request->get_query_params(),
					'urlParams' => $request->get_url_params(),
					'route' => $request->get_route(),
				],
			];
		}

		// Check if there are any response output keys in the data and allowed to be returned.
		foreach (UtilsHelper::getStateResponseOutputKeys() as $key) {
			if (isset($data[$key])) {
				$output[$key] = $data[$key];
			}
		}

		return $output;
	}

	/**
	 * Check user permission for route action.
	 *
	 * @param string $permission Permission to check.
	 *
	 * @return bool
	 */
	protected function checkPermission(string $permission): bool
	{
		return \current_user_can($permission);
	}
}
