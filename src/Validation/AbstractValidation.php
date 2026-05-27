<?php

/**
 * The class for form validation.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use Throwable;
use finfo;

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
	 * Checks whether the mimetype for the file is valid, i.e. that what the
	 * bytes on disk actually are (magic-byte detection via libmagic) matches
	 * the extension claimed by the filename. The client-supplied `$file['type']`
	 * is never trusted — it can be set to anything by an attacker.
	 *
	 * @param array<string|int> $file File array.
	 * @return boolean True if mimetype matches extension, false otherwise.
	 */
	public function isMimeTypeValid(array $file): bool
	{
		$tmpName = $file['tmp_name'] ?? '';
		if (!$tmpName || !\is_readable((string) $tmpName)) {
			// Without a real file on disk we cannot trust anything the client said.
			return false;
		}

		$fileMimetype = $this->detectMimeFromFile((string) $tmpName);
		if ($fileMimetype === '') {
			return false;
		}

		// Google Docs exports come through with a `vnd.openxmlformats-...` MIME
		// that contains additional slashes; normalize to type/subtype.
		$parts = \explode('/', $fileMimetype);
		if (\count($parts) > 1) {
			$last = \end($parts);
			$fileMimetype = "{$parts[0]}/{$last}";
		} else {
			$fileMimetype = $parts[0];
		}

		$fileExtension = $this->getFileExtensionFromFilename($file['name']);
		$mimeTypes = \array_flip(\wp_get_mime_types());
		$allowedExtensionsForMimetype = \explode('|', $mimeTypes[$fileMimetype] ?? '');

		return \in_array($fileExtension, $allowedExtensionsForMimetype, true);
	}

	/**
	 * Detect MIME from file contents (not from client headers). Uses libmagic
	 * via `finfo` and falls back to `mime_content_type` if `finfo` is missing.
	 *
	 * @param string $filepath Absolute path to the file.
	 *
	 * @return string Lowercase MIME, or empty string when detection fails.
	 */
	protected function detectMimeFromFile(string $filepath): string
	{
		if (\class_exists(finfo::class)) {
			try {
				$finfo = new finfo(\FILEINFO_MIME_TYPE);
				$mime = $finfo->file($filepath);
				if (\is_string($mime) && $mime !== '') {
					return \strtolower($mime);
				}
			} catch (Throwable $t) {
				// Fall through to mime_content_type.
			}
		}

		if (\function_exists('mime_content_type')) {
			try {
				$mime = \mime_content_type($filepath);
				if (\is_string($mime) && $mime !== '') {
					return \strtolower($mime);
				}
			} catch (Throwable $t) {
				return '';
			}
		}

		return '';
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
