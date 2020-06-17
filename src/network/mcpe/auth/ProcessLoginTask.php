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

use FG\ASN1\Exception\ParserException;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\types\login\JwtChainLinkBody;
use pocketmine\network\mcpe\protocol\types\login\JwtHeader;
use pocketmine\scheduler\AsyncTask;
use function base64_decode;
use function igbinary_serialize;
use function igbinary_unserialize;
use function time;

class ProcessLoginTask extends AsyncTask{
	private const TLS_KEY_ON_COMPLETION = "completion";

	public const MOJANG_ROOT_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAE8ELkixyLcwlZryUQcu1TvPOmI2B7vX83ndnWRUaXm74wFfa5f/lwQNTfrLVHa2PmenpGI6JhIMUJaWZrjmMj90NoKNFSNBuKdm8rYiXsfaz3K36x/1U26HpG0ZxK/V1V";

	private const CLOCK_DRIFT_MAX = 60;

	/** @var string */
	private $chain;
	/** @var string */
	private $clientDataJwt;

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

	/** @var PublicKeyInterface|null */
	private $clientPublicKey = null;

	/**
	 * @param string[] $chainJwts
	 * @phpstan-var \Closure(bool $isAuthenticated, bool $authRequired, ?string $error, ?PublicKeyInterface $clientPublicKey) : void $onCompletion
	 */
	public function __construct(array $chainJwts, string $clientDataJwt, bool $authRequired, \Closure $onCompletion){
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
		$this->chain = igbinary_serialize($chainJwts);
		$this->clientDataJwt = $clientDataJwt;
		$this->authRequired = $authRequired;
	}

	public function onRun() : void{
		try{
			$this->clientPublicKey = $this->validateChain();
			$this->error = null;
		}catch(VerifyLoginException $e){
			$this->error = $e->getMessage();
		}
	}

	private function validateChain() : PublicKeyInterface{
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

		return (new DerPublicKeySerializer())->parse(base64_decode($clientKey, true));
	}

	/**
	 * @throws VerifyLoginException if errors are encountered
	 */
	private function validateToken(string $jwt, ?string &$currentPublicKey, bool $first = false) : void{
		try{
			[$headersArray, $claimsArray, ] = JwtUtils::parse($jwt);
		}catch(JwtException $e){
			throw new VerifyLoginException("Failed to parse JWT: " . $e->getMessage(), 0, $e);
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bEnforceMapType = false;

		try{
			/** @var JwtHeader $headers */
			$headers = $mapper->map($headersArray, new JwtHeader());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid JWT header: " . $e->getMessage(), 0, $e);
		}

		if($currentPublicKey === null){
			if(!$first){
				throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.missingKey");
			}

			//First link, check that it is self-signed
			$currentPublicKey = $headers->x5u;
		}elseif($headers->x5u !== $currentPublicKey){
			//Fast path: if the header key doesn't match what we expected, the signature isn't going to validate anyway
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.badSignature");
		}

		$derPublicKeySerializer = new DerPublicKeySerializer();
		$rawPublicKey = base64_decode($currentPublicKey, true);
		if($rawPublicKey === false){
			throw new VerifyLoginException("Failed to decode base64'd public key");
		}
		try{
			$signingKey = $derPublicKeySerializer->parse($rawPublicKey);
		}catch(\RuntimeException | ParserException $e){
			throw new VerifyLoginException("Failed to parse DER public key: " . $e->getMessage(), 0, $e);
		}

		try{
			if(!JwtUtils::verify($jwt, $signingKey)){
				throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.badSignature");
			}
		}catch(JwtException $e){
			throw new VerifyLoginException($e->getMessage(), 0, $e);
		}

		if($currentPublicKey === self::MOJANG_ROOT_PUBLIC_KEY){
			$this->authenticated = true; //we're signed into xbox live
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
		$mapper->bExceptionOnMissingData = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;
		try{
			/** @var JwtChainLinkBody $claims */
			$claims = $mapper->map($claimsArray, new JwtChainLinkBody());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid chain link body: " . $e->getMessage(), 0, $e);
		}

		$time = time();
		if(isset($claims->nbf) and $claims->nbf > $time + self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.tooEarly");
		}

		if(isset($claims->exp) and $claims->exp < $time - self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.tooLate");
		}

		$currentPublicKey = $claims->identityPublicKey ?? null; //if there are further links, the next link should be signed with this
	}

	public function onCompletion() : void{
		/**
		 * @var \Closure $callback
		 * @phpstan-var \Closure(bool, bool, ?string, ?PublicKeyInterface) : void $callback
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);
		$callback($this->authenticated, $this->authRequired, $this->error, $this->clientPublicKey);
	}
}
