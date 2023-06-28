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
	 * List of all custom form params used.
	 */
	public const CUSTOM_FORM_PARAMS = [
		'formId' => 'es-form-form-id',
		'postId' => 'es-form-post-id',
		'type' => 'es-form-type',
		'name' => 'es-form-field-name',
		'steps' => 'es-form-steps',
		'settingsType' => 'es-form-settings-type',
		'singleSubmit' => 'es-form-single-submit',
		'storage' => 'es-form-storage',
		'action' => 'es-form-action',
		'actionExternal' => 'es-form-action-external',
		'conditionalTags' => 'es-form-conditional-tags',
		'fileId' => 'es-form-file-id',
		'hubspotCookie' => 'es-form-hubspot-cookie',
		'hubspotPageName' => 'es-form-hubspot-page-name',
		'hubspotPageUrl' => 'es-form-hubspot-page-url',
		'mailchimpTags' => 'es-form-mailchimp-tags',
		'captcha' => 'es-form-captcha',
		'direct' => 'es-form-direct',
		'itemId' => 'es-form-item-id',
		'innerId' => 'es-form-inner-id',
	];

	/**
	 * List of all custom form data attributes used.
	 */
	public const CUSTOM_FORM_DATA_ATTRIBUTES = [
		'formType' => 'data-form-type',
		'stepId' => 'data-step-id',
		'submitStepDirection' => 'data-step-direction',
		'postId' => 'data-post-id',
		'formId' => 'data-form-id',
		'fieldId' => 'data-field-id',
		'fieldName' => 'data-field-name',
		'fieldType' => 'data-field-type',
		'fieldPreventSubmit' => 'data-field-prevent-submit',
		'trackingEventName' => 'data-tracking-event-name',
		'trackingAdditionalData' => 'data-tracking-additional-data',
		'tracking' => 'data-tracking',
		'cacheType' => 'data-cache-type',
		'syncId' => 'data-sync-id',
		'migrationType' => 'data-migration-type',
		'migrationExportItems' => 'data-migration-export-items',
		'successRedirect' => 'data-success-redirect',
		'successRedirectVariation' => 'data-success-redirect-variation',
		'conditionalTags' => 'data-conditional-tags',
		'typeSelector' => 'data-type-selector',
		'actionExternal' => 'data-action-external',
		'fieldTypeCustom' => 'data-type-custom',
		'fieldUncheckedValue' => 'data-unchecked-value',
		'settingsType' => 'data-settings-type',
		'groupSaveAsOneField' => 'data-group-save-as-one-field',
		'datePreviewFormat' => 'data-preview-format',
		'dateOutputFormat' => 'data-output-format',
		'selectAllowSearch' => 'data-allow-search',
		'selectPlaceholder' => 'data-placeholder',
		'selectCustomProperties' => 'data-custom-properties',
		'selectCountryCode' => 'data-country-code',
		'selectCountryLabel' => 'data-country-label',
		'selectCountryNumber' => 'data-country-number',
		'selectVisibility' => 'data-visibility',
		'selectId' => 'data-id',
		'selectValue' => 'data-value',
		'phoneSync' => 'data-phone-sync',
		'phoneDisablePicker' => 'data-phone-disable-picker',
		'saveAsJson' => 'data-save-as-json',
		'downloads' => 'data-downloads',
		'blockSsr' => 'data-block-ssr',
		'disabledDefaultStyles' => 'data-disabled-default-styles',
		'globalMsgHeadingSuccess' => 'data-msg-heading-success',
		'globalMsgHeadingError' => 'data-msg-heading-error',
		'hideCaptchaBadge' => 'data-hide-captcha-badge',
		'reload' => 'data-reload',
		'hubspotTypeId' => 'data-hubspot-type-id'
	];

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
	 * Convert JS FormData object to usable data in php.
	 * Check if array then output only value that is not empty.
	 *
	 * @param array<string, mixed> $params Params to convert.
	 *
	 * @return array<string, mixed>
	 */
	protected function prepareParams(array $params): array
	{
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

		foreach ($paramsOutput as $key => $value) {
			switch ($key) {
				// Used for direct import from settings.
				case self::CUSTOM_FORM_PARAMS['direct']:
					$output['directImport'] = (bool) $value['value'];
					break;
				// Used for direct import from settings.
				case self::CUSTOM_FORM_PARAMS['itemId']:
					$output['itemId'] = $value['value'];
					break;
				// Used for direct import from settings.
				case self::CUSTOM_FORM_PARAMS['innerId']:
					$output['innerId'] = $value['value'];
					break;
				case self::CUSTOM_FORM_PARAMS['formId']:
					$output['formId'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case self::CUSTOM_FORM_PARAMS['postId']:
					$output['postId'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case self::CUSTOM_FORM_PARAMS['type']:
					$output['type'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case self::CUSTOM_FORM_PARAMS['action']:
					$output['action'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case self::CUSTOM_FORM_PARAMS['captcha']:
					$output['captcha'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case self::CUSTOM_FORM_PARAMS['actionExternal']:
					$output['actionExternal'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case self::CUSTOM_FORM_PARAMS['settingsType']:
					$output['settingsType'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case self::CUSTOM_FORM_PARAMS['storage']:
					$output['storage'] = $value['value'];
					$value['value'] = \json_decode($value['value'], true);
					$output['params'][$key] = $value;
					break;
				case self::CUSTOM_FORM_PARAMS['steps']:
					$output['apiSteps'] = [
						'fields' => $value['value'],
						'current' => $value['custom'],
					];
					break;
				default:
					$type = $value['type'] ?? '';

					if ($type === 'file') {
						$output['files'][$key] = $value['value'] ? \array_merge(
							$value,
							[
								'value' => \array_map(
									function ($item) {
										return $this->getFilePath($item);
									},
									\explode(self::DELIMITER, $value['value'])
								),
							]
						) : [];
					} else {
						$output['params'][$key] = $value;
					}
					break;
			}
		}

		return $output;
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
				'id' => $params[AbstractBaseRoute::CUSTOM_FORM_PARAMS['fileId']]['value'] ?? '',
				'fieldName' => $params[AbstractBaseRoute::CUSTOM_FORM_PARAMS['name']]['value'] ?? '',
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
		$params = $this->prepareParams($request->get_body_params());

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
			if ($type === Settings::SETTINGS_TYPE_NAME || $type === Settings::SETTINGS_GLOBAL_TYPE_NAME) {
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

			// Populare files on upload.
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
