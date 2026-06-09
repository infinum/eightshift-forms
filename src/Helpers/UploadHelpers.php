<?php

/**
 * The media upload helper specific functionality.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Config\Config;
use EightshiftForms\Validation\FileSecurity\FileSecurityScanner;

/**
 * UploadHelpers class
 */
final class UploadHelpers
{
	/**
	 * Detect if there is and error in the file upload.
	 *
	 * @param string $error String value, can be file path or error key.
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
		'errorFileUploadFailedSecurityScan' => '',

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
	 * @param array<string, mixed>              $file              File to prepare.
	 * @param array<string, array<int, string>> $extraAllowedMimes Optional `extension => [mime, ...]` supplement for the belt-and-braces security scan
	 *                                                            (mirror of what the caller passed to the up-front Validator scan).
	 *
	 * @return array<string, mixed>
	 */
	public static function uploadFile(array $file, array $extraAllowedMimes = []): array
	{
		$output = $file;

		$fieldName = $file['fieldName'] ?? '';
		$fileId = $file['id'] ?? '';
		$fileName = $file['name'] ?? '';
		$error = $file['error'] ?? '';
		$ext = \pathinfo((string) $fileName, \PATHINFO_EXTENSION);
		$name = \pathinfo((string) $fileName, \PATHINFO_FILENAME);
		$tmpName = $file['tmp_name'] ?? '';
		$uniqueId = \bin2hex(\random_bytes(4));

		if ($file === []) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadNoFileProvided',
				]
			);
		}

		if (!$fieldName) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadNoNameProvided',
				]
			);
		}

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

		// If file is faulty return error.
		if ($error !== \UPLOAD_ERR_OK) {
			return \array_merge(
				$output,
				[
					'errorOutput' => 'errorFileUploadFaultyFile',
				]
			);
		}

		// Belt-and-braces: run the security scanner immediately before the
		// file leaves PHP's managed tmp area. If the caller forgot to call
		// validateFiles, the file still never reaches esforms-tmp.
		if (\is_string($tmpName) && $tmpName !== '') {
			$scanError = new FileSecurityScanner()->scan($tmpName, (string) $fileName, $extraAllowedMimes);
			if ($scanError !== '') {
				return \array_merge(
					$output,
					[
						'errorOutput' => 'errorFileUploadFailedSecurityScan',
					]
				);
			}
		}

		// Create final folder location path.
		$outputName = \sanitize_file_name("{$name}-{$uniqueId}.{$ext}");
		$finalFilePath = "{$folderPath}{$outputName}";

		// Move the file to new location.
		$move = \move_uploaded_file($tmpName, $finalFilePath);
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
				'outputName' => $outputName,
				'ext' => $ext,
				'errorOutput' => '',
			]
		);
	}

	/**
	 * Delete files from the uploads folder.
	 *
	 * @param array<string, mixed> $files Delete submitted files.
	 */
	public static function deleteFiles(array $files): void
	{
		if ($files === []) {
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
	 */
	public static function isFileFaulty(array $files): bool
	{
		return \array_any($files, fn($file): bool => $file === 'error');
	}

	/**
	 * Get upload folder path.
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
