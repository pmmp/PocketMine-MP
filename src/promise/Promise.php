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

namespace pocketmine\promise;

use pocketmine\utils\Utils;
use function count;
use function spl_object_id;

/**
 * @phpstan-template TValue
 */
final class Promise{
	/**
	 * @internal Do NOT call this directly; create a new Resolver and call Resolver->promise()
	 * @see PromiseResolver
	 * @phpstan-param PromiseSharedData<TValue> $shared
	 */
	public function __construct(private PromiseSharedData $shared){}

	/**
	 * @phpstan-param \Closure(TValue) : void $onSuccess
	 * @phpstan-param \Closure() : void $onFailure
	 */
	public function onCompletion(\Closure $onSuccess, \Closure $onFailure) : void{
		$state = $this->shared->state;
		if($state === true){
			$onSuccess($this->shared->result);
		}elseif($state === false){
			$onFailure();
		}else{
			$this->shared->onSuccess[spl_object_id($onSuccess)] = $onSuccess;
			$this->shared->onFailure[spl_object_id($onFailure)] = $onFailure;
		}
	}

	public function isResolved() : bool{
		//TODO: perhaps this should return true when rejected? currently there's no way to tell if a promise was
		//rejected or just hasn't been resolved yet
		return $this->shared->state === true;
	}

	/**
	 * Returns a promise that will resolve only once all the Promises in
	 * `$promises` have resolved. The resolution value of the returned promise
	 * will be an array containing the resolution values of each Promises in
	 * `$promises` indexed by the respective Promises' array keys.
	 *
	 * @param Promise[] $promises
	 *
	 * @phpstan-template TPromiseValue
	 * @phpstan-template TKey of array-key
	 * @phpstan-param array<TKey, Promise<TPromiseValue>> $promises
	 *
	 * @phpstan-return Promise<array<TKey, TPromiseValue>>
	 */
	public static function all(array $promises) : Promise{
		/** @phpstan-var PromiseResolver<array<TKey, TPromiseValue>> $resolver */
		$resolver = new PromiseResolver();
		if(count($promises) === 0){
			$resolver->resolve([]);
			return $resolver->getPromise();
		}
		$values = [];
		$toResolve = count($promises);
		$continue = true;

		foreach(Utils::promoteKeys($promises) as $key => $promise){
			$promise->onCompletion(
				function(mixed $value) use ($resolver, $key, $toResolve, &$values) : void{
					$values[$key] = $value;

					if(count($values) === $toResolve){
						$resolver->resolve($values);
					}
				},
				function() use ($resolver, &$continue) : void{
					if($continue){
						$continue = false;
						$resolver->reject();
					}
				}
			);

			if(!$continue){
				break;
			}
		}

		return $resolver->getPromise();
	}
}
