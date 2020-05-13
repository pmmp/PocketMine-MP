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
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use pocketmine\network\mcpe\JwtUtils;
use function base64_encode;
use function gmp_strval;
use function hex2bin;
use function openssl_digest;
use function str_pad;

final class EncryptionUtils{

	private function __construct(){
		//NOOP
	}

	public static function generateSharedSecret(PrivateKeyInterface $localPriv, PublicKeyInterface $remotePub) : \GMP{
		return $localPriv->createExchange($remotePub)->calculateSharedKey();
	}

	public static function generateKey(\GMP $secret, string $salt) : string{
		return openssl_digest($salt . hex2bin(str_pad(gmp_strval($secret, 16), 96, "0", STR_PAD_LEFT)), 'sha256', true);
	}

	public static function generateServerHandshakeJwt(PrivateKeyInterface $serverPriv, string $salt) : string{
		return JwtUtils::create(
			[
				"x5u" => base64_encode((new DerPublicKeySerializer())->serialize($serverPriv->getPublicKey())),
				"alg" => "ES384"
			],
			[
				"salt" => base64_encode($salt)
			],
			$serverPriv
		);
	}
}
