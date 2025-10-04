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

use pocketmine\block\inventory\window\BrewingStandInventoryWindow;
use pocketmine\block\tile\BrewingStand as TileBrewingStand;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\Container;
use pocketmine\block\utils\ContainerTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\inventory\Inventory;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\world\Position;
use function array_key_exists;
use function spl_object_id;

class BrewingStand extends Transparent implements Container{
	use ContainerTrait;

	/**
	 * @var BrewingStandSlot[]
	 * @phpstan-var array<int, BrewingStandSlot>
	 */
	protected array $slots = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->enumSet($this->slots, BrewingStandSlot::cases());
	}

	protected function recalculateCollisionBoxes() : array{
		return [
			//bottom slab part - in PC this is also inset on X/Z by 1/16, but Bedrock sucks
			AxisAlignedBB::one()->trimmedCopy(Facing::UP, 7 / 8),

			//center post
			AxisAlignedBB::one()
				->squashedCopy(Axis::X, 7 / 16)
				->squashedCopy(Axis::Z, 7 / 16)
				->trimmedCopy(Facing::UP, 1 / 8)
		];
	}

	public function getSupportType(Facing $facing) : SupportType{
		return SupportType::NONE;
	}

	public function hasSlot(BrewingStandSlot $slot) : bool{
		return array_key_exists(spl_object_id($slot), $this->slots);
	}

	public function setSlot(BrewingStandSlot $slot, bool $occupied) : self{
		if($occupied){
			$this->slots[spl_object_id($slot)] = $slot;
		}else{
			unset($this->slots[spl_object_id($slot)]);
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
			$this->slots[spl_object_id($slot)] = $slot;
		}
		return $this;
	}

	protected function newMenu(Player $player, Inventory $inventory, Position $position) : BrewingStandInventoryWindow{
		return new BrewingStandInventoryWindow($player, $inventory, $position);
	}

	public function onScheduledUpdate() : void{
		$world = $this->position->getWorld();
		$brewing = $world->getTile($this->position);
		if($brewing instanceof TileBrewingStand){
			if($brewing->onUpdate()){
				$world->scheduleDelayedBlockUpdate($this->position, 1);
			}

			$changed = false;
			foreach(BrewingStandSlot::cases() as $slot){
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
