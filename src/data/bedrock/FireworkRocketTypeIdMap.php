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

namespace pocketmine\data\bedrock;

use pocketmine\item\FireworkRocketType;
use pocketmine\utils\SingletonTrait;

final class FireworkRocketTypeIdMap{
	use SingletonTrait;

	/**
	 * @var FireworkRocketType[]
	 * @phpstan-var array<int, FireworkRocketType>
	 */
	private array $idToEnum = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	private function __construct(){
		$this->register(FireworkRocketTypeIds::SMALL_BALL, FireworkRocketType::SMALL_BALL());
		$this->register(FireworkRocketTypeIds::LARGE_BALL, FireworkRocketType::LARGE_BALL());
		$this->register(FireworkRocketTypeIds::STAR, FireworkRocketType::STAR());
		$this->register(FireworkRocketTypeIds::CREEPER, FireworkRocketType::CREEPER());
		$this->register(FireworkRocketTypeIds::BURST, FireworkRocketType::BURST());
	}

	private function register(int $id, FireworkRocketType $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?FireworkRocketType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(FireworkRocketType $type) : int{
		if(!isset($this->enumToId[$type->id()])){
			throw new \InvalidArgumentException("Type does not have a mapped ID");
		}
		return $this->enumToId[$type->id()];
	}
}
