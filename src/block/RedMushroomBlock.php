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

namespace pocketmine\block;

use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\data\bedrock\MushroomBlockTypeIdMap;
use pocketmine\item\Item;
use function mt_rand;

class RedMushroomBlock extends Opaque{

	protected MushroomBlockType $mushroomBlockType;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->mushroomBlockType = MushroomBlockType::PORES();
		parent::__construct($idInfo, $name, $breakInfo);
	}

	protected function writeStateToMeta() : int{
		return MushroomBlockTypeIdMap::getInstance()->toId($this->mushroomBlockType);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$type = MushroomBlockTypeIdMap::getInstance()->fromId($stateMeta);
		if($type === null){
			throw new InvalidBlockStateException("No such mushroom variant $stateMeta");
		}
		$this->mushroomBlockType = $type;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getMushroomBlockType() : MushroomBlockType{ return $this->mushroomBlockType; }

	/** @return $this */
	public function setMushroomBlockType(MushroomBlockType $mushroomBlockType) : self{
		$this->mushroomBlockType = $mushroomBlockType;
		return $this;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaBlocks::RED_MUSHROOM()->asItem()->setCount(mt_rand(0, 2))
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}
}
