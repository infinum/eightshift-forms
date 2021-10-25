<?php

/**
 * The media upload helper specific functionality.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

/**
 * Trait UploadHelper
 */
trait UploadHelper
{

	/**
	 * Prepare all files and upload to uploads folder.
	 *
	 * @param array<string, mixed> $files Files to prepare.
	 *
	 * @return array<string, mixed>
	 */
	protected function prepareFiles(array $files): array
	{
		$output = [];
		$hasError = false;

		if (empty($files)) {
			return $output;
		}

		if (!function_exists('wp_handle_upload')) {
			// @codeCoverageIgnoreStart
			require_once ABSPATH . 'wp-admin/includes/file.php';
			// @codeCoverageIgnoreEnd
		}

		add_filter('upload_dir', [$this, 'changeUploadDir'], 1, 10);

		foreach ($files as $fileKey => $fileValue) {
			$upload = \wp_handle_upload(
				$fileValue,
				[
					'test_form' => false,
				]
			);

			if (array_key_exists('error', $upload)) {
				$hasError = true;
				$output[$fileKey] = 'error';
			} else {
				$output[$fileKey] = $upload['file'];
			}
		}

		if ($hasError) {
			$this->deleteFiles($output);
		}

		// Set everything back to normal.
		remove_filter('upload_dir', [$this, 'changeUploadDir'], 2);

		return $output;
	}

	/**
	 * Override the default upload path.
	 *
	 * @param array<string, mixed> $param Dir path.
	 * @param string $myDir My directory override.
	 *
	 * @return array<string, mixed>
	 */
	public function changeUploadDir(array $param, string $myDir = 'tmp'): array
	{
		$param['path'] = "{$param['basedir']}/{$myDir}";
		$param['url'] = "{$param['baseurl']}/{$myDir}";
		$param['subdir'] = '';

		return $param;
	}

	/**
	 * Delete files from the uploads folder.
	 *
	 * @param array<string, mixed> $files Delete submitted files.
	 *
	 * @return void
	 */
	protected function deleteFiles(array $files): void
	{
		array_map(
			function ($file) {
				if (!empty($file)) {
					\wp_delete_file($file);
				}
			},
			$files
		);
	}

	/**
	 * Check if there is a faulty file in the array.
	 *
	 * @param array<string, mixed> $files Files to check.
	 *
	 * @return boolean
	 */
	protected function isFileFaulty(array $files): bool
	{
		$isFaulty = false;

		foreach ($files as $file) {
			if ($file === 'error') {
				$isFaulty = true;
				break;
			}
		}

		return $isFaulty;
	}
}
