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

use pocketmine\item\MedicineType;
use pocketmine\utils\SingletonTrait;

final class MedicineTypeIdMap{
	use SingletonTrait;

	/**
	 * @var MedicineType[]
	 * @phpstan-var array<int, MedicineType>
	 */
	private array $idToEnum = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	private function __construct(){
		$this->register(MedicineTypeIds::ANTIDOTE, MedicineType::ANTIDOTE());
		$this->register(MedicineTypeIds::ELIXIR, MedicineType::ELIXIR());
		$this->register(MedicineTypeIds::EYE_DROPS, MedicineType::EYE_DROPS());
		$this->register(MedicineTypeIds::TONIC, MedicineType::TONIC());
	}

	private function register(int $id, MedicineType $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?MedicineType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(MedicineType $type) : int{
		if(!isset($this->enumToId[$type->id()])){
			throw new \InvalidArgumentException("Type does not have a mapped ID");
		}
		return $this->enumToId[$type->id()];
	}
}
