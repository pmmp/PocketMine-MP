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

use pocketmine\item\SuspiciousStewType;
use pocketmine\utils\SingletonTrait;

final class SuspiciousStewTypeIdMap{
	use SingletonTrait;

	/**
	 * @var SuspiciousStewType[]
	 * @phpstan-var array<int, SuspiciousStewType>
	 */
	private array $idToEnum = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	private function __construct(){
		$this->register(SuspiciousStewTypeIds::POPPY, SuspiciousStewType::POPPY());
		$this->register(SuspiciousStewTypeIds::CORNFLOWER, SuspiciousStewType::CORNFLOWER());
		$this->register(SuspiciousStewTypeIds::TULIP, SuspiciousStewType::TULIP());
		$this->register(SuspiciousStewTypeIds::AZURE_BLUET, SuspiciousStewType::AZURE_BLUET());
		$this->register(SuspiciousStewTypeIds::LILY_OF_THE_VALLEY, SuspiciousStewType::LILY_OF_THE_VALLEY());
		$this->register(SuspiciousStewTypeIds::DANDELION, SuspiciousStewType::DANDELION());
		$this->register(SuspiciousStewTypeIds::BLUE_ORCHID, SuspiciousStewType::BLUE_ORCHID());
		$this->register(SuspiciousStewTypeIds::ALLIUM, SuspiciousStewType::ALLIUM());
		$this->register(SuspiciousStewTypeIds::OXEYE_DAISY, SuspiciousStewType::OXEYE_DAISY());
		$this->register(SuspiciousStewTypeIds::WITHER_ROSE, SuspiciousStewType::WITHER_ROSE());
	}

	private function register(int $id, SuspiciousStewType $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?SuspiciousStewType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(SuspiciousStewType $type) : int{
		if(!isset($this->enumToId[$type->id()])){
			throw new \InvalidArgumentException("Type does not have a mapped ID");
		}
		return $this->enumToId[$type->id()];
	}
}
