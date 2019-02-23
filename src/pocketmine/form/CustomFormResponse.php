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

namespace pocketmine\form;

class CustomFormResponse{
	/** @var array */
	private $data;

	public function __construct(array $data){
		$this->data = $data;
	}

	public function getInt(string $name) : int{
		$this->checkExists($name);
		return $this->data[$name];
	}

	public function getString(string $name) : string{
		$this->checkExists($name);
		return $this->data[$name];
	}

	public function getFloat(string $name) : float{
		$this->checkExists($name);
		return $this->data[$name];
	}

	public function getBool(string $name) : bool{
		$this->checkExists($name);
		return $this->data[$name];
	}

	public function getAll() : array{
		return $this->data;
	}

	private function checkExists(string $name) : void{
		if(!isset($this->data[$name])){
			throw new \InvalidArgumentException("Value \"$name\" not found");
		}
	}
}
