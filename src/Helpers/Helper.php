<?php

/**
 * Trait that holds all generic helpers.
 *
 * @package EightshiftLibs\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

/**
 * Helper class.
 */
class Helper
{

	/**
	 * Encript/Decrypt method.
	 *
	 * @param string $action Action used.
	 * @param string $string String used.
	 *
	 * @return string|bool
	 */
	public static function encryptor(string $action, string $string)
	{
		$encryptMethod = "AES-256-CBC";
		$secretKey = wp_salt(); // user define private key.
		$secretIv = wp_salt('SECURE_AUTH_KEY'); // user define secret key.
		$key = hash('sha256', $secretKey);
		$iv = substr(hash('sha256', $secretIv), 0, 16); // sha256 is hash_hmac_algo.

		if ($action === 'encrypt') {
			$output = openssl_encrypt($string, $encryptMethod, $key, 0, $iv);

			return base64_encode($output); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		}

		return openssl_decrypt(base64_decode($string), $encryptMethod, $key, 0, $iv); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}
}
