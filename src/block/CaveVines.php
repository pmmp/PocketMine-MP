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

use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataReader;
use pocketmine\data\runtime\RuntimeDataWriter;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function mt_rand;

class CaveVines extends Flowable{
	public const MAX_AGE = 25;

	protected int $age = 0;

	public function getRequiredStateDataBits() : int{
		return 5;
	}

	public function describeState(RuntimeDataWriter|RuntimeDataReader $w) : void{
		$w->boundedInt(5, 0, self::MAX_AGE, $this->age);
	}

	public function getAge() : int{
		return $this->age;
	}

	/** @return $this */
	public function setAge(int $age) : self{
		if($age < 0 || $age > self::MAX_AGE){
			throw new \InvalidArgumentException("Age must be in range 0-" . self::MAX_AGE);
		}
		$this->age = $age;
		return $this;
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function canClimb() : bool{
		return true;
	}

	private function canBeSupportedBy(Block $block) : bool{
		return $block->getSupportType(Facing::DOWN)->hasCenterSupport() || $block instanceof CaveVines;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::UP))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedBy($blockReplace->getSide(Facing::UP))){
			return false;
		}
		$this->age = mt_rand(0, self::MAX_AGE);
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer){
			$ev = new BlockGrowEvent($this, VanillaBlocks::CAVE_VINES_WITH_BERRIES()->setAge($this->age));
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
			$item->pop();
			$this->position->getWorld()->setBlock($this->position, $ev->getNewState());
			return true;
		}
		return false;
	}

	public function onRandomTick() : void{
		if($this->age < self::MAX_AGE && mt_rand(1, 10) === 1){
			$growthPos = $this->position->getSide(Facing::DOWN);
			$world = $growthPos->getWorld();
			if($world->isInWorld($growthPos->getFloorX(), $growthPos->getFloorY(), $growthPos->getFloorZ()) && $world->getBlock($growthPos)->getTypeId() === BlockTypeIds::AIR){
				$ev = new BlockGrowEvent($this, (mt_rand(1, 9) === 1 ? VanillaBlocks::CAVE_VINES_WITH_BERRIES() : VanillaBlocks::CAVE_VINES())
					->setAge($this->age + 1));

				$ev->call();

				if(!$ev->isCancelled()){
					$world->setBlock($growthPos, $ev->getNewState());
				}
			}
		}
	}

	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool{
		$entity->resetFallDistance();
		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function getSilkTouchDrops(Item $item) : array{
		return [VanillaItems::GLOW_BERRIES()];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}
}