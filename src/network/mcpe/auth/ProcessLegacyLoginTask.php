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
use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\utils\AssumptionFailedError;
use function base64_decode;
use function igbinary_serialize;
use function igbinary_unserialize;

class ProcessLegacyLoginTask extends AsyncTask{
	private const TLS_KEY_ON_COMPLETION = "completion";

	/**
	 * New Mojang root auth key. Mojang notified third-party developers of this change prior to the release of 1.20.0.
	 * Expectations were that this would be used starting a "couple of weeks" after the release, but as of 2023-07-01,
	 * it has not yet been deployed.
	 */
	public const LEGACY_MOJANG_ROOT_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAECRXueJeTDqNRRgJi/vlRufByu/2G0i2Ebt6YMar5QX/R0DIIyrJMcUpruK4QveTfJSTp3Shlq4Gk34cD/4GUWwkv0DVuzeuB+tXija7HBxii03NHDbPAD0AKnLr2wdAp";

	private string $chain;

	/**
	 * Whether the keychain signatures were validated correctly. This will be set to an error message if any link in the
	 * keychain is invalid for whatever reason (bad signature, not in nbf-exp window, etc). If this is non-null, the
	 * keychain might have been tampered with. The player will always be disconnected if this is non-null.
	 *
	 * @phpstan-var NonThreadSafeValue<Translatable>|string|null
	 */
	private NonThreadSafeValue|string|null $error = "Unknown";
	/** Whether the player has a certificate chain link signed by the given root public key. */
	private bool $authenticated = false;
	private ?string $clientPublicKeyDer = null;

	/**
	 * @param string[] $chainJwts
	 * @phpstan-param \Closure(bool $isAuthenticated, bool $authRequired, Translatable|string|null $error, ?string $clientPublicKey) : void $onCompletion
	 */
	public function __construct(
		array $chainJwts,
		private string $clientDataJwt,
		private ?string $rootAuthKeyDer,
		private bool $authRequired,
		\Closure $onCompletion
	){
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
		$this->chain = igbinary_serialize($chainJwts) ?? throw new AssumptionFailedError("This should never fail");
	}

	public function onRun() : void{
		try{
			$this->clientPublicKeyDer = $this->validateChain();
			AuthJwtHelper::validateSelfSignedToken($this->clientDataJwt, $this->clientPublicKeyDer);
			$this->error = null;
		}catch(VerifyLoginException $e){
			$disconnectMessage = $e->getDisconnectMessage();
			$this->error = $disconnectMessage instanceof Translatable ? new NonThreadSafeValue($disconnectMessage) : $disconnectMessage;
		}
	}

	private function validateChain() : string{
		/** @var string[] $chain */
		$chain = igbinary_unserialize($this->chain);

		$identityPublicKeyDer = null;

		foreach($chain as $jwt){
			$claims = AuthJwtHelper::validateLegacyAuthToken($jwt, $identityPublicKeyDer);
			if($this->rootAuthKeyDer !== null && $identityPublicKeyDer === $this->rootAuthKeyDer){
				$this->authenticated = true; //we're signed into xbox live, according to this root key
			}
			if(!isset($claims->identityPublicKey)){
				throw new VerifyLoginException("Missing identityPublicKey in chain link", KnownTranslationFactory::pocketmine_disconnect_invalidSession_missingKey());
			}
			$identityPublicKey = base64_decode($claims->identityPublicKey, true);
			if($identityPublicKey === false){
				throw new VerifyLoginException("Invalid identityPublicKey: base64 error decoding");
			}
			$identityPublicKeyDer = $identityPublicKey;
		}

		if($identityPublicKeyDer === null){
			throw new VerifyLoginException("No authentication chain links provided");
		}

		return $identityPublicKeyDer;
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
