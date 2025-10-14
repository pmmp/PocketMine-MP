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
use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

trait CoveredByWaterTrait{

	protected ?Water $waterCover = null;

	public function getWaterCover() : ?Water{
		return $this->waterCover !== null ? clone $this->waterCover : null;
	}

	/** @return $this */
	public function setWaterCover(?Water $waterCover) : self{
		$this->waterCover = $waterCover !== null ? clone $waterCover : null;
		return $this;
	}

	public function liquidCollide(Block $cause, Block $result) : bool{
		return $this->waterCover?->liquidCollide($cause, $result) ?? false;
	}

	public function canBeCovered() : bool{
		return true;
	}

	public function canBeCoveredByFlowing() : bool{
		return false;
	}

	public function isSideOpenToFlow(int $face) : bool{
		return true;
	}

	public function getDisplacedBlock() : ?Block{
		return $this->waterCover !== null ? $this->waterCover : null;
	}

	public function setDisplacedBlock(?Block $block) : void{
		if($block instanceof Water){
			$this->waterCover = $block;
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($blockReplace instanceof Water && ($this->canBeCoveredByFlowing() || $blockReplace->isSource())){
			$this->waterCover = clone $blockReplace;
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function readStateFromWorld() : Block{
		$this->waterCover?->readStateFromWorld();
		return $this;
	}

	public function addVelocityToEntity(Entity $entity) : ?Vector3{
		if($this->waterCover !== null && $entity->canBeMovedByCurrents()){
			return $this->waterCover->getFlowVector();
		}
		return null;
	}

	public function onEntityInside(Entity $entity) : bool{
		$this->waterCover?->onEntityInside($entity);
		return true;
	}

	public function onNearbyBlockChange() : void{
		if($this->waterCover !== null){
			$this->position->getWorld()->delayDisplacedBlockUpdate($this->position, $this->waterCover->tickRate());
		}
	}
}
