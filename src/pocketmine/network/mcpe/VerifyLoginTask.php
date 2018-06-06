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

use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class VerifyLoginTask extends AsyncTask{

	public const MOJANG_ROOT_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAE8ELkixyLcwlZryUQcu1TvPOmI2B7vX83ndnWRUaXm74wFfa5f/lwQNTfrLVHa2PmenpGI6JhIMUJaWZrjmMj90NoKNFSNBuKdm8rYiXsfaz3K36x/1U26HpG0ZxK/V1V";

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


	public function __construct(Player $player, LoginPacket $packet){
		$this->storeLocal($player);
		$this->packet = $packet;
	}

	public function onRun(){
		$packet = $this->packet; //Get it in a local variable to make sure it stays unserialized

		try{
			$currentKey = null;
			$first = true;

			foreach($packet->chainData["chain"] as $jwt){
				$this->validateToken($jwt, $currentKey, $first);
				$first = false;
			}

			$this->validateToken($packet->clientDataJwt, $currentKey);

			$this->error = null;
		}catch(VerifyLoginException $e){
			$this->error = $e->getMessage();
		}
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

		$headers = json_decode(base64_decode(strtr($headB64, '-_', '+/'), true), true);

		if($currentPublicKey === null){
			if(!$first){
				throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.missingKey");
			}

			//First link, check that it is self-signed
			$currentPublicKey = $headers["x5u"];
		}

		$plainSignature = base64_decode(strtr($sigB64, '-_', '+/'), true);

		//OpenSSL wants a DER-encoded signature, so we extract R and S from the plain signature and crudely serialize it.

		assert(strlen($plainSignature) === 96);

		[$rString, $sString] = str_split($plainSignature, 48);

		$rString = ltrim($rString, "\x00");
		if(ord($rString{0}) >= 128){ //Would be considered signed, pad it with an extra zero
			$rString = "\x00" . $rString;
		}

		$sString = ltrim($sString, "\x00");
		if(ord($sString{0}) >= 128){ //Would be considered signed, pad it with an extra zero
			$sString = "\x00" . $sString;
		}

		//0x02 = Integer ASN.1 tag
		$sequence = "\x02" . chr(strlen($rString)) . $rString . "\x02" . chr(strlen($sString)) . $sString;
		//0x30 = Sequence ASN.1 tag
		$derSignature = "\x30" . chr(strlen($sequence)) . $sequence;

		$v = openssl_verify("$headB64.$payloadB64", $derSignature, "-----BEGIN PUBLIC KEY-----\n" . wordwrap($currentPublicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----\n", OPENSSL_ALGO_SHA384);
		if($v !== 1){
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.badSignature");
		}

		if($currentPublicKey === self::MOJANG_ROOT_PUBLIC_KEY){
			$this->authenticated = true; //we're signed into xbox live
		}

		$claims = json_decode(base64_decode(strtr($payloadB64, '-_', '+/'), true), true);

		$time = time();
		if(isset($claims["nbf"]) and $claims["nbf"] > $time){
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.tooEarly");
		}

		if(isset($claims["exp"]) and $claims["exp"] < $time){
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.tooLate");
		}

		$currentPublicKey = $claims["identityPublicKey"] ?? null; //if there are further links, the next link should be signed with this
	}

	public function onCompletion(Server $server){
		/** @var Player $player */
		$player = $this->fetchLocal();
		if($player->isClosed()){
			$server->getLogger()->error("Player " . $player->getName() . " was disconnected before their login could be verified");
		}else{
			$player->onVerifyCompleted($this->packet, $this->error, $this->authenticated);
		}
	}

}
