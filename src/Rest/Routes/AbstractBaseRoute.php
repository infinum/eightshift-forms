<?php

/**
 * The class register route for Base endpoint used on all forms.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Settings\Settings\Settings;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use EightshiftFormsVendor\EightshiftLibs\Rest\CallableRouteInterface;
use WP_REST_Request;

/**
 * Class AbstractBaseRoute
 */
abstract class AbstractBaseRoute extends AbstractRoute implements CallableRouteInterface
{
	/**
	 * Use API helper trait.
	 */
	use ApiHelper;

	/**
	 * Use trait Upload_Helper inside class.
	 */
	use UploadHelper;

	/**
	 * Status error const.
	 *
	 * @var string
	 */
	public const STATUS_ERROR = 'error';

	/**
	 * Status success const.
	 *
	 * @var string
	 */
	public const STATUS_SUCCESS = 'success';

	/**
	 * Status warning const.
	 *
	 * @var string
	 */
	public const STATUS_WARNING = 'warning';

	/**
	 * Delimiter used in checkboxes and multiple items.
	 *
	 * @var string
	 */
	public const DELIMITER = '---';

	/**
	 * Dynamic name route prefix for integrations items inner.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_INTEGRATION_ITEMS_INNER = 'integration-items-inner';

	/**
	 * Dynamic name route prefix for integrations items.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_INTEGRATION_ITEMS = 'integration-items';

	/**
	 * Dynamic name route prefix for form submit.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_FORM_SUBMIT = 'submit';

	/**
	 * Dynamic name route prefix for integration editor.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_INTEGRATION_EDITOR = 'integration-editor';

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

		// Check if request maybe has json params usualy sent by the Block editor.
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
						$innerNotEmpty[0]['value'] = \implode(AbstractBaseRoute::DELIMITER, $multiple);

						return $innerNotEmpty[0];
					}

					// If one item then this is probably radio.
					return $innerNotEmpty[0];
				}

				// Just decode value.
				return \json_decode(\sanitize_text_field($item), true);
			},
			$params
		);

		$output = [];

		// If this route is for public form prepare all params.
		foreach ($paramsOutput as $key => $value) {
			switch ($key) {
				// Used for direct import from settings.
				case Helper::getStateParam('direct'):
					$output['directImport'] = (bool) $value['value'];
					break;
				// Used for direct import from settings.
				case Helper::getStateParam('itemId'):
					$output['itemId'] = $value['value'];
					break;
				// Used for direct import from settings.
				case Helper::getStateParam('innerId'):
					$output['innerId'] = $value['value'];
					break;
				case Helper::getStateParam('formId'):
					$output['formId'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case Helper::getStateParam('postId'):
					$output['postId'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case Helper::getStateParam('type'):
					$output['type'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case Helper::getStateParam('action'):
					$output['action'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case Helper::getStateParam('captcha'):
					$output['captcha'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case Helper::getStateParam('actionExternal'):
					$output['actionExternal'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case Helper::getStateParam('settingsType'):
					$output['settingsType'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case Helper::getStateParam('storage'):
					$output['storage'] = $value['value'];
					$value['value'] = (!empty($value['value'])) ? \json_decode($value['value'], true) : [];
					$output['params'][$key] = $value;
					break;
				case Helper::getStateParam('steps'):
					$output['apiSteps'] = [
						'fields' => $value['value'],
						'current' => $value['custom'],
					];
					break;
				default:
					// All other "normal" fields.
					$fieldType = $value['type'] ?? '';
					$fieldValue = $value['value'] ?? '';

					if ($fieldType === 'file') {
						$output['files'][$key] = $fieldValue ? \array_merge(
							$value,
							[
								'value' => \array_map(
									function ($item) {
										return $this->getFilePath($item);
									},
									\explode(self::DELIMITER, $fieldValue)
								),
							]
						) : $value;
						break;
					}

					if ($fieldType === 'rating' && $fieldValue === '0') {
						$value['value'] = '';
					}

					$output['params'][$key] = $value;

					break;
			}
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
	 * @param array<string, mixed> $file File array from reuqest.
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
				'id' => $params[Helper::getStateParam('fileId')]['value'] ?? '',
				'fieldName' => $params[Helper::getStateParam('name')]['value'] ?? '',
			]
		);
	}

	/**
	 * Check user permission for route action.
	 *
	 * @param string $permission Permission to check.
	 *
	 * @return array<string, mixed>
	 */
	protected function checkUserPermission(string $permission = FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY): array
	{
		if (\current_user_can($permission)) {
			return [];
		}

		return $this->getApiPermissionsErrorOutput();
	}

	/**
	 * Prepare array for later check like validation and etc...
	 *
	 * @param mixed $request Data got from endpoint url.
	 *
	 * @return array<string, mixed>
	 */
	protected function getFormDataReference($request): array
	{
		// Get params from request.
		$params = $this->prepareApiParams($request);

		// Populare params.
		$formDataReference['params'] = $params['params'] ?? [];

		// Populate files from uploaded ID.
		$formDataReference['files'] = $params['files'] ?? [];

		// Get form directImport from params.
		if (isset($params['directImport'])) {
			$formDataReference['directImport'] = true;
			$formDataReference['itemId'] = $params['itemId'] ?? '';
			$formDataReference['innerId'] = $params['innerId'] ?? '';
			$formDataReference['type'] = $params['type'] ?? '';
			$formDataReference['formId'] = $params['formId'] ?? '';
			$formDataReference['postId'] = $params['postId'] ?? '';
			$formDataReference['params'] = $params['params'] ?? [];
			$formDataReference['files'] = $params['files'] ?? [];
		} else {
			// Get form id from params.
			$formId = $params['formId'] ?? '';

			// Get form type from params.
			$type = $params['type'] ?? '';

			// Get form settings for admin from params.
			$formSettingsType = $params['settingsType'] ?? '';

			// Manual populate output it admin settings our build it from form Id.
			if (
				$type === Settings::SETTINGS_TYPE_NAME ||
				$type === Settings::SETTINGS_GLOBAL_TYPE_NAME ||
				$type === 'fileUploadAdmin'
			) {
				$formDataReference = [
					'formId' => $formId,
					'type' => $type,
					'itemId' => '',
					'innerId' => '',
					'fieldsOnly' => isset(Filters::ALL[$formSettingsType][$type]) ? \apply_filters(Filters::ALL[$formSettingsType][$type], $formId) : [],
				];
			} else {
				$formDataReference = Helper::getFormDetailsById($formId);
			}

			// Populare params.
			$formDataReference['params'] = $params['params'] ?? [];

			// Populate files from uploaded ID.
			$formDataReference['files'] = $params['files'] ?? [];

			// Populare files on upload. Only populated on file upload.
			$formDataReference['filesUpload'] = $this->prepareFile($request->get_file_params(), $params['params'] ?? []);

			// Populare action.
			$formDataReference['action'] = $params['action'] ?? '';

			// Populare action external.
			$formDataReference['actionExternal'] = $params['actionExternal'] ?? '';

			// Populare step fields.
			$formDataReference['apiSteps'] = $params['apiSteps'] ?? [];

			// Get form captcha from params.
			$formDataReference['captcha'] = $params['captcha'] ?? [];

			// Get form post Id from params.
			$formDataReference['postId'] = $params['postId'] ?? '';

			// Get form storage from params.
			$formDataReference['storage'] = \json_decode($params['storage'] ?? '', true) ?? [];
		}

		return $formDataReference;
	}
}
