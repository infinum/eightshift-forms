<?php

/**
 * Transfer class.
 *
 * @package EightshiftForms\Transfer
 */

declare(strict_types=1);

namespace EightshiftForms\Transfer;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Helpers\UploadHelper;
use WP_Query;

/**
 * Transfer class.
 */
class Transfer implements TransferInterface
{
	/**
	 * Use trait UploadHelper inside class.
	 */
	use UploadHelper;

	/**
	 * Type global settings key.
	 */
	public const TYPE_GLOBAL_SETTINGS = 'globalSettings';

	/**
	 * Type forms key.
	 */
	public const TYPE_FORMS = 'forms';

	/**
	 * Export global settings.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getExportGlobalSettings(): array
	{
		global $wpdb;

		$options = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT option_name as name, option_value as value
				FROM $wpdb->options
				WHERE option_name LIKE '%es-forms-%'"
		);

		return $options ? $this->getMetaOutput($options) : [];
	}

	/**
	 * Export forms with settings.
	 *
	 * @param array<int, string> $items Specify items to query.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getExportForms(array $items = []): array
	{
		$args = [
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'post_status' => 'any',
			'nopaging' => true,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
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
					AND meta_key LIKE '%es-forms-%'", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
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
	 * Export one form with settings.
	 *
	 * @param string $item Specify item id to query.
	 *
	 * @return array<int, mixed>
	 */
	public function getExportForm(string $item): array
	{
		$args = [
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'posts_per_page' => 1, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'p' => (int) $item
		];

		$theQuery = new WP_Query($args);

		$form = $theQuery->posts;
		\wp_reset_postdata();

		if (!$form) {
			return [];
		}

		global $wpdb;

		$output = (array) $form[0];
		$settings = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT meta_key name, meta_value as value
				FROM $wpdb->postmeta
				WHERE post_id=%d
				AND meta_key LIKE '%es-forms-%'", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
				$item
			)
		);

		$output['es_settings'] = $this->getMetaOutput($settings);

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
	public function getImport(string $upload, bool $override): bool
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
				AND option_name LIKE '%es-forms-%'"
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

				\update_option($name, \maybe_unserialize($value));
			}
		}

		// Import forms.
		if ($forms) {
			foreach ($forms as $form) {
				$this->getImportByFormArray($form, $override);
			}
		}

		return true;
	}

	/**
	 * Import forms by form object.
	 *
	 * @param array<int, array<string, mixed>> $form Forms export details.
	 * @param bool $override Override existing form.
	 *
	 * @return boolean
	 */
	public function getImportByFormArray(array $form, bool $override): bool
	{
		global $wpdb;

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
				'post_parent' => (int) $postParent,
				'post_type' => $postType,
			]);

			// Find all forms existing setting.
			$existingSettings = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT meta_key name, meta_value as value
					FROM $wpdb->postmeta
					WHERE post_id=%s
					AND meta_key LIKE '%es-forms-%'", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
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
				'post_parent' => (int) $postParent,
				'post_type' => $postType,
			]);
		}

		$esSettings = $form['es_settings'] ?? [];

		// Check if new form is created and has settings. Create settings if true.
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
}
