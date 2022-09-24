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

use pocketmine\block\tile\BrewingStand as TileBrewingStand;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\SupportType;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function array_key_exists;

class BrewingStand extends Transparent{

	/**
	 * @var BrewingStandSlot[]
	 * @phpstan-var array<int, BrewingStandSlot>
	 */
	protected array $slots = [];

	protected function writeStateToMeta() : int{
		$flags = 0;
		foreach([
			BlockLegacyMetadata::BREWING_STAND_FLAG_EAST => BrewingStandSlot::EAST(),
			BlockLegacyMetadata::BREWING_STAND_FLAG_NORTHWEST => BrewingStandSlot::NORTHWEST(),
			BlockLegacyMetadata::BREWING_STAND_FLAG_SOUTHWEST => BrewingStandSlot::SOUTHWEST(),
		] as $flag => $slot){
			$flags |= (array_key_exists($slot->id(), $this->slots) ? $flag : 0);
		}
		return $flags;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->slots = [];
		foreach([
			BlockLegacyMetadata::BREWING_STAND_FLAG_EAST => BrewingStandSlot::EAST(),
			BlockLegacyMetadata::BREWING_STAND_FLAG_NORTHWEST => BrewingStandSlot::NORTHWEST(),
			BlockLegacyMetadata::BREWING_STAND_FLAG_SOUTHWEST => BrewingStandSlot::SOUTHWEST(),
		] as $flag => $slot){
			if(($stateMeta & $flag) !== 0){
				$this->slots[$slot->id()] = $slot;
			}
		}
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	protected function recalculateCollisionBoxes() : array{
		return [
			//bottom slab part - in PC this is also inset on X/Z by 1/16, but Bedrock sucks
			AxisAlignedBB::one()->trim(Facing::UP, 7 / 8),

			//center post
			AxisAlignedBB::one()
				->squash(Axis::X, 7 / 16)
				->squash(Axis::Z, 7 / 16)
				->trim(Facing::UP, 1 / 8)
		];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	public function hasSlot(BrewingStandSlot $slot) : bool{
		return array_key_exists($slot->id(), $this->slots);
	}

	public function setSlot(BrewingStandSlot $slot, bool $occupied) : self{
		if($occupied){
			$this->slots[$slot->id()] = $slot;
		}else{
			unset($this->slots[$slot->id()]);
		}
		return $this;
	}

	/**
	 * @return BrewingStandSlot[]
	 * @phpstan-return array<int, BrewingStandSlot>
	 */
	public function getSlots() : array{
		return $this->slots;
	}

	/** @param BrewingStandSlot[] $slots */
	public function setSlots(array $slots) : self{
		$this->slots = [];
		foreach($slots as $slot){
			$this->slots[$slot->id()] = $slot;
		}
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$stand = $this->position->getWorld()->getTile($this->position);
			if($stand instanceof TileBrewingStand && $stand->canOpenWith($item->getCustomName())){
				$player->setCurrentWindow($stand->getInventory());
			}
		}

		return true;
	}

	public function onScheduledUpdate() : void{
		$world = $this->position->getWorld();
		$brewing = $world->getTile($this->position);
		if($brewing instanceof TileBrewingStand){
			if($brewing->onUpdate()){
				$world->scheduleDelayedBlockUpdate($this->position, 1);
			}

			$changed = false;
			foreach(BrewingStandSlot::getAll() as $slot){
				$occupied = !$brewing->getInventory()->isSlotEmpty($slot->getSlotNumber());
				if($occupied !== $this->hasSlot($slot)){
					$this->setSlot($slot, $occupied);
					$changed = true;
				}
			}

			if($changed){
				$world->setBlock($this->position, $this);
			}
		}
	}
}
