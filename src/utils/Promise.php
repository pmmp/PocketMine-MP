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

namespace pocketmine\utils;

use function spl_object_id;

/**
 * @phpstan-template TValue
 */
final class Promise{
	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure(TValue) : void>
	 */
	private array $onSuccess = [];

	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure() : void>
	 */
	private array $onFailure = [];

	private bool $resolved = false;

	/**
	 * @var mixed
	 * @phpstan-var TValue|null
	 */
	private $result = null;

	/**
	 * @phpstan-param \Closure(TValue) : void $onSuccess
	 * @phpstan-param \Closure() : void $onFailure
	 */
	public function onCompletion(\Closure $onSuccess, \Closure $onFailure) : void{
		if($this->resolved){
			$this->result === null ? $onFailure() : $onSuccess($this->result);
		}else{
			$this->onSuccess[spl_object_id($onSuccess)] = $onSuccess;
			$this->onFailure[spl_object_id($onFailure)] = $onFailure;
		}
	}

	/**
	 * @param mixed $value
	 * @phpstan-param TValue $value
	 */
	public function resolve($value) : void{
		if($this->resolved){
			throw new \InvalidStateException("Promise has already been resolved/rejected");
		}
		$this->resolved = true;
		$this->result = $value;
		foreach($this->onSuccess as $c){
			$c($value);
		}
		$this->onSuccess = [];
		$this->onFailure = [];
	}

	public function reject() : void{
		if($this->resolved){
			throw new \InvalidStateException("Promise has already been resolved/rejected");
		}
		$this->resolved = true;
		foreach($this->onFailure as $c){
			$c();
		}
		$this->onSuccess = [];
		$this->onFailure = [];
	}
}
