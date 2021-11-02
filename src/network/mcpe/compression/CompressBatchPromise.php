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

namespace pocketmine\network\mcpe\compression;

use pocketmine\utils\Utils;
use function array_push;

class CompressBatchPromise{
	/**
	 * @var \Closure[]
	 * @phpstan-var (\Closure(self) : void)[]
	 */
	private $callbacks = [];

	/** @var string|null */
	private $result = null;

	/** @var bool */
	private $cancelled = false;

	/**
	 * @phpstan-param \Closure(self) : void ...$callbacks
	 */
	public function onResolve(\Closure ...$callbacks) : void{
		$this->checkCancelled();
		foreach($callbacks as $callback){
			Utils::validateCallableSignature(function(CompressBatchPromise $promise) : void{}, $callback);
		}
		if($this->result !== null){
			foreach($callbacks as $callback){
				$callback($this);
			}
		}else{
			array_push($this->callbacks, ...$callbacks);
		}
	}

	public function resolve(string $result) : void{
		if(!$this->cancelled){
			if($this->result !== null){
				throw new \LogicException("Cannot resolve promise more than once");
			}
			$this->result = $result;
			foreach($this->callbacks as $callback){
				$callback($this);
			}
			$this->callbacks = [];
		}
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return (\Closure(self) : void)[]
	 */
	public function getResolveCallbacks() : array{
		return $this->callbacks;
	}

	public function getResult() : string{
		$this->checkCancelled();
		if($this->result === null){
			throw new \LogicException("Promise has not yet been resolved");
		}
		return $this->result;
	}

	public function hasResult() : bool{
		return $this->result !== null;
	}

	public function isCancelled() : bool{
		return $this->cancelled;
	}

	public function cancel() : void{
		if($this->hasResult()){
			throw new \LogicException("Cannot cancel a resolved promise");
		}
		$this->cancelled = true;
	}

	private function checkCancelled() : void{
		if($this->cancelled){
			throw new \InvalidArgumentException("Promise has been cancelled");
		}
	}
}
