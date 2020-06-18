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

use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Crypto\Signature\Signature;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;
use pocketmine\utils\AssumptionFailedError;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function count;
use function explode;
use function gmp_init;
use function gmp_strval;
use function hex2bin;
use function is_array;
use function json_decode;
use function json_encode;
use function json_last_error_msg;
use function openssl_error_string;
use function openssl_sign;
use function openssl_verify;
use function rtrim;
use function str_pad;
use function str_repeat;
use function str_split;
use function strlen;
use function strtr;
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
	public static function verify(string $jwt, PublicKeyInterface $signingKey) : bool{
		[$header, $body, $signature] = self::split($jwt);

		$plainSignature = self::b64UrlDecode($signature);
		if(strlen($plainSignature) !== 96){
			throw new JwtException("JWT signature has unexpected length, expected 96, got " . strlen($plainSignature));
		}

		[$rString, $sString] = str_split($plainSignature, 48);
		$sig = new Signature(gmp_init(bin2hex($rString), 16), gmp_init(bin2hex($sString), 16));

		$v = openssl_verify(
			$header . '.' . $body,
			(new DerSignatureSerializer())->serialize($sig),
			(new PemPublicKeySerializer(new DerPublicKeySerializer()))->serialize($signingKey),
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
	public static function create(array $header, array $claims, PrivateKeyInterface $signingKey) : string{
		$jwtBody = JwtUtils::b64UrlEncode(json_encode($header)) . "." . JwtUtils::b64UrlEncode(json_encode($claims));

		openssl_sign(
			$jwtBody,
			$sig,
			(new PemPrivateKeySerializer(new DerPrivateKeySerializer()))->serialize($signingKey),
			OPENSSL_ALGO_SHA384
		);

		$decodedSig = (new DerSignatureSerializer())->parse($sig);
		$jwtSig = JwtUtils::b64UrlEncode(
			hex2bin(str_pad(gmp_strval($decodedSig->getR(), 16), 96, "0", STR_PAD_LEFT)) .
			hex2bin(str_pad(gmp_strval($decodedSig->getS(), 16), 96, "0", STR_PAD_LEFT))
		);

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
}
