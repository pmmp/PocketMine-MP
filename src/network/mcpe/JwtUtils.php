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

use FG\ASN1\Exception\ParserException;
use FG\ASN1\Universal\Integer;
use FG\ASN1\Universal\Sequence;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function gmp_export;
use function gmp_import;
use function gmp_init;
use function gmp_strval;
use function is_array;
use function json_decode;
use function json_encode;
use function json_last_error_msg;
use function openssl_error_string;
use function openssl_pkey_get_details;
use function openssl_pkey_get_public;
use function openssl_sign;
use function openssl_verify;
use function preg_match;
use function rtrim;
use function sprintf;
use function str_pad;
use function str_repeat;
use function str_replace;
use function str_split;
use function strlen;
use function strtr;
use const GMP_BIG_ENDIAN;
use const GMP_MSW_FIRST;
use const JSON_THROW_ON_ERROR;
use const OPENSSL_ALGO_SHA384;
use const STR_PAD_LEFT;

final class JwtUtils{

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

	/**
	 * @throws JwtException
	 */
	public static function verify(string $jwt, \OpenSSLAsymmetricKey $signingKey) : bool{
		[$header, $body, $signature] = self::split($jwt);

		$plainSignature = self::b64UrlDecode($signature);
		if(strlen($plainSignature) !== 96){
			throw new JwtException("JWT signature has unexpected length, expected 96, got " . strlen($plainSignature));
		}

		[$rString, $sString] = str_split($plainSignature, 48);
		$convert = fn(string $str) => gmp_strval(gmp_import($str, 1, GMP_BIG_ENDIAN | GMP_MSW_FIRST), 10);

		$sequence = new Sequence(
			new Integer($convert($rString)),
			new Integer($convert($sString))
		);

		$v = openssl_verify(
			$header . '.' . $body,
			$sequence->getBinary(),
			$signingKey,
			OPENSSL_ALGO_SHA384
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
			$rawDerSig,
			$signingKey,
			OPENSSL_ALGO_SHA384
		);

		try{
			$asnObject = Sequence::fromBinary($rawDerSig);
		}catch(ParserException $e){
			throw new AssumptionFailedError("Failed to parse OpenSSL signature: " . $e->getMessage(), 0, $e);
		}
		if(count($asnObject) !== 2){
			throw new AssumptionFailedError("OpenSSL produced invalid signature, expected exactly 2 parts");
		}
		[$r, $s] = [$asnObject[0], $asnObject[1]];
		if(!($r instanceof Integer) || !($s instanceof Integer)){
			throw new AssumptionFailedError("OpenSSL produced invalid signature, expected 2 INTEGER parts");
		}
		$rString = $r->getContent();
		$sString = $s->getContent();

		$toBinary = fn($str) => str_pad(
			gmp_export(gmp_init($str, 10), 1, GMP_BIG_ENDIAN | GMP_MSW_FIRST),
			48,
			"\x00",
			STR_PAD_LEFT
		);
		$jwtSig = JwtUtils::b64UrlEncode($toBinary($rString) . $toBinary($sString));

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
		return $signingKeyOpenSSL;
	}
}
