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
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSettingsSubmitRoute
 */
class FormSettingsSubmitRoute extends AbstractBaseRoute
{

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/form-settings-submit';


	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

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
	 * @param \WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return \WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(\WP_REST_Request $request)
	{

	// Try catch request.
		try {
			$params = $this->prepareParams($request->get_body_params());

			// Get encripted form ID and decrypt it.
			$formId = $this->getFormId($params);

			// Determine form type.
			$formType = $this->getFormType($params);

			// Check if form settings or global settings.
			$formInternalType = 'settings';
			if (!$formId) {
				$formInternalType = 'global';
			}

			// Get form fields for validation.
			$formData = isset(Filters::ALL[$formType][$formInternalType]) ? apply_filters(Filters::ALL[$formType][$formInternalType], $formId) : []; // @phpstan-ignore-line

			// Validate request.
			$this->verifyRequest(
				$params,
				$request->get_file_params(),
				$formId,
				$formData
			);

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
				'message' => esc_html__('Form successfully saved!', 'eightshift-form'),
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
		if (! current_user_can(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-form'),
			]);
		}

		foreach ($params as $key => $value) {
			if (!isset(array_flip(SettingsCache::ALL_CACHE)[$key])) {
				continue;
			}

			delete_transient((string) $key);
		}

		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => esc_html__('Selected cache successfully deleted!', 'eightshift-form'),
		]);
	}
}
