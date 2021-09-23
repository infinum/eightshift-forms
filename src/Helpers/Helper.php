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
		$encrypt_method = "AES-256-CBC";
		$secret_key = wp_salt(); // user define private key
		$secret_iv = wp_salt('SECURE_AUTH_KEY'); // user define secret key
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo

		if ($action == 'encrypt') {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);

			return base64_encode($output);
		}

		return openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	}
}
