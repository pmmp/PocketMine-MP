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

use pocketmine\network\mcpe\protocol\types\login\auth\AuthServiceKey;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use function time;

class AuthKeyProvider{
	private const ALLOWED_REFRESH_INTERVAL = 30 * 60; // 30 minutes

	/** @phpstan-var array<string, AuthServiceKey> */
	private array $keys = [];
	private string $issuer;

	/** @phpstan-var PromiseResolver<null> */
	private PromiseResolver $resolver;

	private int $lastFetch;

	public function __construct(private readonly Server $server){
		$this->fetchKeys();
	}

	public function getIssuer() : string{
		return $this->issuer;
	}

	/**
	 * @phpstan-return Promise<AuthServiceKey>
	 */
	public function getKey(string $keyId) : Promise{
		/** @phpstan-var PromiseResolver<AuthServiceKey> $resolver */
		$resolver = new PromiseResolver();

		if(isset($this->keys[$keyId])){
			$resolver->resolve($this->keys[$keyId]);
		}else{
			$this->onKeyNotFound($keyId, $resolver);
		}

		return $resolver->getPromise();
	}

	/**
	 * @phpstan-param PromiseResolver<AuthServiceKey> $resolver
	 */
	private function onKeyNotFound(string $keyId, PromiseResolver $resolver) : void{
		if($this->canRefreshKeys()){
			$this->fetchKeys();
		}

		$this->resolver->getPromise()->onCompletion(function() use ($keyId, $resolver) : void{
			if(isset($this->keys[$keyId])){
				$resolver->resolve($this->keys[$keyId]);
			}else{
				$resolver->reject();
			}
		}, function() use ($resolver) : void{
			$resolver->reject();
		});
	}

	/**
	 * @phpstan-param array<string, AuthServiceKey>|null $keys
	 * @phpstan-param string[]|null $errors
	 */
	private function onKeysFetched(?array $keys, string $issuer, ?array $errors) : void{
		if ($errors !== null){
			foreach($errors as $error){
				$this->server->getLogger()->error($error);
			}
		}

		$this->issuer = $issuer;

		if ($keys === null){
			$this->server->getLogger()->error("Failed to fetch authentication keys. Authentication will not be possible.");
			$this->resolver->reject();
		} else {
			$this->keys = $keys;
			$this->resolver->resolve(null);
		}
	}

	private function fetchKeys() : void{
		$this->lastFetch = time();
		$this->resolver = new PromiseResolver();
		$this->server->getAsyncPool()->submitTask(new FetchAuthKeysTask($this->onKeysFetched(...)));
	}

	private function canRefreshKeys() : bool{
		return time() - $this->lastFetch >= self::ALLOWED_REFRESH_INTERVAL;
	}
}
