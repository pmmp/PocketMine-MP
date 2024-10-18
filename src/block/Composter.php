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

use pocketmine\block\utils\CompostFactory;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\ComposterEmptyEvent;
use pocketmine\event\block\ComposterFillEvent;
use pocketmine\event\block\ComposterMatureEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\particle\CropGrowthEmitterParticle;
use pocketmine\world\sound\ComposterEmptySound;
use pocketmine\world\sound\ComposterFillSound;
use pocketmine\world\sound\ComposterFillSuccessSound;
use pocketmine\world\sound\ComposterReadySound;
use function max;
use function mt_rand;

class Composter extends Transparent{

	public const EMPTY_LEVEL = 0;
	public const FULL_LEVEL = 7;
	public const COLLECTING_LEVEL = 8;

	protected int $fillLevel = self::EMPTY_LEVEL;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedInt(4, self::EMPTY_LEVEL, self::COLLECTING_LEVEL, $this->fillLevel);
	}

	protected function recalculateCollisionBoxes() : array{
		$empty_layer = (max(1, 15 - 2 * $this->fillLevel) - (int) ($this->fillLevel === 0)) / 16;
		$boxes = [AxisAlignedBB::one()->trim(Facing::UP, $empty_layer)];

		foreach(Facing::HORIZONTAL as $side){
			$boxes[] = AxisAlignedBB::one()->trim(Facing::opposite($side), 14 / 16);
		}
		return $boxes;
	}

	public function isEmpty() : bool{
		return $this->fillLevel === self::EMPTY_LEVEL;
	}

	public function isFull() : bool{
		return $this->fillLevel === self::FULL_LEVEL;
	}

	public function canCollect() : bool{
		return $this->fillLevel === self::COLLECTING_LEVEL;
	}

	public function getFillLevel() : int{
		return $this->fillLevel;
	}

	/** @return $this */
	public function setFillLevel(int $fillLevel) : self{
		if($fillLevel < 0 || $fillLevel > self::COLLECTING_LEVEL){
			throw new \InvalidArgumentException("Layers must be in range " . self::EMPTY_LEVEL . " ... " . self::COLLECTING_LEVEL);
		}
		$this->fillLevel = $fillLevel;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->isFull()){
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20);
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player !== null && $this->addItem($item)){
			$item->pop();
		}
		return true;
	}

	public function onScheduledUpdate() : void{
		if($this->isFull()){
			$block = clone $this;
			$block->setFillLevel(self::COLLECTING_LEVEL);

			if(ComposterMatureEvent::hasHandlers()){
				$ev = new ComposterMatureEvent($this, $block);
				$ev->call();
				if($ev->isCancelled()){
					return;
				}
				$block = $ev->getNewState();
			}

			$this->position->getWorld()->addSound($this->position, new ComposterReadySound());
			$this->position->getWorld()->setBlock($this->position, $block);
		}
	}

	public function addItem(Item $item) : bool{
		if($this->canCollect()){
			$this->empty();
			return false;
		}
		if($this->isFull() || !CompostFactory::getInstance()->isCompostable($item)){
			return false;
		}

		$this->position->getWorld()->addParticle($this->position->add(0.5, 0.5, 0.5), new CropGrowthEmitterParticle());

		$success = mt_rand(1,100) <= CompostFactory::getInstance()->getPercentage($item);
		if(ComposterFillEvent::hasHandlers()){
			$ev = new ComposterFillEvent($this, $item, $this->fillLevel, $success);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
			$success = $ev->isSuccess();
		}
		if($success){
			$this->fillLevel++;
			$this->position->getWorld()->addSound($this->position, new ComposterFillSuccessSound());
			$this->position->getWorld()->setBlock($this->position, $this);

			if($this->isFull()){
				$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20);
			}
		}else{
			$this->position->getWorld()->addSound($this->position, new ComposterFillSound());
		}
		return true;
	}

	public function empty() : void{
		$drops = [VanillaItems::BONE_MEAL()];
		if(ComposterEmptyEvent::hasHandlers()){
			$ev = new ComposterEmptyEvent($this, $drops);
			$ev->call();
			if($ev->isCancelled()){
				return;
			}
			$drops = $ev->getDrops();
		}
		foreach($drops as $drop){
			$this->position->getWorld()->dropItem(
				$this->position->add(0.5, 0.85, 0.5),
				$drop,
				new Vector3(0, 0, 0)
			);
		}
		$this->fillLevel = self::EMPTY_LEVEL;
		$this->position->getWorld()->addParticle($this->position, new CropGrowthEmitterParticle());
		$this->position->getWorld()->addSound($this->position, new ComposterEmptySound());
		$this->position->getWorld()->setBlock($this->position, $this);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return $this->canCollect() ? [
			VanillaBlocks::COMPOSTER()->asItem(),
			VanillaItems::BONE_MEAL()
		] : [
			VanillaBlocks::COMPOSTER()->asItem()
		];
	}

	public function getFlameEncouragement() : int{
		return 5;
	}

	public function getFlammability() : int{
		return 20;
	}

	public function getFuelTime() : int{
		return 50;
	}
}
