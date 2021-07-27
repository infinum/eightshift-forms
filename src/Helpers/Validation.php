<?php

/**
 * Helpers for validation
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

/**
 * Helpers for validation
 */
class Validation
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
	public static function containsUrl(string $string): bool
	{
		return (bool) preg_match('/(http|ftp|mailto)/', $string);
	}

	/**
	 * Validate File Minimum size.
	 *
	 * @param integer $fileSize File size value.
	 *
	 * @return boolean
	 */
	public static function isFileMaxSizeValid(int $fileSize): bool
	{
		return $fileSize <= static::MAX_FILE_SIZE;
	}

	/**
	 * Validate File Minimum size.
	 *
	 * @param integer $fileSize File size value.
	 *
	 * @return boolean
	 */
	public static function isFileMinSizeValid(int $fileSize): bool
	{
		return $fileSize >= static::MIN_FILE_SIZE ? true : false;
	}

	/**
	 * Validate File type.
	 *
	 * @param string $fileName Full name for file.
	 * @param string $fileTypes String of all file types.
	 *
	 * @return boolean
	 */
	public static function isFileTypeValid(string $fileName, string $fileTypes): bool
	{
		$fileExtension = explode('.', $fileName);
		$validTypes = explode(',', str_replace(' ', '', $fileTypes));

		return in_array(end($fileExtension), $validTypes, true) ? true : false;
	}
}
