<?php

namespace PhpJwtHelper;

use Firebase\JWT\{JWT,Key};

class JwtHelper
{
	private static $key;

	public static function getKey()
	{
		return self::$key;
	}

	private static function setKey(string $key): void
	{
		self::$key = $key;
	}

	public static function encode($sub)
	{
		if(!is_null($key = self::getKey())) {
			$key = $key;
		} else {
			$key = strrev(str_repeat(date('YmdH'), 2));
		}

		$time = time();
		$payload = array(
			"iat" => $time,
			"nbf" => $time - 100,
			"exp" => $time + (60*60),
			"sub" => $sub
		);
		$jwt = JWT::encode($payload, $key, 'HS256');

		list($algo, $payload, $signature) = explode('.', $jwt);
		$algo = self::changeOriginal($algo);
		$payload = self::changeOriginal($payload);

		$token = implode('.', [$algo, $payload, $signature]);

		return $token;
	}

	public static function decode(string $token)
	{
		list($algo, $payload, $signature) = explode('.', $token);
		$algo = self::getOriginal($algo);
		$payload = self::getOriginal($payload);

		$jwt = implode('.', [$algo, $payload, $signature]);
		$key = strrev(str_repeat(self::getKey(), 2));

		$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
		return $decoded;
	}

	private static function getOriginal(string $ciphertext, $key = null)
	{
		if(!is_null($key = self::getKey())) {
			$key = $key;
		} else if (is_null($key)) {
			$key = date('YmdH');
		}

		$c = base64_decode($ciphertext);
		$ivlen = openssl_cipher_iv_length($cipher = "AES-256-CBC");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len = 32);
		$ciphertext_raw = substr($c, $ivlen + $sha2len);
		$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
		if (hash_equals($hmac, $calcmac)) {
			self::setKey($key);
			return $original_plaintext;
		}

		self::setKey(date('YmdH', strtotime('-1 hour')));
		if ($key == date('YmdH', strtotime('-1 hour'))) return $ciphertext;

		return self::getOriginal($ciphertext, date('YmdH', strtotime('-1 hour')));
	}

	private static function changeOriginal(string $sub)
	{
		if(!is_null($key = self::getKey())) {
			$key = $key;
		} else {
			$key = date('YmdH');
		}

		$plaintext = $sub;
		$ivlen = openssl_cipher_iv_length($cipher = "AES-256-CBC");
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
		$ciphertext = base64_encode( $iv . $hmac . $ciphertext_raw );

		return $ciphertext;
	}
}