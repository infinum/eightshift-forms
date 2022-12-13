<?php

/**
 * The class to provide form builder json to block editor for integrations.
 *
 * @package EightshiftForms\Rest\Routes\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use WP_REST_Request;

/**
 * Class EditorFormBuilderRoute
 */
class EditorFormBuilderRoute extends AbstractBaseRoute
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/editor-form-builder/(?P<id>\d+)';

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
	 * By default allow public access to route.
	 *
	 * @return bool
	 */
	// public function permissionCallback(): bool
	// {
	// 	return true;
	// }

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
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
		if (! \current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
			]);
		}

		// Find form ID from url request.
		$formId = $request->get_url_params()['id'];

		// Detect form type based on the provided form ID.
		$type = Helper::getUsedFormTypeById($formId);

		// Find the correct form type filters.
		$integration = Filters::ALL[$type] ?? [];

		// Bailout if integration filters are missing.
		if (!$integration) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Provided form ID has no valid integration.', 'eightshift-forms'),
			]);
		}

		// Bailout if integration is not in use.
		$use = $integration['use'] ?? '';
		if (!$use) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Provided form ID has no active integration.', 'eightshift-forms'),
			]);
		}

		// Bailout if integration fileds map is not provided.
		$fields = $integration['fields'] ?? '';
		if (!$fields) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Provided form ID is missing integration fields map.', 'eightshift-forms'),
			]);
		}

		// Find integration file map.
		$formFields =  apply_filters( // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
			$fields,
			$formId
		);

		// Bailout if integration fileds map is empty.
		if (!$formFields) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Provided form ID integration fields map is empty.', 'eightshift-forms'),
			]);
		}

		// Exit with success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'data' => $formFields,
		]);
	}
}
