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

namespace pocketmine\network\mcpe\auth;

use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\types\login\JwtChainLinkBody;
use pocketmine\network\mcpe\protocol\types\login\JwtHeader;
use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use function base64_decode;
use function igbinary_serialize;
use function igbinary_unserialize;
use function time;

class ProcessLoginTask extends AsyncTask{
	private const TLS_KEY_ON_COMPLETION = "completion";

	/**
	 * New Mojang root auth key. Mojang notified third-party developers of this change prior to the release of 1.20.0.
	 * Expectations were that this would be used starting a "couple of weeks" after the release, but as of 2023-07-01,
	 * it has not yet been deployed.
	 */
	public const MOJANG_ROOT_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAECRXueJeTDqNRRgJi/vlRufByu/2G0i2Ebt6YMar5QX/R0DIIyrJMcUpruK4QveTfJSTp3Shlq4Gk34cD/4GUWwkv0DVuzeuB+tXija7HBxii03NHDbPAD0AKnLr2wdAp";

	private const CLOCK_DRIFT_MAX = 60;

	private string $chain;

	/**
	 * Whether the keychain signatures were validated correctly. This will be set to an error message if any link in the
	 * keychain is invalid for whatever reason (bad signature, not in nbf-exp window, etc). If this is non-null, the
	 * keychain might have been tampered with. The player will always be disconnected if this is non-null.
	 *
	 * @phpstan-var NonThreadSafeValue<Translatable>|string|null
	 */
	private NonThreadSafeValue|string|null $error = "Unknown";
	/**
	 * Whether the player is logged into Xbox Live. This is true if any link in the keychain is signed with the Mojang
	 * root public key.
	 */
	private bool $authenticated = false;
	private ?string $clientPublicKey = null;

	/**
	 * @param string[] $chainJwts
	 * @phpstan-param \Closure(bool $isAuthenticated, bool $authRequired, Translatable|string|null $error, ?string $clientPublicKey) : void $onCompletion
	 */
	public function __construct(
		array $chainJwts,
		private string $clientDataJwt,
		private bool $authRequired,
		\Closure $onCompletion
	){
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
		$this->chain = igbinary_serialize($chainJwts);
	}

	public function onRun() : void{
		try{
			$this->clientPublicKey = $this->validateChain();
			$this->error = null;
		}catch(VerifyLoginException $e){
			$disconnectMessage = $e->getDisconnectMessage();
			$this->error = $disconnectMessage instanceof Translatable ? new NonThreadSafeValue($disconnectMessage) : $disconnectMessage;
		}
	}

	private function validateChain() : string{
		/** @var string[] $chain */
		$chain = igbinary_unserialize($this->chain);

		$currentKey = null;
		$first = true;

		foreach($chain as $jwt){
			$this->validateToken($jwt, $currentKey, $first);
			if($first){
				$first = false;
			}
		}

		/** @var string $clientKey */
		$clientKey = $currentKey;

		$this->validateToken($this->clientDataJwt, $currentKey);

		return $clientKey;
	}

	/**
	 * @throws VerifyLoginException if errors are encountered
	 */
	private function validateToken(string $jwt, ?string &$currentPublicKey, bool $first = false) : void{
		try{
			[$headersArray, $claimsArray, ] = JwtUtils::parse($jwt);
		}catch(JwtException $e){
			throw new VerifyLoginException("Failed to parse JWT: " . $e->getMessage(), null, 0, $e);
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;

		try{
			/** @var JwtHeader $headers */
			$headers = $mapper->map($headersArray, new JwtHeader());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid JWT header: " . $e->getMessage(), null, 0, $e);
		}

		$headerDerKey = base64_decode($headers->x5u, true);
		if($headerDerKey === false){
			throw new VerifyLoginException("Invalid JWT public key: base64 decoding error decoding x5u");
		}

		if($currentPublicKey === null){
			if(!$first){
				throw new VerifyLoginException("Missing JWT public key", KnownTranslationFactory::pocketmine_disconnect_invalidSession_missingKey());
			}
		}elseif($headerDerKey !== $currentPublicKey){
			//Fast path: if the header key doesn't match what we expected, the signature isn't going to validate anyway
			throw new VerifyLoginException("Invalid JWT signature", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badSignature());
		}

		try{
			$signingKeyOpenSSL = JwtUtils::parseDerPublicKey($headerDerKey);
		}catch(JwtException $e){
			throw new VerifyLoginException("Invalid JWT public key: " . $e->getMessage(), null, 0, $e);
		}
		try{
			if(!JwtUtils::verify($jwt, $signingKeyOpenSSL)){
				throw new VerifyLoginException("Invalid JWT signature", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badSignature());
			}
		}catch(JwtException $e){
			throw new VerifyLoginException($e->getMessage(), null, 0, $e);
		}

		if($headers->x5u === self::MOJANG_ROOT_PUBLIC_KEY){
			$this->authenticated = true; //we're signed into xbox live
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
		$mapper->bExceptionOnMissingData = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;
		try{
			/** @var JwtChainLinkBody $claims */
			$claims = $mapper->map($claimsArray, new JwtChainLinkBody());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid chain link body: " . $e->getMessage(), null, 0, $e);
		}

		$time = time();
		if(isset($claims->nbf) && $claims->nbf > $time + self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("JWT not yet valid", KnownTranslationFactory::pocketmine_disconnect_invalidSession_tooEarly());
		}

		if(isset($claims->exp) && $claims->exp < $time - self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("JWT expired", KnownTranslationFactory::pocketmine_disconnect_invalidSession_tooLate());
		}

		if(isset($claims->identityPublicKey)){
			$identityPublicKey = base64_decode($claims->identityPublicKey, true);
			if($identityPublicKey === false){
				throw new VerifyLoginException("Invalid identityPublicKey: base64 error decoding");
			}
			try{
				//verify key format and parameters
				JwtUtils::parseDerPublicKey($identityPublicKey);
			}catch(JwtException $e){
				throw new VerifyLoginException("Invalid identityPublicKey: " . $e->getMessage(), null, 0, $e);
			}
			$currentPublicKey = $identityPublicKey; //if there are further links, the next link should be signed with this
		}
	}

	public function onCompletion() : void{
		/**
		 * @var \Closure $callback
		 * @phpstan-var \Closure(bool, bool, Translatable|string|null, ?string) : void $callback
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);
		$callback($this->authenticated, $this->authRequired, $this->error instanceof NonThreadSafeValue ? $this->error->deserialize() : $this->error, $this->clientPublicKey);
	}
}
