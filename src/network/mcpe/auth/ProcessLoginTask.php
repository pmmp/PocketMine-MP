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
use pocketmine\network\mcpe\protocol\types\login\auth\AuthServiceKey;
use pocketmine\network\mcpe\protocol\types\login\JwtBodyRfc7519;
use pocketmine\network\mcpe\protocol\types\login\JwtChainLinkBody;
use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use function base64_decode;
use function time;

class ProcessLoginTask extends AsyncTask{
	private const TLS_KEY_ON_COMPLETION = "completion";

	public const MOJANG_AUDIENCE = "api://auth-minecraft-services/multiplayer";

	private const CLOCK_DRIFT_MAX = 60;

	/** @var NonThreadSafeValue<AuthServiceKey> */
	private NonThreadSafeValue $key;

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
	 * @phpstan-param \Closure(bool $isAuthenticated, bool $authRequired, Translatable|string|null $error, ?string $clientPublicKey) : void $onCompletion
	 */
	public function __construct(
		private string $jwt,
		private string $issuer,
		AuthServiceKey $key,
		private string $clientDataJwt,
		private bool $authRequired,
		\Closure $onCompletion
	){
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
		$this->key = new NonThreadSafeValue($key);
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
		$key = $this->key->deserialize();
		$currentKey = JwtUtils::createDerFromModulusAndExponent($key->n, $key->e);

		$this->validateToken($this->jwt, $currentKey, true);
		$this->validateToken($this->clientDataJwt, $currentKey);

		return $currentKey;
	}

	/**
	 * @throws VerifyLoginException if errors are encountered
	 */
	private function validateToken(string $jwt, string &$currentPublicKey, bool $first = false) : void{
		try{
			[, $claimsArray, ] = JwtUtils::parse($jwt);
		}catch(JwtException $e){
			throw new VerifyLoginException("Failed to parse JWT: " . $e->getMessage(), null, 0, $e);
		}

		try{
			$signingKeyOpenSSL = JwtUtils::parseDerPublicKey($currentPublicKey, !$first);
		}catch(JwtException $e){
			throw new VerifyLoginException("Invalid JWT public key: " . $e->getMessage(), null, 0, $e);
		}
		try{
			if(!JwtUtils::verify($jwt, $signingKeyOpenSSL, !$first)){
				throw new VerifyLoginException("Invalid JWT signature", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badSignature());
			}
		}catch(JwtException $e){
			throw new VerifyLoginException($e->getMessage(), null, 0, $e);
		}

		if($first){
			$this->authenticated = true; //we're signed into xbox live
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
		$mapper->bExceptionOnMissingData = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;
		try{
			/** @var JwtChainLinkBody|JwtBodyRfc7519 $claims */
			$claims = $mapper->map($claimsArray, $first ? new JwtChainLinkBody() : new JwtBodyRfc7519());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid chain link body: " . $e->getMessage(), null, 0, $e);
		}

		if(isset($claims->iss) && $claims->iss !== $this->issuer){
			throw new VerifyLoginException("Invalid JWT issuer", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badIssuer());
		}

		if(isset($claims->aud) && $claims->aud !== self::MOJANG_AUDIENCE){
			throw new VerifyLoginException("Invalid JWT audience", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badAudience());
		}

		$time = time();
		if(isset($claims->nbf) && $claims->nbf > $time + self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("JWT not yet valid", KnownTranslationFactory::pocketmine_disconnect_invalidSession_tooEarly());
		}

		if(isset($claims->exp) && $claims->exp < $time - self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("JWT expired", KnownTranslationFactory::pocketmine_disconnect_invalidSession_tooLate());
		}

		if($first){
			$identityPublicKey = base64_decode($claims->cpk, true);
			if($identityPublicKey === false){
				throw new VerifyLoginException("Invalid identityPublicKey: base64 error decoding");
			}
			try{
				//verify key format and parameters
				JwtUtils::parseDerPublicKey($identityPublicKey);
			}catch(JwtException $e){
				throw new VerifyLoginException("Invalid identityPublicKey: " . $e->getMessage(), null, 0, $e);
			}
			$currentPublicKey = $identityPublicKey;
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
