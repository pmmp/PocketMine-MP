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

use pocketmine\network\mcpe\JwtUtils;
use function base64_encode;
use function bin2hex;
use function gmp_init;
use function gmp_strval;
use function hex2bin;
use function openssl_digest;
use function openssl_error_string;
use function openssl_pkey_derive;
use function str_pad;

final class EncryptionUtils{

	private function __construct(){
		//NOOP
	}

	public static function generateSharedSecret(\OpenSSLAsymmetricKey $localPriv, \OpenSSLAsymmetricKey $remotePub) : \GMP{
		$hexSecret = openssl_pkey_derive($remotePub, $localPriv, 48);
		if($hexSecret === false){
			throw new \InvalidArgumentException("Failed to derive shared secret: " . openssl_error_string());
		}
		return gmp_init(bin2hex($hexSecret), 16);
	}

	public static function generateKey(\GMP $secret, string $salt) : string{
		return openssl_digest($salt . hex2bin(str_pad(gmp_strval($secret, 16), 96, "0", STR_PAD_LEFT)), 'sha256', true);
	}

	public static function generateServerHandshakeJwt(\OpenSSLAsymmetricKey $serverPriv, string $salt) : string{
		$derPublicKey = JwtUtils::emitDerPublicKey($serverPriv);
		return JwtUtils::create(
			[
				"x5u" => base64_encode($derPublicKey),
				"alg" => "ES384"
			],
			[
				"salt" => base64_encode($salt)
			],
			$serverPriv
		);
	}
}
