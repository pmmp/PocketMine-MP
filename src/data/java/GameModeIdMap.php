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

namespace pocketmine\data\java;

use pocketmine\player\GameMode;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;

final class GameModeIdMap{
	use SingletonTrait;

	/**
	 * @var GameMode[]
	 * @phpstan-var array<int, GameMode>
	 */
	private array $idToEnum = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	public function __construct(){
		$this->register(0, GameMode::SURVIVAL());
		$this->register(1, GameMode::CREATIVE());
		$this->register(2, GameMode::ADVENTURE());
		$this->register(3, GameMode::SPECTATOR());
	}

	private function register(int $id, GameMode $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?GameMode{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(GameMode $type) : int{
		if(!array_key_exists($type->id(), $this->enumToId)){
			throw new \InvalidArgumentException("Game mode does not have a mapped ID"); //this should never happen
		}
		return $this->enumToId[$type->id()];
	}
}
