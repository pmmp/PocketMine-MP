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

/**
 * @phpstan-template TValue
 */
final class PromiseResolver{
	/** @phpstan-var PromiseSharedData<TValue> */
	private PromiseSharedData $shared;
	/** @phpstan-var Promise<TValue> */
	private Promise $promise;

	public function __construct(){
		$this->shared = new PromiseSharedData();
		$this->promise = new Promise($this->shared);
	}

	/**
	 * @param mixed $value
	 * @phpstan-param TValue $value
	 */
	public function resolve($value) : void{
		if($this->shared->resolved){
			throw new \LogicException("Promise has already been resolved/rejected");
		}
		$this->shared->resolved = true;
		$this->shared->result = $value;
		foreach($this->shared->onSuccess as $c){
			$c($value);
		}
		$this->shared->onSuccess = [];
		$this->shared->onFailure = [];
	}

	public function reject() : void{
		if($this->shared->resolved){
			throw new \LogicException("Promise has already been resolved/rejected");
		}
		$this->shared->resolved = true;
		foreach($this->shared->onFailure as $c){
			$c();
		}
		$this->shared->onSuccess = [];
		$this->shared->onFailure = [];
	}

	/**
	 * @phpstan-return Promise<TValue>
	 */
	public function getPromise() : Promise{
		return $this->promise;
	}
}
