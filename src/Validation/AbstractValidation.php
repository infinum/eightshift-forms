<?php

/**
 * The class for form validation
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

/**
 * Class Validation
 */
abstract class AbstractValidation implements ValidatorInterface
{
	/**
	 * Maximum file size expressed in B.
	 *
	 * @var integer
	 */
	public const MAX_FILE_SIZE = 5 * 1024 * 1024;

	/**
	 * Minimum file size expressed in B.
	 *
	 * @var integer
	 */
	public const MIN_FILE_SIZE = 1;

	/**
	 * Check if string contains url
	 *
	 * @param string $string String to check.
	 *
	 * @return boolean
	 */
	public function containsUrl(string $string): bool
	{
		return (bool) preg_match('/(http|ftp|mailto)/', $string);
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
		$fileExtension = explode('.', $fileName);
		$validTypes = explode(',', str_replace(' ', '', str_replace('.', '', $fileTypes)));

		return in_array(end($fileExtension), $validTypes, true);
	}
}
