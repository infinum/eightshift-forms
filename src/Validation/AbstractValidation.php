<?php

/**
 * The class for form validation.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use Throwable;

/**
 * Class Validation
 */
abstract class AbstractValidation implements ValidatorInterface
{
	/**
	 * Check if string is url.
	 *
	 * @param string $url String to check.
	 *
	 * @return boolean
	 */
	public function isUrl(string $url): bool
	{
		return (bool) \preg_match('/(http:\/\/|https:\/\/|ftp:\/\/|mailto:)/', $url);
	}

	/**
	 * Check if string is email.
	 *
	 * @param string $email String to check.
	 *
	 * @return boolean
	 */
	public function isEmail(string $email): bool
	{
		$email = \strtolower($email);
		return (bool) \filter_var($email, \FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Check if emails top level domain is valid.
	 *
	 * @param string $email String to check.
	 * @param array<int, string> $db Database to reference.
	 *
	 * @return boolean
	 */
	public function isEmailTldValid(string $email, array $db): bool
	{
		$email = \strtolower($email);
		$email = \explode('.', $email);
		$email = \end($email);

		$check = \array_filter(
			$db,
			static function ($item) use ($email) {
				return $item === $email;
			}
		);

		return (bool) $check;
	}

	/**
	 * Validate File Minimum size.
	 *
	 * @param integer $fileSize File size value.
	 * @param integer $maxFileSize Max file size.
	 *
	 * @return boolean
	 */
	public function isFileMaxSizeValid(int $fileSize, int $maxFileSize): bool
	{
		return $fileSize <= $maxFileSize;
	}

	/**
	 * Validate File Minimum size.
	 *
	 * @param integer $fileSize File size value.
	 * @param integer $minFileSize Min file size.
	 *
	 * @return boolean
	 */
	public function isFileMinSizeValid(int $fileSize, int $minFileSize): bool
	{
		return $fileSize >= $minFileSize;
	}

	/**
	 * Validate File type.
	 *
	 * @param string $fileName Full name for file.
	 * @param string $fileTypes String of all file types.
	 *
	 * @return boolean
	 */
	public function isFileTypeValid(string $fileName, string $fileTypes): bool
	{
		$validTypes = $this->parseFiletypesString($fileTypes);

		return \in_array($this->getFileExtensionFromFilename($fileName), $validTypes, true);
	}

	/**
	 * Checks whether the mimetype for the file is valid,
	 * i.e. that it matches the extension. If the file is written
	 * to disk, it'll check its mime_content_type from the filesystem.
	 * Use the validation filter failMimetypeValidationWhenFileNotOnFS
	 * to override this behaviour.
	 *
	 * @param array<string|int> $file File array.
	 * @return boolean True if mimetype matches extension, false otherwise.
	 */
	public function isMimeTypeValid(array $file): bool
	{
		$denyIfFileIsNotUploaded = \apply_filters(UtilsHooksHelper::getFilterName(['validation', 'forceMimetypeFromFs']), false); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

		if (\getenv('TEST')) {
			$denyIfFileIsNotUploaded = \getenv('test_force_option_eightshift_forms_force_mimetype_from_fs');
		}

		$mimeTypes = \array_flip(\wp_get_mime_types());

		$fileMimetype = $file['type'];
		if ($file['tmp_name'] ?? false) {
			try {
				$fileMimetype = \mime_content_type($file['tmp_name']);
			} catch (Throwable $t) {
				if ($denyIfFileIsNotUploaded) {
					return false;
				}
			}
		} elseif ($denyIfFileIsNotUploaded) {
			return false;
		}

		// Check for the first and last item in array, this issue is due to Google Docs docx export file.
		$fileMimetype = \explode('/', $fileMimetype);

		if (\count($fileMimetype) > 1) {
			$last = \end($fileMimetype);
			$fileMimetype = "{$fileMimetype[0]}/{$last}";
		} else {
			$fileMimetype = $fileMimetype[0];
		}

		$fileExtension = $this->getFileExtensionFromFilename($file['name']);

		$allowedExtensionsForMimetype = \explode('|', $mimeTypes[$fileMimetype] ?? '');

		if (\in_array($fileExtension, $allowedExtensionsForMimetype, true)) {
			return true;
		}

		return false;
	}

	/**
	 * Parses a comma-separated list of file extensions to return
	 * a normalized array of allowed extensions.
	 *
	 * @param string $fileTypes String of file extensions, e.g. ".pdf, jpg,.gif".
	 * @return array<string> Array of extensions, e.g. ["pdf", "jpg", "gif"]
	 */
	private function parseFiletypesString(string $fileTypes): array
	{
		$fileTypes = \str_replace(['.', ' '], '', $fileTypes);
		$fileTypes = \explode(',', \strtolower($fileTypes));

		if ($fileTypes) {
			$fileTypes = \array_unique($fileTypes);
		}

		return $fileTypes;
	}

	/**
	 * Given a filename, returns its extension.
	 *
	 * @param string $fileName File name.
	 * @return string Extension or filename if no extension.
	 */
	private function getFileExtensionFromFilename(string $fileName): string
	{
		$explodedFilename = \explode('.', $fileName);
		return \strtolower(\end($explodedFilename));
	}
}
