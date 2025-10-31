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

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

trait WaterloggableTrait{

	protected ?Water $containedWater = null;

	public function getContainedWater() : ?Water{
		return $this->containedWater !== null ? clone $this->containedWater : null;
	}

	/** @return $this */
	public function setContainedWater(?Water $containedWater) : self{
		$this->containedWater = $containedWater !== null ? clone $containedWater : null;
		return $this;
	}

	public function liquidCollide(Block $cause, Block $result) : bool{
		return $this->containedWater?->liquidCollide($cause, $result) ?? false;
	}

	public function canBeWaterlogged() : bool{
		return true;
	}

	public function isSideOpenToFlow(int $face) : bool{
		return true;
	}

	public function getDisplacedBlock() : ?Block{
		return $this->containedWater !== null ? $this->containedWater : null;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($blockReplace instanceof Water && ($this->hasTypeTag(BlockTypeTags::NON_SOURCE_WATERLOGGABLE) || $blockReplace->isSource())){
			$this->containedWater = clone $blockReplace;
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function readStateFromWorld() : Block{
		$this->containedWater?->readStateFromWorld();
		return $this;
	}

	public function addVelocityToEntity(Entity $entity) : ?Vector3{
		if($this->containedWater !== null && $entity->canBeMovedByCurrents()){
			return $this->containedWater->getFlowVector();
		}
		return null;
	}

	public function onEntityInside(Entity $entity) : bool{
		$this->containedWater?->onEntityInside($entity);
		return true;
	}

	public function onNearbyBlockChange() : void{
		if($this->containedWater !== null){
			$this->position->getWorld()->delayDisplacedBlockUpdate($this->position, $this->containedWater->tickRate());
		}
	}
}
