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

use pocketmine\lang\Translatable;
use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use function base64_decode;

class ProcessOpenIdLoginTask extends AsyncTask{
	private const TLS_KEY_ON_COMPLETION = "completion";

	public const MOJANG_AUDIENCE = "api://auth-minecraft-services/multiplayer";

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
	private ?string $clientPublicKeyDer = null;

	/**
	 * @phpstan-param \Closure(bool $isAuthenticated, bool $authRequired, Translatable|string|null $error, ?string $clientPublicKey) : void $onCompletion
	 */
	public function __construct(
		private string $jwt,
		private string $issuer,
		private string $mojangPublicKeyDer,
		private string $clientDataJwt,
		private bool $authRequired,
		\Closure $onCompletion
	){
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
	}

	public function onRun() : void{
		try{
			$this->clientPublicKeyDer = $this->validateChain();
			$this->error = null;
		}catch(VerifyLoginException $e){
			$disconnectMessage = $e->getDisconnectMessage();
			$this->error = $disconnectMessage instanceof Translatable ? new NonThreadSafeValue($disconnectMessage) : $disconnectMessage;
		}
	}

	private function validateChain() : string{
		$claims = AuthJwtHelper::validateOpenIdAuthToken($this->jwt, $this->mojangPublicKeyDer, issuer: $this->issuer, audience: self::MOJANG_AUDIENCE);
		//validateToken will throw if the JWT is not valid
		$this->authenticated = true;

		$clientDerKey = base64_decode($claims->cpk, strict: true);
		if($clientDerKey === false){
			throw new VerifyLoginException("Invalid client public key: base64 error decoding");
		}
		//no further validation needed - OpenSSL will bail if the key is invalid
		AuthJwtHelper::validateSelfSignedToken($this->clientDataJwt, $clientDerKey);

		return $clientDerKey;
	}

	public function onCompletion() : void{
		/**
		 * @var \Closure $callback
		 * @phpstan-var \Closure(bool, bool, Translatable|string|null, ?string) : void $callback
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);
		$callback($this->authenticated, $this->authRequired, $this->error instanceof NonThreadSafeValue ? $this->error->deserialize() : $this->error, $this->clientPublicKeyDer);
	}
}
