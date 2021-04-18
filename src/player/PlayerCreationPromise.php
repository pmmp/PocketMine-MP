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

namespace pocketmine\player;

use function spl_object_id;

final class PlayerCreationPromise{
	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure(Player) : void>
	 */
	private array $onSuccess = [];

	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure() : void>
	 */
	private array $onFailure = [];

	private bool $resolved = false;
	private ?Player $result = null;

	/**
	 * @phpstan-param \Closure(Player) : void $onSuccess
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

	public function resolve(Player $player) : void{
		$this->resolved = true;
		$this->result = $player;
		foreach($this->onSuccess as $c){
			$c($player);
		}
		$this->onSuccess = [];
		$this->onFailure = [];
	}

	public function reject() : void{
		$this->resolved = true;
		foreach($this->onFailure as $c){
			$c();
		}
		$this->onSuccess = [];
		$this->onFailure = [];
	}
}
