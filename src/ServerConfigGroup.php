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

namespace pocketmine;

use pocketmine\utils\Config;
use function array_key_exists;
use function getopt;
use function is_bool;
use function strtolower;

final class ServerConfigGroup{

	/** @var Config */
	private $pocketmineYml;
	/** @var Config */
	private $serverProperties;

	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private $propertyCache = [];

	public function __construct(Config $pocketmineYml, Config $serverProperties){
		$this->pocketmineYml = $pocketmineYml;
		$this->serverProperties = $serverProperties;
	}

	/**
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getProperty(string $variable, $defaultValue = null){
		if(!array_key_exists($variable, $this->propertyCache)){
			$v = getopt("", ["$variable::"]);
			if(isset($v[$variable])){
				$this->propertyCache[$variable] = $v[$variable];
			}else{
				$this->propertyCache[$variable] = $this->pocketmineYml->getNested($variable);
			}
		}

		return $this->propertyCache[$variable] ?? $defaultValue;
	}

	public function getPropertyBool(string $variable, bool $defaultValue) : bool{
		return (bool) $this->getProperty($variable, $defaultValue);
	}

	public function getPropertyInt(string $variable, int $defaultValue) : int{
		return (int) $this->getProperty($variable, $defaultValue);
	}

	public function getPropertyString(string $variable, string $defaultValue) : string{
		return (string) $this->getProperty($variable, $defaultValue);
	}

	public function getConfigString(string $variable, string $defaultValue = "") : string{
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (string) $v[$variable];
		}

		return $this->serverProperties->exists($variable) ? (string) $this->serverProperties->get($variable) : $defaultValue;
	}

	public function setConfigString(string $variable, string $value) : void{
		$this->serverProperties->set($variable, $value);
	}

	public function getConfigInt(string $variable, int $defaultValue = 0) : int{
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (int) $v[$variable];
		}

		return $this->serverProperties->exists($variable) ? (int) $this->serverProperties->get($variable) : $defaultValue;
	}

	public function setConfigInt(string $variable, int $value) : void{
		$this->serverProperties->set($variable, $value);
	}

	public function getConfigBool(string $variable, bool $defaultValue = false) : bool{
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			$value = $v[$variable];
		}else{
			$value = $this->serverProperties->exists($variable) ? $this->serverProperties->get($variable) : $defaultValue;
		}

		if(is_bool($value)){
			return $value;
		}
		switch(strtolower($value)){
			case "on":
			case "true":
			case "1":
			case "yes":
				return true;
		}

		return false;
	}

	public function setConfigBool(string $variable, bool $value) : void{
		$this->serverProperties->set($variable, $value ? "1" : "0");
	}

	public function save() : void{
		if($this->serverProperties->hasChanged()){
			$this->serverProperties->save();
		}
		if($this->pocketmineYml->hasChanged()){
			$this->pocketmineYml->save();
		}
	}
}
