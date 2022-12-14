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

use pocketmine\item\GoatHornType;
use pocketmine\utils\SingletonTrait;

final class GoatHornTypeIdMap{
	use SingletonTrait;

	/**
	 * @var GoatHornType[]
	 * @phpstan-var array<int, GoatHornType>
	 */
	private array $idToEnum;

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId;

	private function __construct(){
		$this->register(GoatHornTypeIds::PONDER, GoatHornType::PONDER());
		$this->register(GoatHornTypeIds::SING, GoatHornType::SING());
		$this->register(GoatHornTypeIds::SEEK, GoatHornType::SEEK());
		$this->register(GoatHornTypeIds::FEEL, GoatHornType::FEEL());
		$this->register(GoatHornTypeIds::ADMIRE, GoatHornType::ADMIRE());
		$this->register(GoatHornTypeIds::CALL, GoatHornType::CALL());
		$this->register(GoatHornTypeIds::YEARN, GoatHornType::YEARN());
		$this->register(GoatHornTypeIds::DREAM, GoatHornType::DREAM());
	}

	private function register(int $id, GoatHornType $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?GoatHornType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(GoatHornType $type) : int{
		if(!isset($this->enumToId[$type->id()])){
			throw new \InvalidArgumentException("Type does not have a mapped ID");
		}
		return $this->enumToId[$type->id()];
	}
}
