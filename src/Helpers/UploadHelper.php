<?php

/**
 * The media upload helper specific functionality.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Rest\Routes\AbstractBaseRoute;

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
	protected function uploadFile(array $file): array
	{
		$output = [];

		if (!$file) {
			return $output;
		}

		$fieldName = $file['fieldName'] ?? '';

		if (!$fieldName) {
			return $output;
		}

		$fileId = $file['id'] ?? '';

		if (!$fileId) {
			return $output;
		}

		if (!\defined('WP_CONTENT_DIR')) {
			return $output;
		}

		$folderPath = \WP_CONTENT_DIR . \DIRECTORY_SEPARATOR . 'esforms-tmp' . \DIRECTORY_SEPARATOR;

		if (!\is_dir($folderPath)) {
			\mkdir($folderPath);
		}

		$error = $file['error'] ?? '';

		// If file is faulty return error.
		if ($error !== \UPLOAD_ERR_OK) {
			return $output;
		}

		// Create hashed file name so there is no collision.
		$ext = \explode('.', $file['name']);
		$ext = \end($ext);
		$name = "{$fileId}.{$ext}";

		// Create final folder location path.
		$finalFilePath = $folderPath . $name;

		// Move the file to new location.
		\move_uploaded_file($file['tmp_name'], $finalFilePath);

		$output[$fieldName] = [
			'id' => $fileId,
			'name' => $name,
			'path' => $finalFilePath,
			'type' => $file['type'],
		];

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
