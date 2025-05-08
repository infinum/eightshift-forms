<?php

/**
 * The media upload helper specific functionality.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Config\Config;

/**
 * UploadHelpers class
 */
final class UploadHelpers
{
	/**
	 * Detect if there is and error in the file upload.
	 *
	 * @param string $error String value, can be file path or error key.
	 *
	 * @return boolean
	 */
	public static function isUploadError(string $error): bool
	{
		$errors = [
			// uploadFile() method errors.
			'errorFileUploadNoFileProvided' => '',
			'errorFileUploadNoNameProvided' => '',
			'errorFileUploadNoIdProvided' => '',
			'errorFileUploadFolderUploadPathMissing' => '',
			'errorFileUploadUnableToCreateFolder' => '',
			'errorFileUploadFaultyFile' => '',
			'errorFileUploadUnableToMoveFile' => '',

			// getFilePath() method errors.
			'errorFilePathMissingUploadFolder' => '',
			'errorFilePathMissingFile' => '',

			// getUploadFolderPath() method errors.
			'errorUploadFolderPathMissingWpContentDir' => '',
		];

		return isset($errors[$error]);
	}

	/**
	 * Prepare all files and upload to uploads folder.
	 *
	 * @param array<string, mixed> $file File to prepare.
	 *
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	public static function uploadFile(array $file): array
	{
		$output = $file;

		if (!$file) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadNoFileProvided',
				]
			);
		}

		$fieldName = $file['fieldName'] ?? '';

		if (!$fieldName) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadNoNameProvided',
				]
			);
		}

		$fileId = $file['id'] ?? '';

		if (!$fileId) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadNoIdProvided',
				]
			);
		}

		$folderPath = self::getUploadFolderPath();
		if (self::isUploadError($folderPath)) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadFolderUploadPathMissing',
				]
			);
		}

		if (!\is_dir($folderPath)) {
			$newFolder = \mkdir($folderPath); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir

			if (!$newFolder) {
				return \array_merge(
					$output,
					[
						'errorOutput' => 'errorFileUploadUnableToCreateFolder',
					]
				);
			}
		}

		$error = $file['error'] ?? '';

		// If file is faulty return error.
		if ($error !== \UPLOAD_ERR_OK) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadFaultyFile',
				]
			);
		}

		// Create hashed file name so there is no collision.
		$ext = \explode('.', $file['name']);
		$ext = \end($ext);
		$name = "{$fileId}.{$ext}";

		// Create final folder location path.
		$finalFilePath = "{$folderPath}{$name}";

		// Move the file to new location.
		$move = \move_uploaded_file($file['tmp_name'], $finalFilePath);
		if (!$move) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadUnableToMoveFile',
				]
			);
		}

		return \array_merge(
			$output,
			[
				'path' => $finalFilePath,
				'ext' => $ext,
				'errorOutput' => '',
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
	public static function deleteFiles(array $files): void
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
					\unlink($file['path']); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir, WordPress.WP.AlternativeFunctions.unlink_unlink
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
	public static function deleteUploadFolderContent(int $numberOfHours = 2): void
	{
		$folderPath = self::getUploadFolderPath();
		if (self::isUploadError($folderPath)) {
			return;
		}

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
			\unlink($file); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir, WordPress.WP.AlternativeFunctions.unlink_unlink
		}
	}

	/**
	 * Return file path by provided name with ext.
	 *
	 * @param string $name File name.
	 *
	 * @return string
	 */
	public static function getFilePath(string $name): string
	{
		$folderPath = self::getUploadFolderPath();
		if (self::isUploadError($folderPath)) {
			return 'errorFilePathMissingUploadFolder';
		}

		$filePath = "{$folderPath}{$name}";

		if (!\file_exists($filePath)) {
			return 'errorFilePathMissingFile';
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
	public static function getFileNameFromPath(string $path): string
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
	public static function getFileExtFromPath(string $path): string
	{
		$filename = self::getFileNameFromPath($path);
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
	public static function isFileFaulty(array $files): bool
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

	/**
	 * Get upload folder path.
	 *
	 * @return string
	 */
	private static function getUploadFolderPath(): string
	{
		if (!\defined('WP_CONTENT_DIR')) {
			return 'errorUploadFolderPathMissingWpContentDir';
		}

		$sep = \DIRECTORY_SEPARATOR;
		$dir = Config::TEMP_UPLOAD_DIR;
		return \WP_CONTENT_DIR . "{$sep}{$dir}{$sep}";
	}
}
