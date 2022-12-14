<?php

/**
 * The class to provide form builder json to block editor for integrations.
 *
 * @package EightshiftForms\Rest\Routes\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use WP_REST_Request;

/**
 * Class IntegrationEditorRoute
 */
class IntegrationEditorRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/integration-editor';

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var MapperInterface
	 */
	protected $hubspot;

	/**
	 * Create a new instance.
	 *
	 * @param MapperInterface $hubspot Inject Hubspot which holds Hubspot form data.
	 */
	public function __construct(
		MapperInterface $hubspot
	) {
		$this->hubspot = $hubspot;
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

		$formId = $request->get_params()['id'] ?? '';
		if (!$formId) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form ID.', 'eightshift-forms'),
			]);
		}

		$itemId = $request->get_params()['itemId'] ?? '';
		if (!$itemId) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form integration item ID.', 'eightshift-forms'),
			]);
		}

		$type = $request->get_params()['type'] ?? '';
		if (!$type) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form integration type.', 'eightshift-forms'),
			]);
		}

		$fields = $this->hubspot->getFormBlockGrammarArray($formId, $itemId, $type);

		if (!$fields) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing integration fields.', 'eightshift-forms'),
			]);
		}

		// $this->diffChanges($formId, $fields);

		// $update = wp_update_post([
		// 	'ID' => $formId,
		// 	'post_content' => serialize_blocks([$fields]),
		// ]);

		// if ($update < 0 || \is_wp_error($update)) {
		// 	return \rest_ensure_response([
		// 		'code' => 400,
		// 		'status' => 'error',
		// 		'message' => \esc_html__('Something went wrong in updating form.', 'eightshift-forms'),
		// 	]);
		// }

		$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		// Exit with success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => \esc_html__('Form updated.', 'eightshift-forms'),
			'data' => $isDeveloperMode ? $fields : [],
		]);
	}

	private function diffChanges(string $formId, array $fields): array
	{
		if (!$fields) {
			return [
				'status' => 'new_content_missing',
				'message' => esc_html__('New integration content is missing or empty.', 'eightshift-forms'),
				'reload' => false,
			];
		}

		$currentFormData = get_post_field('post_content', $formId);
		if (!$currentFormData) {
			return [
				'status' => 'old_content_missing',
				'message' => esc_html__('Old content is missing or empty.', 'eightshift-forms'),
				'reload' => false,
			];
		}

		$currentFormData = parse_blocks($currentFormData)[0] ?? [];
		if (!$currentFormData) {
			return [
				'status' => 'old_content_parsed_missing_blocks',
				'message' => esc_html__('Missing old content.', 'eightshift-forms'),
				'reload' => false,
			];
		}

		$blocksCurrent = $this->getBlockIntegration($currentFormData);
		$blocksNew = $this->getBlockIntegration($fields);

		// $result = $this->check_diff_multi($fields, $currentFormData);

		error_log( print_r( ( $currentFormData ), true ) );
		// error_log( print_r( ( $fields['blockName'] ), true ) );
		// error_log( print_r( ( $result ), true ) );

		return [
			'status' => 'error',
			'message' => esc_html__('Something went wrong.', 'eightshift-forms'),
			'reload' => false,
		];
	}

	private function getBlockIntegration($blocks): array
	{
		$blocks = $blocks['innerBlocks'] ?? [];

		if (!$blocks) {
			return [];
		}

		$block = $blocks[0] ?? [];

		if (!$block) {
			return [];
		}

		return $block;
	}

	private function checkBlockName($old, $new): array {
		$blockNameOld = $old['blockName'] ?? '';
		$blockNameNew = $new['blockName'] ?? '';

		if ($blockNameOld === $blockNameNew) {
			return [
				'status' => 'old_content_parsed_missing_blocks',
				'message' => esc_html__('Missing old content.', 'eightshift-forms'),
			];
		}

		// if (!$blockNameOld && $blockNameNew) {
		// 	ret
		// }

		// if (!$blockNameOld || $blockNameNew) {
		// 	return 
		// }
	}
}
