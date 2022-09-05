<?php

/**
 * The class register route for Form Settings endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\AdminMenus\FormGlobalSettingsAdminSubMenu;
use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsTroubleshooting;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use WP_REST_Request;

/**
 * Class FormSettingsSubmitRoute
 */
class FormSettingsSubmitRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ValidatorInterface $validator
	) {
		$this->validator = $validator;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/form-settings-submit';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
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
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{

		// Try catch request.
		try {
			$params = $this->prepareParams($request->get_body_params());

			// Get encrypted form ID and decrypt it.
			$formId = $this->getFormId($params, false);

			// Determine form type.
			$formType = $this->getFormType($params);

			// Check if form settings or global settings.
			$formInternalType = 'settings';
			if (!$formId) {
				$formInternalType = 'global';
			}

			// Get form fields for validation.
			$formData = isset(Filters::ALL[$formType][$formInternalType]) ? \apply_filters(Filters::ALL[$formType][$formInternalType], $formId) : [];

			// Validate request.
			if (!$this->isCheckboxOptionChecked(SettingsTroubleshooting::SETTINGS_TROUBLESHOOTING_SKIP_VALIDATION_KEY, SettingsTroubleshooting::SETTINGS_TROUBLESHOOTING_DEBUGGING_KEY)) {
				$this->verifyRequest(
					$params,
					$request->get_file_params(),
					$formId,
					$formData
				);
			}

			// Remove unnecessary internal params before continue.
			$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

			// Remove unnecessary params.
			foreach ($params as $key => $value) {
				if (isset($customFields[$key])) {
					unset($params[$key]);
				}
			}

			// Determine form type to use.
			switch ($formType) {
				case SettingsCache::SETTINGS_TYPE_KEY:
					return $this->cache($params);
				default:
					// If form ID is not set this is considered an global setting.
					if (empty($formId)) {
						// Save all fields in the settings.
						foreach ($params as $key => $value) {
							// Check if key needs updating or deleting.
							if ($value['value']) {
								\update_option($key, $value['value']);
							} else {
								\delete_option($key);
							}
						}
					} else {
						// Save all fields in the settings.
						foreach ($params as $key => $value) {
							// Check if key needs updating or deleting.
							if ($value['value']) {
								\update_post_meta((int) $formId, $key, $value['value']);
							} else {
								\delete_post_meta((int) $formId, $key);
							}
						}
					}
					break;
			}

			return \rest_ensure_response([
				'code' => 200,
				'status' => 'success',
				'message' => \esc_html__('Changes saved!', 'eightshift-forms'),
			]);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				[
					'code' => 400,
					'status' => 'error_validation',
					'message' => $e->getMessage(),
					'validation' => $e->getData(),
				]
			);
		}
	}

	/**
	 * Delete transient cache from the DB.
	 *
	 * @param array<int|string, mixed> $params Keys to delete.
	 *
	 * @return mixed
	 */
	private function cache(array $params)
	{
		if (! \current_user_can(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
			]);
		}

		foreach ($params as $key => $items) {
			if (!isset(SettingsCache::ALL_CACHE[$key])) {
				continue;
			}

			foreach (SettingsCache::ALL_CACHE[$key] as $item) {
				\delete_transient($item);
			}
		}

		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => \esc_html__('Selected cache successfully deleted!', 'eightshift-forms'),
		]);
	}
}
