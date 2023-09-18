<?php

/**
 * The class register route for transfer endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
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
	 * Use trait Upload_Helper inside class.
	 */
	use UploadHelper;

	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Type global settings key.
	 */
	public const TYPE_GLOBAL_SETTINGS = 'globalSettings';

	/**
	 * Type forms key.
	 */
	public const TYPE_FORMS = 'forms';

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
	public function __construct(ValidatorInterface $validator)
	{
		$this->validator = $validator;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'transfer';

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
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $request->get_body_params();

		$type = $params['type'] ?? '';

		if (!$type) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Transfer version type key was not provided.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$output = [
			self::TYPE_GLOBAL_SETTINGS => [],
			self::TYPE_FORMS => [],
		];

		switch ($type) {
			case SettingsTransfer::TYPE_EXPORT_GLOBAL_SETTINGS:
				$output[self::TYPE_GLOBAL_SETTINGS] = $this->getExportGlobalSettings();
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_FORMS:
				$items = $params['items'] ?? [];

				if (!$items) {
					return \rest_ensure_response(
						$this->getApiErrorOutput(
							\esc_html__('Please click on the forms you want to export.', 'eightshift-forms'),
							[],
							$debug
						)
					);
				}

				$items = \explode(',', $items);

				$output[self::TYPE_FORMS] = $this->getExportForms($items);
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_ALL:
				$output[self::TYPE_GLOBAL_SETTINGS] = $this->getExportGlobalSettings();
				$output[self::TYPE_FORMS] = $this->getExportForms();
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_IMPORT:
				$upload = $params['upload'] ?? '';

				if (!$upload) {
					return \rest_ensure_response(
						$this->getApiErrorOutput(
							\esc_html__('Please use the upload field to provide the .json file for the upload.', 'eightshift-forms'),
							[],
							$debug
						)
					);
				}

				$uploadStatus = $this->getImport(
					$upload,
					isset($params['override']) ? \filter_var($params['override'], \FILTER_VALIDATE_BOOLEAN) : false
				);

				if (!$uploadStatus) {
					return \rest_ensure_response(
						$this->getApiErrorOutput(
							\esc_html__('There was an issue with your upload file. Please make sure you use forms export file and try again.', 'eightshift-forms'),
							[],
							$debug
						)
					);
				}

				$internalType = 'import';
				break;
			default:
				$internalType = 'transfer';
				break;
		}

		$output = \wp_json_encode($output);

		$date = \current_datetime()->format('Y-m-d-H-i-s-u');

		// Finish.
		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				// translators: %s will be replaced with the transfer internal type.
				\sprintf(\esc_html__('%s successfully done!', 'eightshift-forms'), \ucfirst($internalType)),
				[
					'name' => "eightshift-forms-{$type}-{$date}",
					'content' => $output,
				],
				$debug
			)
		);
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

	/**
	 * Import uploaded file.
	 *
	 * @param string $upload Upload file.
	 * @param bool $override Override existing form.
	 *
	 * @return boolean
	 */
	private function getImport(string $upload, bool $override): bool
	{
		$filePath = $this->getFilePath($upload);

		if (!$filePath) {
			return false;
		}

		$data = \json_decode(\implode(' ', (array)\file($filePath)), true);

		if (!$data) {
			return false;
		}

		$globalSettings = $data[self::TYPE_GLOBAL_SETTINGS] ?? [];
		$forms = $data[self::TYPE_FORMS] ?? [];

		// Bailout if both keys are missing.
		if (!$globalSettings && !$forms) {
			return false;
		}

		global $wpdb;

		// Import global settings.
		if ($globalSettings) {
			// Find all global existing setting.
			$existingGlobalSettings = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				"SELECT option_name name, option_value as value
				FROM $wpdb->options
				AND option_name REGEXP 'es-forms-'"
			);

			// If existing global settings are present delete them all.
			if ($existingGlobalSettings) {
				$existingGlobalSettings = $this->getMetaOutput($existingGlobalSettings);

				foreach ($existingGlobalSettings as $existingGlobalSetting) {
					$name = $existingGlobalSetting['name'] ?? '';

					if (!$name) {
						continue;
					}

					\delete_option($name);
				}
			}

			// Import all global settings.
			foreach ($globalSettings as $globalSetting) {
				$name = $globalSetting['name'] ?? '';
				$value = $globalSetting['value'] ?? '';

				if (!$name || !$value) {
					continue;
				}

				\update_option($name, $value);
			}
		}

		// Import forms.
		if ($forms) {
			foreach ($forms as $form) {
				$postTitle = $form['post_title'] ?? '';
				$postContent = $form['post_content'] ?? '';
				$postStatus = $form['post_status'] ?? '';
				$postPassword = $form['post_password'] ?? '';
				$postParent = $form['post_parent'] ?? '';
				$postType = $form['post_type'] ?? '';
				$postName = $form['post_name'] ?? '';

				// Check if form exists.
				$exists = $this->formExists([
					'name' => $postName,
					'post_type' => $postType,
				]);

				if ($override) {
					// If override checkbox is set update the existing form.
					$newId = \wp_update_post([
						'ID' => (int) $exists,
						'post_title' => $postTitle,
						'post_content' => \wp_slash($postContent),
						'post_status' => $postStatus,
						'post_password' => $postPassword,
						'post_parent' => $postParent,
						'post_type' => $postType,
					]);

					// Find all forms existing setting.
					$existingSettings = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$wpdb->prepare(
							"SELECT meta_key name, meta_value as value
							FROM $wpdb->postmeta
							WHERE post_id=%s
							AND meta_key REGEXP 'es-forms-'",
							$exists
						)
					);

					// If existing settings are present delete them all.
					if ($existingSettings) {
						$existingSettings = $this->getMetaOutput($existingSettings);

						foreach ($existingSettings as $existingSetting) {
							$name = $existingSetting['name'] ?? '';

							if (!$name) {
								continue;
							}

							\delete_post_meta((int) $exists, $name);
						}
					}
				} else {
					// If override checkbox is not set create new form.

					// If form name exists add copy to the title.
					if ($exists) {
						$postTitle = "{$postTitle} - copy";
					}

					// Create a new post.
					$newId = \wp_insert_post([
						'post_title' => $postTitle,
						'post_content' => \wp_slash($postContent),
						'post_status' => $postStatus,
						'post_password' => $postPassword,
						'post_parent' => $postParent,
						'post_type' => $postType,
					]);
				}

				$esSettings = $form['es_settings'] ?? [];

				// Check if form new form is created and has settings and create them.
				if ($newId && $esSettings) {
					foreach ($esSettings as $esSettings) {
						$name = $esSettings['name'] ?? '';
						$value = $esSettings['value'] ?? '';

						if (!$name || !$value) {
							continue;
						}

						\update_post_meta($newId, $name, $value);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Check if form exists
	 *
	 * @param array<string, mixed> $args Arguments to pass to WP_Query.
	 *
	 * @return string
	 */
	private function formExists(array $args): string
	{
		$args = \array_merge(
			$args,
			[
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'no_found_rows' => true,
				'fields' => 'ids',
				'numberposts' => 1,
			]
		);

		$theQuery = new WP_Query($args);

		\wp_reset_postdata();

		return isset($theQuery->posts[0]) ? (string) $theQuery->posts[0] : '';
	}
}
