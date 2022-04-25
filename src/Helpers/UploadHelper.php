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
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	protected function uploadFiles(array $files): array
	{
		$output = [];

		if (!$files) {
			return $output;
		}

		if (!\defined('WP_CONTENT_DIR')) {
			return $output;
		}

		$folderPath = \WP_CONTENT_DIR . \DIRECTORY_SEPARATOR . 'esforms-tmp' . \DIRECTORY_SEPARATOR;

		if (!\is_dir($folderPath)) {
			\mkdir($folderPath);
		}

		foreach ($files as $fileKey => $fileValue) {
			foreach ($fileValue['name'] as $key => $value) {
				$error = $fileValue['error'][$key] ?? '';

				// If file is faulty return error.
				if ($error !== \UPLOAD_ERR_OK) {
					continue;
				}

				// Create hashed file name so there is no collision.
				$originalName = $fileValue['name'][$key];
				$name = \md5((string) \time()) . '-' . \basename($originalName);
				$tmpName = $fileValue['tmp_name'][$key];

				// Create final folder location path.
				$finalFilePath = $folderPath . \DIRECTORY_SEPARATOR . $name;

				// Move the file to new location.
				\move_uploaded_file($tmpName, $finalFilePath);

				$output[$fileKey][] = [
					'id' => $fileKey,
					'index' => $key,
					'fileName' => $originalName,
					'name' => $name,
					'path' => $finalFilePath
				];
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
		if (!$files) {
			return;
		}

		foreach ($files as $items) {
			if (!$items) {
				continue;
			}

			foreach ($items as $file) {
				if (\file_exists($file['path'])) {
					\unlink($file['path']);
				}
			}
		}
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
