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
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\scheduler\AsyncTask;
use function base64_encode;
use function gmp_strval;
use function hex2bin;
use function json_encode;
use function openssl_digest;
use function openssl_sign;
use function random_bytes;
use function rtrim;
use function str_pad;
use function strtr;
use const OPENSSL_ALGO_SHA384;
use const STR_PAD_LEFT;

class PrepareEncryptionTask extends AsyncTask{

	private const TLS_KEY_SESSION = "session";

	/** @var PrivateKeyInterface|null */
	private static $SERVER_PRIVATE_KEY = null;

	/** @var PrivateKeyInterface|null */
	private $serverPrivateKey = null;

	/** @var string|null */
	private $aesKey = null;
	/** @var string|null */
	private $handshakeJwt = null;
	/** @var PublicKeyInterface */
	private $clientPub;

	public function __construct(NetworkSession $session, PublicKeyInterface $clientPub){
		if(self::$SERVER_PRIVATE_KEY === null){
			self::$SERVER_PRIVATE_KEY = EccFactory::getNistCurves()->generator384()->createPrivateKey();
		}

		$this->serverPrivateKey = self::$SERVER_PRIVATE_KEY;
		$this->clientPub = $clientPub;
		$this->storeLocal(self::TLS_KEY_SESSION, $session);
	}

	public function onRun() : void{
		$serverPriv = $this->serverPrivateKey;
		$salt = random_bytes(16);
		$sharedSecret = $serverPriv->createExchange($this->clientPub)->calculateSharedKey();

		$this->aesKey = openssl_digest($salt . hex2bin(str_pad(gmp_strval($sharedSecret, 16), 96, "0", STR_PAD_LEFT)), 'sha256', true);
		$this->handshakeJwt = $this->generateServerHandshakeJwt($serverPriv, $salt);
	}

	private function generateServerHandshakeJwt(PrivateKeyInterface $serverPriv, string $salt) : string{
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

	private static function b64UrlEncode(string $str) : string{
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}

	public function onCompletion() : void{
		/** @var NetworkSession $session */
		$session = $this->fetchLocal(self::TLS_KEY_SESSION);
		$session->enableEncryption($this->aesKey, $this->handshakeJwt);
	}
}
