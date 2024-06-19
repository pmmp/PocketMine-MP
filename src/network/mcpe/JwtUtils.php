<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe;

use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Utils;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function chr;
use function count;
use function explode;
use function is_array;
use function json_decode;
use function json_encode;
use function json_last_error_msg;
use function ltrim;
use function openssl_error_string;
use function openssl_pkey_get_details;
use function openssl_pkey_get_public;
use function openssl_sign;
use function openssl_verify;
use function ord;
use function preg_match;
use function rtrim;
use function sprintf;
use function str_pad;
use function str_repeat;
use function str_replace;
use function str_split;
use function strlen;
use function strtr;
use function substr;
use const JSON_THROW_ON_ERROR;
use const OPENSSL_ALGO_SHA384;
use const STR_PAD_LEFT;

final class JwtUtils{
	public const BEDROCK_SIGNING_KEY_CURVE_NAME = "secp384r1";

	private const ASN1_INTEGER_TAG = "\x02";
	private const ASN1_SEQUENCE_TAG = "\x30";

	private const SIGNATURE_PART_LENGTH = 48;
	private const SIGNATURE_ALGORITHM = OPENSSL_ALGO_SHA384;

	/**
	 * @return string[]
	 * @phpstan-return array{string, string, string}
	 * @throws JwtException
	 */
	public static function split(string $jwt) : array{
		$v = explode(".", $jwt);
		if(count($v) !== 3){
			throw new JwtException("Expected exactly 3 JWT parts, got " . count($v));
		}
		return [$v[0], $v[1], $v[2]]; //workaround phpstan bug
	}

	/**
	 * TODO: replace this result with an object
	 *
	 * @return mixed[]
	 * @phpstan-return array{mixed[], mixed[], string}
	 *
	 * @throws JwtException
	 */
	public static function parse(string $token) : array{
		$v = self::split($token);
		$header = json_decode(self::b64UrlDecode($v[0]), true);
		if(!is_array($header)){
			throw new JwtException("Failed to decode JWT header JSON: " . json_last_error_msg());
		}
		$body = json_decode(self::b64UrlDecode($v[1]), true);
		if(!is_array($body)){
			throw new JwtException("Failed to decode JWT payload JSON: " . json_last_error_msg());
		}
		$signature = self::b64UrlDecode($v[2]);
		return [$header, $body, $signature];
	}

	private static function signaturePartToAsn1(string $part) : string{
		if(strlen($part) !== self::SIGNATURE_PART_LENGTH){
			throw new JwtException("R and S for a SHA384 signature must each be exactly 48 bytes, but have " . strlen($part) . " bytes");
		}
		$part = ltrim($part, "\x00");
		if(ord($part[0]) >= 128){
			//ASN.1 integers with a leading 1 bit are considered negative - add a leading 0 byte to prevent this
			//ECDSA signature R and S values are always positive
			$part = "\x00" . $part;
		}

		//we can assume the length is 1 byte here - if it were larger than 127, more complex logic would be needed
		return self::ASN1_INTEGER_TAG . chr(strlen($part)) . $part;
	}

	private static function rawSignatureToDer(string $rawSignature) : string{
		if(strlen($rawSignature) !== self::SIGNATURE_PART_LENGTH * 2){
			throw new JwtException("JWT signature has unexpected length, expected 96, got " . strlen($rawSignature));
		}

		[$rString, $sString] = str_split($rawSignature, self::SIGNATURE_PART_LENGTH);
		$sequence = self::signaturePartToAsn1($rString) . self::signaturePartToAsn1($sString);

		//we can assume the length is 1 byte here - if it were larger than 127, more complex logic would be needed
		return self::ASN1_SEQUENCE_TAG . chr(strlen($sequence)) . $sequence;
	}

	private static function signaturePartFromAsn1(BinaryStream $stream) : string{
		$prefix = $stream->get(1);
		if($prefix !== self::ASN1_INTEGER_TAG){
			throw new \InvalidArgumentException("Expected an ASN.1 INTEGER tag, got " . bin2hex($prefix));
		}
		//we can assume the length is 1 byte here - if it were larger than 127, more complex logic would be needed
		$length = $stream->getByte();
		if($length > self::SIGNATURE_PART_LENGTH + 1){ //each part may have an extra leading 0 byte to prevent it being interpreted as a negative number
			throw new \InvalidArgumentException("Expected at most 49 bytes for signature R or S, got $length");
		}
		$part = $stream->get($length);
		return str_pad(ltrim($part, "\x00"), self::SIGNATURE_PART_LENGTH, "\x00", STR_PAD_LEFT);
	}

