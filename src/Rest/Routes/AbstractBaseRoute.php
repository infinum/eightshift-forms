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
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Rest\ApiHelper;
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
	 * List of all custom form params used.
	 */
	public const CUSTOM_FORM_PARAMS = [
		'postId' => 'es-form-post-id',
		'type' => 'es-form-type',
		'settingsType' => 'es-form-settings-type',
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
		'fieldName' => 'data-field-name',
		'fieldType' => 'data-field-type',
		'trackingEventName' => 'data-tracking-event-name',
		'trackingAdditionalData' => 'data-tracking-additional-data',
		'tracking' => 'data-tracking',
		'successRedirect' => 'data-success-redirect',
		'successRedirectVariation' => 'data-success-redirect-variation',
		'conditionalTags' => 'data-conditional-tags',
		'typeSelector' => 'data-type-selector',
		'actionExternal' => 'data-action-external',
		'fieldTypeInternal' => 'data-type-internal',
		'fieldUncheckedValue' => 'data-unchecked-value',
		'settingsType' => 'data-settings-type',
		'groupSaveAsOneField' => 'data-group-save-as-one-field',
		'datePreviewFormat' => 'data-preview-format',
		'dateOutputFormat' => 'data-output-format',
		'selectShowCountryIcons' => 'data-select-show-country-icons',
		'selectAllowSearch' => 'data-allow-search',
		'selectInitial' => 'data-initial',
		'selectPlaceholder' => 'data-placeholder',
		'phoneSync' => 'data-phone-sync',
		'phoneDisablePicker' => 'data-phone-disable-picker',
		'saveAsJson' => 'data-save-as-json',
		'downloads' => 'data-downloads',
		'blockSsr' => 'data-block-ssr',
		'disabledDefaultStyles' => 'data-disabled-default-styles',
		'globalMsgHeadingSuccess' => 'data-msg-heading-success',
		'globalMsgHeadingError' => 'data-msg-heading-error',
		'hideCaptchaBadge' => 'data-hide-captcha-badge',
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
	public const ROUTE_PREFIX_FORM_SUBMIT = 'form-submit';

	/**
	 * Dynamic name route prefix for settings.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_SETTINGS = 'settings';

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
		return \array_map(
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
	 * Return mailer for sender email field params.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @return string
	 */
	protected function getFormCustomAction(array $params): string
	{
		return $params[self::CUSTOM_FORM_PARAMS['action']]['value'] ?? '';
	}

	/**
	 * Return mailer for sender email field params.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @return string
	 */
	protected function getFormCustomActionExternal(array $params): string
	{
		return $params[self::CUSTOM_FORM_PARAMS['actionExternal']]['value'] ?? '';
	}

	/**
	 * Return form settings type from form params.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @throws UnverifiedRequestException Wrong request response.
	 *
	 * @return string
	 */
	protected function getFormSettingsType(array $params): string
	{
		return $params[self::CUSTOM_FORM_PARAMS['settingsType']]['value'] ?? '';
	}

	/**
	 * Return form ID from form params.
	 *
	 * @param array<string, mixed> $params Array of params got from form.
	 *
	 * @throws UnverifiedRequestException Wrong request response.
	 *
	 * @return string
	 */
	protected function getFormId(array $params): string
	{
		$formId = $params[self::CUSTOM_FORM_PARAMS['postId']] ?? '';

		if (!$formId) {
			throw new UnverifiedRequestException(
				\__('Something went wrong while submitting your form. Please try again.', 'eightshift-forms')
			);
		}

		return $formId['value'] ?? '';
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
}
