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

use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\types\login\openid\api\AuthServiceKey;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\scheduler\AsyncPool;
use pocketmine\utils\AssumptionFailedError;
use function array_keys;
use function count;
use function implode;
use function time;

class AuthKeyProvider{
	private const ALLOWED_REFRESH_INTERVAL = 30 * 60; // 30 minutes

	private ?AuthKeyring $keyring = null;

	/** @phpstan-var PromiseResolver<AuthKeyring> */
	private ?PromiseResolver $resolver = null;

	private int $lastFetch = 0;

	public function __construct(
		private readonly \Logger $logger,
		private readonly AsyncPool $asyncPool,
		private readonly int $keyRefreshIntervalSeconds = self::ALLOWED_REFRESH_INTERVAL
	){}

	/**
	 * Fetches the key for the given key ID.
	 * The promise will be resolved with an array of [issuer, pemPublicKey].
	 *
	 * @phpstan-return Promise<array{string, string}>
	 */
	public function getKey(string $keyId) : Promise{
		/** @phpstan-var PromiseResolver<array{string, string}> $resolver */
		$resolver = new PromiseResolver();

		if(
			$this->keyring === null || //we haven't fetched keys yet
			($this->keyring->getKey($keyId) === null && $this->lastFetch < time() - $this->keyRefreshIntervalSeconds) //we don't recognise this one & keys might be outdated
		){
			//only refresh keys when we see one we don't recognise
			$this->fetchKeys()->onCompletion(
				onSuccess: fn(AuthKeyring $newKeyring) => $this->resolveKey($resolver, $newKeyring, $keyId),
				onFailure: $resolver->reject(...)
			);
		}else{
			$this->resolveKey($resolver, $this->keyring, $keyId);
		}

		return $resolver->getPromise();
	}

	/**
	 * @phpstan-param PromiseResolver<array{string, string}> $resolver
	 */
	private function resolveKey(PromiseResolver $resolver, AuthKeyring $keyring, string $keyId) : void{
		$key = $keyring->getKey($keyId);
		if($key === null){
			$this->logger->debug("Key $keyId not recognised!");
			$resolver->reject();
			return;
		}

		$this->logger->debug("Key $keyId found in keychain");
		$resolver->resolve([$keyring->getIssuer(), $key]);
	}

	/**
	 * @phpstan-param array<string, AuthServiceKey>|null $keys
	 * @phpstan-param string[]|null                      $errors
	 */
	private function onKeysFetched(?array $keys, string $issuer, ?array $errors) : void{
		$resolver = $this->resolver;
		if($resolver === null){
			throw new AssumptionFailedError("Not expecting this to be called without a resolver present");
		}
		if($errors !== null){
			$this->logger->error("The following errors occurred while fetching new keys:\n\t- " . implode("\n\t-", $errors));
			//we might've still succeeded in fetching keys even if there were errors, so don't return
		}

		if($keys === null){
			$this->logger->critical("Failed to fetch authentication keys from Mojang's API. Xbox players may not be able to authenticate!");
			$resolver->reject();
		}else{
			$pemKeys = [];
			foreach($keys as $keyModel){
				if($keyModel->use !== "sig" || $keyModel->kty !== "RSA"){
					$this->logger->error("Key ID $keyModel->kid doesn't have the expected properties: expected use=sig, kty=RSA, got use=$keyModel->use, kty=$keyModel->kty");
					continue;
				}
				$derKey = JwtUtils::rsaPublicKeyModExpToDer($keyModel->n, $keyModel->e);

				//make sure the key is valid
				try{
					JwtUtils::parseDerPublicKey($derKey);
				}catch(JwtException $e){
					$this->logger->error("Failed to parse RSA public key for key ID $keyModel->kid: " . $e->getMessage());
					$this->logger->logException($e);
					continue;
				}

				//retain PEM keys instead of OpenSSLAsymmetricKey since these are easier and cheaper to copy between threads
				$pemKeys[$keyModel->kid] = $derKey;
			}

			if(count($keys) === 0){
				$this->logger->critical("No valid authentication keys returned by Mojang's API. Xbox players may not be able to authenticate!");
				$resolver->reject();
			}else{
				$this->logger->info("Successfully fetched " . count($keys) . " new authentication keys from issuer $issuer, key IDs: " . implode(", ", array_keys($pemKeys)));
				$this->keyring = new AuthKeyring($issuer, $pemKeys);
				$this->lastFetch = time();
				$resolver->resolve($this->keyring);
			}
		}
	}

	/**
	 * @phpstan-return Promise<AuthKeyring>
	 */
	private function fetchKeys() : Promise{
		if($this->resolver !== null){
			$this->logger->debug("Key refresh was requested, but it's already in progress");
			return $this->resolver->getPromise();
		}

		$this->logger->notice("Fetching new authentication keys");

		/** @phpstan-var PromiseResolver<AuthKeyring> $resolver */
		$resolver = new PromiseResolver();
		$this->resolver = $resolver;
		//TODO: extract this so it can be polyfilled for unit testing
		$this->asyncPool->submitTask(new FetchAuthKeysTask($this->onKeysFetched(...)));
		return $this->resolver->getPromise();
	}
}
