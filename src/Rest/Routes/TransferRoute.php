<?php

/**
 * The class register route for transfer endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Validation\ValidatorInterface;
use WP_Query;
use WP_REST_Request;

/**
 * Class TransferRoute
 */
class TransferRoute extends AbstractBaseRoute
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
	public const ROUTE_SLUG = '/transfer';

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
		if (! \current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
			]);
		}

		$params = $request->get_body_params();

		$type = $params['type'] ?? '';

		if (!$type) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Transfer version type key was not provided.', 'eightshift-forms'),
			]);
		}

		$output = [
			'globalSettings' => [],
			'forms' => [],
		];

		switch ($type) {
			case SettingsTransfer::TYPE_EXPORT_GLOBAL_SETTINGS:
				$output['globalSettings'] = $this->getExportGlobalSettings();
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_FORMS:
				$items = $params['items'] ?? [];

				if (!$items) {
					return \rest_ensure_response([
						'code' => 400,
						'status' => 'error',
						'message' => \esc_html__('Please click on the forms you want to export.', 'eightshift-forms'),
					]);
				}

				$items = \explode(',', $items);

				$output['forms'] = $this->getExportForms($items);
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_ALL:
				$output['globalSettings'] = $this->getExportGlobalSettings();
				$output['forms'] = $this->getExportForms();
				$internalType = 'export';
				break;
			default:
				$internalType = 'transfer';
				break;
		}

		$output = \wp_json_encode($output);

		$date = \current_datetime()->format('Y-m-d-H-i-s-u');

		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			// translators: %s will be replaced with the transfer internal type.
			'message' => \sprintf(\esc_html__('%s successfully done!', 'eightshift-forms'), \ucfirst($internalType)),
			'data' => [
				'name' => "eightshift-forms-{$type}-{$date}",
				'content' => $output,
			],
		]);
	}

	/**
	 * Get formated meta/options output.
	 *
	 * @param array<int, object> $items Query output items.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getMetaOutput(array $items): array
	{
		$output = [];
		foreach ($items as $item) {
			$name = $item->name ?? '';
			$value = $item->value ?? '';

			if (!$name || !$value) {
				continue;
			}

			$output[] = [
				'name' => $name,
				'value' => $value,
			];
		}

		return $output;
	}

	/**
	 * Export global settings.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getExportGlobalSettings(): array
	{
		global $wpdb;

		$options = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT option_name as name, option_value as value
				FROM $wpdb->options
				WHERE option_name REGEXP 'es-forms-'"
		);

		return $options ? $this->getMetaOutput($options) : [];
	}

	/**
	 * Export Forms with settings.
	 *
	 * @param array<int, string> $items Specify items to query.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getExportForms(array $items = []): array
	{
		$args = [
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'posts_per_page' => 10000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		];

		if ($items) {
			$args['post__in'] = $items;
		}

		$theQuery = new WP_Query($args);

		$forms = $theQuery->posts;
		\wp_reset_postdata();

		if (!$forms) {
			return [];
		}

		global $wpdb;

		$output = [];
		foreach ($forms as $key => $form) {
			$settings = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT meta_key name, meta_value as value
					FROM $wpdb->postmeta
					WHERE post_id=%d
					AND meta_key REGEXP 'es-forms-'",
					$form->ID
				)
			);

			$output[$key] = (array) $form;

			if (!$settings) {
				continue;
			}

			$output[$key]['es_settings'] = $this->getMetaOutput($settings);
		}

		return $output;
	}
}
