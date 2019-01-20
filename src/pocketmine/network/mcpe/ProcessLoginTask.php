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
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use function assert;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function explode;
use function gmp_init;
use function gmp_strval;
use function hex2bin;
use function json_decode;
use function json_encode;
use function openssl_digest;
use function openssl_sign;
use function openssl_verify;
use function random_bytes;
use function rtrim;
use function str_pad;
use function str_repeat;
use function str_split;
use function strlen;
use function time;
use const OPENSSL_ALGO_SHA384;
use const STR_PAD_LEFT;

class ProcessLoginTask extends AsyncTask{

	public const MOJANG_ROOT_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAE8ELkixyLcwlZryUQcu1TvPOmI2B7vX83ndnWRUaXm74wFfa5f/lwQNTfrLVHa2PmenpGI6JhIMUJaWZrjmMj90NoKNFSNBuKdm8rYiXsfaz3K36x/1U26HpG0ZxK/V1V";

	private const CLOCK_DRIFT_MAX = 60;

	/** @var PrivateKeyInterface|null */
	private static $SERVER_PRIVATE_KEY = null;

	/** @var LoginPacket */
	private $packet;

	/**
	 * @var string|null
	 * Whether the keychain signatures were validated correctly. This will be set to an error message if any link in the
	 * keychain is invalid for whatever reason (bad signature, not in nbf-exp window, etc). If this is non-null, the
	 * keychain might have been tampered with. The player will always be disconnected if this is non-null.
	 */
	private $error = "Unknown";
	/**
	 * @var bool
	 * Whether the player is logged into Xbox Live. This is true if any link in the keychain is signed with the Mojang
	 * root public key.
	 */
	private $authenticated = false;
	/** @var bool */
	private $authRequired;

	/**
	 * @var bool
	 * Whether or not to enable encryption for the session that sent this login.
	 */
	private $useEncryption = true;

	/** @var PrivateKeyInterface|null */
	private $serverPrivateKey = null;

	/** @var string|null */
	private $aesKey = null;
	/** @var string|null */
	private $handshakeJwt = null;

	public function __construct(Player $player, LoginPacket $packet, bool $authRequired, bool $useEncryption = true){
		$this->storeLocal($player);
		$this->packet = $packet;
		$this->authRequired = $authRequired;
		$this->useEncryption = $useEncryption;
		if($useEncryption){
			if(self::$SERVER_PRIVATE_KEY === null){
				self::$SERVER_PRIVATE_KEY = EccFactory::getNistCurves()->generator384()->createPrivateKey();
			}

			$this->serverPrivateKey = self::$SERVER_PRIVATE_KEY;
		}
	}

	public function onRun() : void{
		try{
			$clientPub = $this->validateChain();
		}catch(VerifyLoginException $e){
			$this->error = $e->getMessage();
			return;
		}

		if($this->useEncryption){
			$serverPriv = $this->serverPrivateKey;
			$salt = random_bytes(16);
			$sharedSecret = $serverPriv->createExchange($clientPub)->calculateSharedKey();

			$this->aesKey = openssl_digest($salt . hex2bin(str_pad(gmp_strval($sharedSecret, 16), 96, "0", STR_PAD_LEFT)), 'sha256', true);
			$this->handshakeJwt = $this->generateServerHandshakeJwt($serverPriv, $salt);
		}

		$this->error = null;
	}

	private function validateChain() : PublicKeyInterface{
		$packet = $this->packet;

		$currentKey = null;
		$first = true;

		foreach($packet->chainDataJwt as $jwt){
			$this->validateToken($jwt, $currentKey, $first);
			if($first){
				$first = false;
			}
		}

		/** @var string $clientKey */
		$clientKey = $currentKey;

		$this->validateToken($packet->clientDataJwt, $currentKey);

		return (new DerPublicKeySerializer())->parse(base64_decode($clientKey, true));
	}

	/**
	 * @param string      $jwt
	 * @param null|string $currentPublicKey
	 * @param bool        $first
	 *
	 * @throws VerifyLoginException if errors are encountered
	 */
	private function validateToken(string $jwt, ?string &$currentPublicKey, bool $first = false) : void{
		[$headB64, $payloadB64, $sigB64] = explode('.', $jwt);

		if($currentPublicKey === null){
			if(!$first){
				throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.missingKey");
			}

			//First link, check that it is self-signed
			$headers = json_decode(self::b64UrlDecode($headB64), true);
			$currentPublicKey = $headers["x5u"];
		}

		$plainSignature = self::b64UrlDecode($sigB64);
		assert(strlen($plainSignature) === 96);
		[$rString, $sString] = str_split($plainSignature, 48);
		$sig = new Signature(gmp_init(bin2hex($rString), 16), gmp_init(bin2hex($sString), 16));

		$derSerializer = new DerPublicKeySerializer();
		$v = openssl_verify(
			"$headB64.$payloadB64",
			(new DerSignatureSerializer())->serialize($sig),
			(new PemPublicKeySerializer($derSerializer))->serialize($derSerializer->parse(base64_decode($currentPublicKey))),
			OPENSSL_ALGO_SHA384
		);

		if($v !== 1){
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.badSignature");
		}

		if($currentPublicKey === self::MOJANG_ROOT_PUBLIC_KEY){
			$this->authenticated = true; //we're signed into xbox live
		}

		$claims = json_decode(self::b64UrlDecode($payloadB64), true);

		$time = time();
		if(isset($claims["nbf"]) and $claims["nbf"] > $time + self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.tooEarly");
		}

		if(isset($claims["exp"]) and $claims["exp"] < $time - self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.tooLate");
		}

		$currentPublicKey = $claims["identityPublicKey"] ?? null; //if there are further links, the next link should be signed with this
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

	private static function b64UrlDecode(string $str) : string{
		if(($len = strlen($str) % 4) !== 0){
			$str .= str_repeat('=', 4 - $len);
		}
		return base64_decode(strtr($str, '-_', '+/'), true);
	}

	private static function b64UrlEncode(string $str) : string{
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}

	public function onCompletion() : void{
		/** @var Player $player */
		$player = $this->fetchLocal();
		if(!$player->isConnected()){
			$this->worker->getLogger()->error("Player " . $player->getName() . " was disconnected before their login could be verified");
		}elseif($player->setAuthenticationStatus($this->authenticated, $this->authRequired, $this->error)){
			if(!$this->useEncryption){
				$player->getNetworkSession()->onLoginSuccess();
			}else{
				$player->getNetworkSession()->enableEncryption($this->aesKey, $this->handshakeJwt);
			}
		}
	}
}
