<?php

/**
 * Class that holds all Encryption helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

/**
 * EncryptionHelpers class.
 */
class EncryptionHelpers
{
	/**
	 * Encrypt method.
	 *
	 * @param string $value Value used.
	 * @param string $action Action used.
	 *
	 * @return string|bool
	 */
	public static function encryptor(string $value, string $action = 'encrypt')
	{
		$filterNameSecretKey = HooksHelpers::getFilterName(['encryption', 'secretKey']);
		$secretKey = \apply_filters($filterNameSecretKey, \wp_salt()); // user define private key.

		$filterNameSecretIv = HooksHelpers::getFilterName(['encryption', 'secretIvKey']);
		$secretIv = \apply_filters($filterNameSecretIv, \wp_salt('SECURE_AUTH_KEY')); // user define secret key.

		$encryptMethod = 'AES-256-CBC';
		$key = \hash('sha256', $secretKey);
		$iv = \substr(\hash('sha256', $secretIv), 0, 16); // sha256 is hash_hmac_algo.

		if ($action === 'encrypt') {
			$output = \openssl_encrypt($value, $encryptMethod, $key, 0, $iv);

			return \base64_encode((string) $output); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		}

		return \openssl_decrypt(\base64_decode($value), $encryptMethod, $key, 0, $iv); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}

	/**
	 * Decrypt method.
	 *
	 * @param string $value Value used.
	 *
	 * @return string|bool
	 */
	public static function decryptor(string $value)
	{
		return self::encryptor($value, 'decryptor');
	}
}
