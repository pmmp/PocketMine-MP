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

/**
 * This trait provides destructor callback functionality to objects which use it. This enables a weakmap-like system
 * to function without actually having weak maps.
 * TODO: remove this in PHP 8
 */
trait DestructorCallbackTrait{
	/**
	 * @var ObjectSet
	 * @phpstan-var ObjectSet<\Closure() : void>|null
	 */
	private $destructorCallbacks = null;

	/** @phpstan-return ObjectSet<\Closure() : void> */
	public function getDestructorCallbacks() : ObjectSet{
		return $this->destructorCallbacks === null ? ($this->destructorCallbacks = new ObjectSet()) : $this->destructorCallbacks;
	}

	public function __destruct(){
		if($this->destructorCallbacks !== null){
			foreach($this->destructorCallbacks as $callback){
				$callback();
			}
		}
	}
}
