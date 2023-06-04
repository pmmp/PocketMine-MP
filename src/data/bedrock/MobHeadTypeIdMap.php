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

use pocketmine\block\utils\MobHeadType;
use pocketmine\utils\SingletonTrait;

final class MobHeadTypeIdMap{
	use SingletonTrait;

	/**
	 * @var MobHeadType[]
	 * @phpstan-var array<int, MobHeadType>
	 */
	private array $idToEnum = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	private function __construct(){
		$this->register(0, MobHeadType::SKELETON());
		$this->register(1, MobHeadType::WITHER_SKELETON());
		$this->register(2, MobHeadType::ZOMBIE());
		$this->register(3, MobHeadType::PLAYER());
		$this->register(4, MobHeadType::CREEPER());
		$this->register(5, MobHeadType::DRAGON());
	}

	private function register(int $id, MobHeadType $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?MobHeadType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(MobHeadType $type) : int{
		if(!isset($this->enumToId[$type->id()])){
			throw new \InvalidArgumentException("Type does not have a mapped ID");
		}
		return $this->enumToId[$type->id()];
	}
}
