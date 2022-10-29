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

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function array_intersect_key;
use function count;

class Vine extends Flowable{

	/** @var int[] */
	protected array $faces = [];

	protected function writeStateToMeta() : int{
		return
			(isset($this->faces[Facing::SOUTH]) ? BlockLegacyMetadata::VINE_FLAG_SOUTH : 0) |
			(isset($this->faces[Facing::WEST]) ? BlockLegacyMetadata::VINE_FLAG_WEST : 0) |
			(isset($this->faces[Facing::NORTH]) ? BlockLegacyMetadata::VINE_FLAG_NORTH : 0) |
			(isset($this->faces[Facing::EAST]) ? BlockLegacyMetadata::VINE_FLAG_EAST : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->setFaceFromMeta($stateMeta, BlockLegacyMetadata::VINE_FLAG_SOUTH, Facing::SOUTH);
		$this->setFaceFromMeta($stateMeta, BlockLegacyMetadata::VINE_FLAG_WEST, Facing::WEST);
		$this->setFaceFromMeta($stateMeta, BlockLegacyMetadata::VINE_FLAG_NORTH, Facing::NORTH);
		$this->setFaceFromMeta($stateMeta, BlockLegacyMetadata::VINE_FLAG_EAST, Facing::EAST);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	private function setFaceFromMeta(int $meta, int $flag, int $face) : void{
		$this->setFace($face, ($meta & $flag) !== 0);
	}

	/** @return int[] */
	public function getFaces() : array{ return $this->faces; }

	/**
	 * @param int[] $faces
	 * @phpstan-param list<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST> $faces
	 * @return $this
	 */
	public function setFaces(array $faces) : self{
		$uniqueFaces = [];
		foreach($faces as $face){
			if($face !== Facing::NORTH && $face !== Facing::SOUTH && $face !== Facing::WEST && $face !== Facing::EAST){
				throw new \InvalidArgumentException("Facing can only be north, east, south or west");
			}
			$uniqueFaces[$face] = $face;
		}
		$this->faces = $uniqueFaces;
		return $this;
	}

	/** @return $this */
	public function setFace(int $face, bool $value) : self{
		if($face !== Facing::NORTH && $face !== Facing::SOUTH && $face !== Facing::WEST && $face !== Facing::EAST){
			throw new \InvalidArgumentException("Facing can only be north, east, south or west");
		}
		if($value){
			$this->faces[$face] = $face;
		}else{
			unset($this->faces[$face]);
		}
		return $this;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function canClimb() : bool{
		return true;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool{
		$entity->resetFallDistance();
		return true;
	}

	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$blockClicked->isFullCube() || Facing::axis($face) === Axis::Y){
			return false;
		}

		$this->faces = $blockReplace instanceof Vine ? $blockReplace->faces : [];
		$this->faces[Facing::opposite($face)] = Facing::opposite($face);

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$changed = false;

		$up = $this->getSide(Facing::UP);
		//check which faces have corresponding vines in the block above
		$supportedFaces = $up instanceof Vine ? array_intersect_key($this->faces, $up->faces) : [];

		foreach($this->faces as $face){
			if(!isset($supportedFaces[$face]) && !$this->getSide($face)->isSolid()){
				unset($this->faces[$face]);
				$changed = true;
			}
		}

		if($changed){
			$world = $this->position->getWorld();
			if(count($this->faces) === 0){
				$world->useBreakOn($this->position);
			}else{
				$world->setBlock($this->position, $this);
			}
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		//TODO: vine growth
	}

	public function getDrops(Item $item) : array{
		if(($item->getBlockToolType() & BlockToolType::SHEARS) !== 0){
			return $this->getDropsForCompatibleTool($item);
		}

		return [];
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}
}
