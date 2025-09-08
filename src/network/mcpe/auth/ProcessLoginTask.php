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
	private ?string $clientPublicKeyPem = null;

	/**
	 * @phpstan-param \Closure(bool $isAuthenticated, bool $authRequired, Translatable|string|null $error, ?string $clientPublicKey) : void $onCompletion
	 */
	public function __construct(
		private string $jwt,
		private string $issuer,
		private string $mojangPublicKeyPem,
		private string $clientDataJwt,
		private bool $authRequired,
		\Closure $onCompletion
	){
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
	}

	public function onRun() : void{
		try{
			$this->clientPublicKeyPem = $this->validateChain();
			$this->error = null;
		}catch(VerifyLoginException $e){
			$disconnectMessage = $e->getDisconnectMessage();
			$this->error = $disconnectMessage instanceof Translatable ? new NonThreadSafeValue($disconnectMessage) : $disconnectMessage;
		}
	}

	private function validateChain() : string{
		$claims = $this->validateToken($this->jwt, $this->mojangPublicKeyPem, isEcKey: false, bodyClass: JwtChainLinkBody::class);
		//validateToken will throw if the JWT is not valid
		$this->authenticated = true;

		$clientDerKey = base64_decode($claims->cpk, strict: true);
		if($clientDerKey === false){
			throw new VerifyLoginException("Invalid client public key: base64 error decoding");
		}
		//no further validation needed - OpenSSL will bail if the key is invalid
		$clientPublicKeyPem = JwtUtils::derPublicKeyToPem($clientDerKey);
		$this->validateToken($this->clientDataJwt, $clientPublicKeyPem, isEcKey: true, bodyClass: JwtBodyRfc7519::class);

		return $clientPublicKeyPem;
	}

	/**
	 * @phpstan-template TBody of JwtBodyRfc7519
	 * @phpstan-param class-string<TBody> $bodyClass
	 * @phpstan-return TBody
	 *
	 * @throws VerifyLoginException if errors are encountered
	 */
	private function validateToken(string $jwt, string $signingKeyPem, bool $isEcKey, string $bodyClass) : JwtBodyRfc7519{
		try{
			[, $claimsArray, ] = JwtUtils::parse($jwt);
		}catch(JwtException $e){
			throw new VerifyLoginException("Failed to parse JWT: " . $e->getMessage(), null, 0, $e);
		}

		try{
			if(!JwtUtils::verify($jwt, $signingKeyPem, $isEcKey)){
				throw new VerifyLoginException("Invalid JWT signature", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badSignature());
			}
		}catch(JwtException $e){
			throw new VerifyLoginException($e->getMessage(), null, 0, $e);
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
		$mapper->bExceptionOnMissingData = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;
		try{
			//nasty dynamic new for JsonMapper
			$claims = $mapper->map($claimsArray, new $bodyClass());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid chain link body: " . $e->getMessage(), null, 0, $e);
		}

		if(isset($claims->iss) && $claims->iss !== $this->issuer){
			throw new VerifyLoginException("Invalid JWT issuer");
		}

		if(isset($claims->aud) && $claims->aud !== self::MOJANG_AUDIENCE){
			throw new VerifyLoginException("Invalid JWT audience");
		}

		$time = time();
		if(isset($claims->nbf) && $claims->nbf > $time + self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("JWT not yet valid", KnownTranslationFactory::pocketmine_disconnect_invalidSession_tooEarly());
		}

		if(isset($claims->exp) && $claims->exp < $time - self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("JWT expired", KnownTranslationFactory::pocketmine_disconnect_invalidSession_tooLate());
		}

		return $claims;
	}

	public function onCompletion() : void{
		/**
		 * @var \Closure $callback
		 * @phpstan-var \Closure(bool, bool, Translatable|string|null, ?string) : void $callback
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);
		$callback($this->authenticated, $this->authRequired, $this->error instanceof NonThreadSafeValue ? $this->error->deserialize() : $this->error, $this->clientPublicKeyPem);
	}
}
