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

namespace pocketmine\network\mcpe;

class CompressBatchPromise{
	/** @var callable[] */
	private $callbacks = [];

	/** @var string|null */
	private $result = null;

	public function onResolve(callable $callback) : void{
		if($this->result !== null){
			$callback($this);
		}else{
			$this->callbacks[] = $callback;
		}
	}

	public function resolve(string $result) : void{
		if($this->result !== null){
			throw new \InvalidStateException("Cannot resolve promise more than once");
		}
		$this->result = $result;
		foreach($this->callbacks as $callback){
			$callback($this);
		}
		$this->callbacks = [];
	}

	public function getResult() : string{
		if($this->result === null){
			throw new \InvalidStateException("Promise has not yet been resolved");
		}
		return $this->result;
	}

	public function hasResult() : bool{
		return $this->result !== null;
	}
}
