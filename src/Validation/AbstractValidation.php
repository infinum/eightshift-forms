<?php

/**
 * The class for form validation.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Hooks\Filters;

/**
 * Class Validation
 */
abstract class AbstractValidation implements ValidatorInterface
{
	/**
	 * Check if string is url.
	 *
	 * @param string $string String to check.
	 *
	 * @return boolean
	 */
	public function isUrl(string $string): bool
	{
		return (bool) preg_match('/(http:\/\/|https:\/\/|ftp:\/\/|mailto:)/', $string);
	}

	/**
	 * Check if string is email.
	 *
	 * @param string $string String to check.
	 *
	 * @return boolean
	 */
	public function isEmail(string $string): bool
	{
		return (bool) filter_var($string, FILTER_VALIDATE_EMAIL);
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

		return in_array($this->getFileExtensionFromFilename($fileName), $validTypes, true);
	}

	/**
	 * Checks whether the mimetype for the file is valid,
	 * i.e. that it matches the extension. If the file is written
	 * to disk, it'll check its mime_content_type from the filesystem.
	 * Use the validation filter failMimetypeValidationWhenFileNotOnFS
	 * to override this behaviour.
	 * 
	 * @param array $file File array.
	 * @return boolean True if mimetype matches extension, false otherwise.
	 */
	public function isMimeTypeValid(array $file): bool {
		$denyIfFileIsNotUploaded = apply_filters(Filters::getValidationSettingsFilterName('failMimetypeValidationWhenFileNotOnFS'), false); 
		$mimeTypes = \array_flip(\wp_get_mime_types());

		$fileMimetype = $file['type'];
		if ($file['tmp_name'] ?? false) {
			$fileMimetype = \mime_content_type($file['tmp_name']);
		}
		elseif ($denyIfFileIsNotUploaded) {
			return false;
		}

		$fileExtension = $this->getFileExtensionFromFilename($file['name']);

		if (in_array($fileExtension, explode('|', $mimeTypes[$fileMimetype] ?? []))) {
			return true;
		}

		return false;
	}

	/**
	 * Parses a comma-separated list of file extensions to return
	 * a normalized array of allowed extenstions.
	 * 
	 * @param string $fileTypes String of file extensions, e.g. ".pdf, jpg,.gif"
	 * @return array Array of extensions, e.g. ["pdf", "jpg", "gif"]
	 */
	private function parseFiletypesString(string $fileTypes): array {
		$validTypes = explode(',', str_replace(' ', '', str_replace('.', '', $fileTypes)));
		return $validTypes;
	}

	/**
	 * Given a filename, returns its extension.
	 *
	 * @param string $fileName File name.
	 * @return string Extension or filename if no extension.
	 */
	private function getFileExtensionFromFilename(string $fileName): string {
		$explodedFilename = explode('.', $fileName);
		return end($explodedFilename);
	}
}
