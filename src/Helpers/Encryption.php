<?php

/**
 * Class that holds all Encryption helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftFormsVendor\Firebase\JWT\JWT;
use EightshiftFormsVendor\Firebase\JWT\Key;

/**
 * Encryption class.
 */
class Encryption
{
	/**
	 * Encode method - JWT.
	 *
	 * @param array<mixed> $payload Payload used to store.
	 * @param string $key Key used.
	 *
	 * @return string
	 */
	public static function encodeJwt(array $payload, string $key)
	{
		return JWT::encode($payload, $key, 'HS256');
	}

	/**
	 * Decode method - JWT.
	 *
	 * @param string $payload Payload used to export.
	 * @param string $key Key used.
	 *
	 * @return array<mixed>
	 */
	public static function decodeJwt(string $payload, string $key)
	{
		$output =  JWT::decode($payload, new Key($key, 'HS256'));

		return (array) $output;
	}

	/**
	 * Encript method.
	 *
	 * @param string $value Value used.
	 * @param string $action Action used.
	 *
	 * @return string|bool
	 */
	public static function encryptor(string $value, string $action = 'encrypt')
	{
		$encryptMethod = "AES-256-CBC";
		$secretKey = \wp_salt(); // user define private key.
		$secretIv = \wp_salt('SECURE_AUTH_KEY'); // user define secret key.
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
