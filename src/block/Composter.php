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

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\crafting\CompostRecipe;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\CropGrowthEmitterParticle;
use pocketmine\world\sound\ComposterEmptySound;
use pocketmine\world\sound\ComposterFillSound;
use pocketmine\world\sound\ComposterFillSuccessSound;
use pocketmine\world\sound\ComposterReadySound;
use function max;
use function mt_rand;

class Composter extends Transparent{

	protected int $fill_level = 0;

	protected function writeStateToMeta() : int{
		return $this->fill_level;
	}

	public function writeStateToItemMeta() : int{
		return $this->fill_level;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->fill_level = BlockDataSerializer::readBoundedInt("composter_fill_level", $stateMeta, 0, 8);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	protected function recalculateCollisionBoxes() : array{
		$empty_layer = (max(1, 15 - 2 * $this->fill_level) - (int) ($this->fill_level === 0)) / 16;
		$boxes = [AxisAlignedBB::one()->contract(2 / 16, 0, 2 / 16)->trim(Facing::UP, $empty_layer)];

		foreach (Facing::HORIZONTAL as $side) {
			$boxes[] = AxisAlignedBB::one()->trim(Facing::opposite($side), 14 / 16);
		}
		return $boxes;
	}

	public function isEmpty() : bool{
		return $this->fill_level === 0;
	}

	public function isReady() : bool{
		return $this->fill_level === 8;
	}

	public function getFillLevel() : int{
		return $this->fill_level;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if (($player instanceof Player) && $this->compost(clone $item)) {
			$item->pop();
		}
		return true;
	}

	public function compost(Item $item) : bool{
		if ($this->fill_level >= 8) {
			$this->position->getWorld()->dropItem(
				$this->position->add(0.5, 0.85, 0.5),
				VanillaItems::BONE_MEAL(),
				new Vector3(0, 0, 0)
			);
			$this->position->getWorld()->addSound($this->position, new ComposterEmptySound());

			$this->position->getWorld()->addParticle($this->position, new CropGrowthEmitterParticle());

			$this->fill_level = 0;
			$this->position->getWorld()->setBlock($this->position->add(0.5, 0.5, 0.5), $this);
		}
		$compost = $this->position->getWorld()->getServer()->getCraftingManager()->getCompostRecipeManager()->match($item);
		if ($compost instanceof CompostRecipe) {
			return false;
		}
		$this->position->getWorld()->addParticle($this->position->add(0.5, 0.5, 0.5), new CropGrowthEmitterParticle());

		if (mt_rand(1, 100) <= $compost->getPercentage()) {
			++$this->fill_level;
			if ($this->fill_level === 8) {
				$this->position->getWorld()->addSound($this->position, new ComposterReadySound());
			} else {
				$this->position->getWorld()->addSound($this->position, new ComposterFillSuccessSound());
			}
		} else {
			$this->position->getWorld()->addSound($this->position, new ComposterFillSound());
			return true;
		}
		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function getDrops(Item $item) : array{
		return $this->fill_level === 8 ? [
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
