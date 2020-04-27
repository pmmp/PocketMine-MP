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

namespace pocketmine\network\mcpe\encryption;

use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;
use function base64_encode;
use function gmp_strval;
use function hex2bin;
use function json_encode;
use function openssl_digest;
use function openssl_sign;
use function rtrim;
use function str_pad;
use function strtr;

final class EncryptionUtils{

	private function __construct(){
		//NOOP
	}

	private static function b64UrlEncode(string $str) : string{
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}

	public static function generateSharedSecret(PrivateKeyInterface $localPriv, PublicKeyInterface $remotePub) : \GMP{
		return $localPriv->createExchange($remotePub)->calculateSharedKey();
	}

	public static function generateKey(\GMP $secret, string $salt) : string{
		return openssl_digest($salt . hex2bin(str_pad(gmp_strval($secret, 16), 96, "0", STR_PAD_LEFT)), 'sha256', true);
	}

	public static function generateServerHandshakeJwt(PrivateKeyInterface $serverPriv, string $salt) : string{
		$jwtBody = self::b64UrlEncode(json_encode([
					"x5u" => base64_encode((new DerPublicKeySerializer())->serialize($serverPriv->getPublicKey())),
					"alg" => "ES384"
				])
			) . "." . self::b64UrlEncode(json_encode([
					"salt" => base64_encode($salt)
				])
			);

		openssl_sign($jwtBody, $sig, (new PemPrivateKeySerializer(new DerPrivateKeySerializer()))->serialize($serverPriv), OPENSSL_ALGO_SHA384);

		$decodedSig = (new DerSignatureSerializer())->parse($sig);
		$jwtSig = self::b64UrlEncode(
			hex2bin(str_pad(gmp_strval($decodedSig->getR(), 16), 96, "0", STR_PAD_LEFT)) .
			hex2bin(str_pad(gmp_strval($decodedSig->getS(), 16), 96, "0", STR_PAD_LEFT))
		);

		return "$jwtBody.$jwtSig";
	}
}