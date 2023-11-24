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

use pocketmine\block\utils\AgeableTrait;
use pocketmine\block\utils\CropGrowthHelper;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

final class DoublePitcherCrop extends DoublePlant{
	use AgeableTrait {
		describeBlockOnlyState as describeAge;
	}

	public const MAX_AGE = 1;

	public function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		parent::describeBlockOnlyState($w);
		$this->describeAge($w);
	}

	protected function recalculateCollisionBoxes() : array{
		if($this->top){
			return [];
		}

		//the pod exists only in the bottom half of the plant
		return [
			AxisAlignedBB::one()
			->trim(Facing::UP, 11 / 16)
			->squash(Axis::X, 3 / 16)
			->squash(Axis::Z, 3 / 16)
			->extend(Facing::DOWN, 1 / 16) //presumably this is to correct for farmland being 15/16 of a block tall
		];
	}

	private function grow(?Player $player) : bool{
		if($this->age >= self::MAX_AGE){
			return false;
		}

		$bottom = $this->top ? $this->getSide(Facing::DOWN) : $this;
		$top = $this->top ? $this : $this->getSide(Facing::UP);
		if($top->getTypeId() !== BlockTypeIds::AIR && !$top->hasSameTypeId($this)){
			return false;
		}

		$newState = (clone $this)->setAge($this->age + 1);

		$tx = new BlockTransaction($this->position->getWorld());
		$tx->addBlock($bottom->position, (clone $newState)->setTop(false));
		$tx->addBlock($top->position, (clone $newState)->setTop(true));

		$ev = new StructureGrowEvent($bottom, $tx, $player);
		$ev->call();

		return !$ev->isCancelled() && $tx->apply();

	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer && $this->grow($player)){
			$item->pop();
			return true;
		}

		return false;
	}

	public function ticksRandomly() : bool{
		return $this->age < self::MAX_AGE && !$this->top;
	}

	public function onRandomTick() : void{
		//only the bottom half of the plant can grow randomly
		if(CropGrowthHelper::canGrow($this) && !$this->top){
			$this->grow(null);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			$this->age >= self::MAX_AGE ? VanillaBlocks::PITCHER_PLANT()->asItem() : VanillaItems::PITCHER_POD()
		];
	}

	public function asItem() : Item{
		return VanillaItems::PITCHER_POD();
	}
}
