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
	 * @return array<string, array<string, bool|string>>
	 */
	protected function uploadFiles(array $files): array
	{
		$output = [];

		if (!$files) {
			return $output;
		}

		if (!defined('WP_CONTENT_DIR')) {
			return $output;
		}

		$folderPath = WP_CONTENT_DIR . '/esforms-tmp';

		if (!is_dir($folderPath)) {
			mkdir($folderPath);
		}

		foreach ($files as $fileKey => $fileValue) {
			foreach ($fileValue['name'] as $key => $value) {
				$name = $fileValue['name'][$key] ?? '';
				$data = $fileValue['tmp_name'][$key] ?? '';

				if (!$name || !$data) {
					continue;
				}

				$file = esc_url($folderPath . '/' . md5((string) time()) . '-' . $name);

				if (file_exists($file)) {
					unlink($file);
				}

				$fileData = file_get_contents($data); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

				if (!$fileData) {
					continue;
				}

				$upload = file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
					$file,
					$fileData
				);

				if (!$upload) {
					$output["{$fileKey}---{$key}"] = [
						'success' => false,
						'path' => '',
					];
				} else {
					$output["{$fileKey}---{$key}"] = [
						'success' => true,
						'path' => $file,
					];
				}

				unlink($data);
			}
		}

		return $output;
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
			static function ($file) {
				if (file_exists($file['path'])) {
					unlink($file['path']);
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
