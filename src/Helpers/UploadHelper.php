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
	 * @param array<string, mixed> $file File to prepare.
	 *
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	protected function uploadFile(array $file): array
	{
		if (!$file) {
			return [];
		}

		$fieldName = $file['fieldName'] ?? '';

		if (!$fieldName) {
			return [];
		}

		$fileId = $file['id'] ?? '';

		if (!$fileId) {
			return [];
		}

		if (!\defined('WP_CONTENT_DIR')) {
			return [];
		}

		$sep = \DIRECTORY_SEPARATOR;

		$folderPath = \WP_CONTENT_DIR . "{$sep}esforms-tmp{$sep}";

		if (!\is_dir($folderPath)) {
			\mkdir($folderPath);
		}

		$error = $file['error'] ?? '';

		// If file is faulty return error.
		if ($error !== \UPLOAD_ERR_OK) {
			return [];
		}

		// Create hashed file name so there is no collision.
		$ext = \explode('.', $file['name']);
		$ext = \end($ext);
		$name = "{$fileId}.{$ext}";

		// Create final folder location path.
		$finalFilePath = "{$folderPath}{$name}";

		// Move the file to new location.
		\move_uploaded_file($file['tmp_name'], $finalFilePath);

		return \array_merge(
			$file,
			[
				'path' => $finalFilePath,
				'ext' => $ext,
			]
		);
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
	 * Delete all items in the tem upload folder
	 *
	 * @param int $numberOfHours Number of hours used.
	 *
	 * @return void
	 */
	protected function deleteUploadFolderContent(int $numberOfHours = 2): void
	{
		if (!\defined('WP_CONTENT_DIR')) {
			return;
		}
		$sep = \DIRECTORY_SEPARATOR;

		$folderPath = \WP_CONTENT_DIR . "{$sep}esforms-tmp{$sep}";

		if (!\is_dir($folderPath)) {
			return;
		}

		$files = \glob("{$folderPath}*");

		if (!$files) {
			return;
		}

		foreach ($files as $file) {
			// file is younger than x hours skip it.
			if (\time() - \filemtime($file) < $numberOfHours * \HOUR_IN_SECONDS) {
				continue;
			}

			// Remove old files.
			\unlink($file);
		}
	}

	/**
	 * Return file path by provided name with ext.
	 *
	 * @param string $name File name.
	 *
	 * @return mixed
	 */
	protected function getFilePath(string $name)
	{
		if (!\defined('WP_CONTENT_DIR')) {
			return '';
		}

		$sep = \DIRECTORY_SEPARATOR;

		$filePath = \WP_CONTENT_DIR . "{$sep}esforms-tmp{$sep}{$name}";

		if (!\file_exists($filePath)) {
			return '';
		}

		return $filePath;
	}

	/**
	 * Return file name from path.
	 *
	 * @param string $path File path.
	 *
	 * @return string
	 */
	protected function getFileNameFromPath(string $path): string
	{
		$path = \explode(\DIRECTORY_SEPARATOR, $path);
		return \end($path);
	}

	/**
	 * Return file ext from path.
	 *
	 * @param string $path File path.
	 *
	 * @return string
	 */
	protected function getFileExtFromPath(string $path): string
	{
		$filename = $this->getFileNameFromPath($path);
		$ext = \explode('.', $filename);
		return \end($ext);
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