	private static function rawSignatureFromDer(string $derSignature) : string{
		if($derSignature[0] !== self::ASN1_SEQUENCE_TAG){
			throw new \InvalidArgumentException("Invalid DER signature, expected ASN.1 SEQUENCE tag, got " . bin2hex($derSignature[0]));
		}

		//we can assume the length is 1 byte here - if it were larger than 127, more complex logic would be needed
		$length = ord($derSignature[1]);
		$parts = substr($derSignature, 2, $length);
		if(strlen($parts) !== $length){
			throw new \InvalidArgumentException("Invalid DER signature, expected $length sequence bytes, got " . strlen($parts));
		}

		$stream = new BinaryStream($parts);
		$rRaw = self::signaturePartFromAsn1($stream);
		$sRaw = self::signaturePartFromAsn1($stream);

		if(!$stream->feof()){
			throw new \InvalidArgumentException("Invalid DER signature, unexpected trailing sequence data");
		}

		return $rRaw . $sRaw;
	}

	/**
	 * @throws JwtException
	 */
	public static function verify(string $jwt, \OpenSSLAsymmetricKey $signingKey) : bool{
		[$header, $body, $signature] = self::split($jwt);

		$rawSignature = self::b64UrlDecode($signature);
		$derSignature = self::rawSignatureToDer($rawSignature);

		$v = openssl_verify(
			$header . '.' . $body,
			$derSignature,
			$signingKey,
			self::SIGNATURE_ALGORITHM
		);
		switch($v){
			case 0: return false;
			case 1: return true;
			case -1: throw new JwtException("Error verifying JWT signature: " . openssl_error_string());
			default: throw new AssumptionFailedError("openssl_verify() should only return -1, 0 or 1");
		}
	}

	/**
	 * @phpstan-param array<string, mixed> $header
	 * @phpstan-param array<string, mixed> $claims
	 */
	public static function create(array $header, array $claims, \OpenSSLAsymmetricKey $signingKey) : string{
		$jwtBody = JwtUtils::b64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)) . "." . JwtUtils::b64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR));

		openssl_sign(
			$jwtBody,
			$derSignature,
			$signingKey,
			self::SIGNATURE_ALGORITHM
		);

		$rawSignature = self::rawSignatureFromDer($derSignature);
		$jwtSig = self::b64UrlEncode($rawSignature);

		return "$jwtBody.$jwtSig";
	}

	public static function b64UrlEncode(string $str) : string{
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}

	public static function b64UrlDecode(string $str) : string{
		if(($len = strlen($str) % 4) !== 0){
			$str .= str_repeat('=', 4 - $len);
		}
		$decoded = base64_decode(strtr($str, '-_', '+/'), true);
		if($decoded === false){
			throw new JwtException("Malformed base64url encoded payload could not be decoded");
		}
		return $decoded;
	}

	public static function emitDerPublicKey(\OpenSSLAsymmetricKey $opensslKey) : string{
		$details = Utils::assumeNotFalse(openssl_pkey_get_details($opensslKey), "Failed to get details from OpenSSL key resource");
		/** @var string $pemKey */
		$pemKey = $details['key'];
		if(preg_match("@^-----BEGIN[A-Z\d ]+PUBLIC KEY-----\n([A-Za-z\d+/\n]+)\n-----END[A-Z\d ]+PUBLIC KEY-----\n$@", $pemKey, $matches) === 1){
			$derKey = base64_decode(str_replace("\n", "", $matches[1]), true);
			if($derKey !== false){
				return $derKey;
			}
		}
		throw new AssumptionFailedError("OpenSSL resource contains invalid public key");
	}

	public static function parseDerPublicKey(string $derKey) : \OpenSSLAsymmetricKey{
		$signingKeyOpenSSL = openssl_pkey_get_public(sprintf("-----BEGIN PUBLIC KEY-----\n%s\n-----END PUBLIC KEY-----\n", base64_encode($derKey)));
		if($signingKeyOpenSSL === false){
			throw new JwtException("OpenSSL failed to parse key: " . openssl_error_string());
		}
		$details = openssl_pkey_get_details($signingKeyOpenSSL);
		if($details === false){
			throw new JwtException("OpenSSL failed to get details from key: " . openssl_error_string());
		}
		if(!isset($details['ec']['curve_name'])){
			throw new JwtException("Expected an EC key");
		}
		$curve = $details['ec']['curve_name'];
		if($curve !== self::BEDROCK_SIGNING_KEY_CURVE_NAME){
			throw new JwtException("Key must belong to curve " . self::BEDROCK_SIGNING_KEY_CURVE_NAME . ", got $curve");
		}
		return $signingKeyOpenSSL;
	}
}
