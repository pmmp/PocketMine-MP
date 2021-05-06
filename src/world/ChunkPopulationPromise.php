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

namespace pocketmine\world;

use function spl_object_id;

final class ChunkPopulationPromise{
	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure() : void>
	 */
	private array $onSuccess = [];
	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure() : void>
	 */
	private array $onFailure = [];

	private ?bool $success = null;

	/**
	 * @phpstan-param \Closure() : void $onSuccess
	 * @phpstan-param \Closure() : void $onFailure
	 */
	public function onCompletion(\Closure $onSuccess, \Closure $onFailure) : void{
		if($this->success !== null){
			$this->success ? $onSuccess() : $onFailure();
		}else{
			$this->onSuccess[spl_object_id($onSuccess)] = $onSuccess;
			$this->onFailure[spl_object_id($onFailure)] = $onFailure;
		}
	}

	public function resolve() : void{
		$this->success = true;
		foreach($this->onSuccess as $callback){
			$callback();
		}
		$this->onSuccess = [];
		$this->onFailure = [];
	}

	public function reject() : void{
		$this->success = false;
		foreach($this->onFailure as $callback){
			$callback();
		}
		$this->onSuccess = [];
		$this->onFailure = [];
	}

	public function isCompleted() : bool{
		return $this->success !== null;
	}
}
