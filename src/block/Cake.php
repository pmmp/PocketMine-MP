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

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Cake extends BaseCake{
	public const MAX_BITES = 6;

	protected int $bites = 0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(0, self::MAX_BITES, $this->bites);
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [
			AxisAlignedBB::one()
				->contract(1 / 16, 0, 1 / 16)
				->trim(Facing::UP, 0.5)
				->trim(Facing::WEST, $this->bites / 8)
		];
	}

	public function getBites() : int{ return $this->bites; }

	/** @return $this */
	public function setBites(int $bites) : self{
		if($bites < 0 || $bites > self::MAX_BITES){
			throw new \InvalidArgumentException("Bites must be in range 0 ... " . self::MAX_BITES);
		}
		$this->bites = $bites;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($this->bites === 0 && $item instanceof ItemBlock){
			$block = $item->getBlock();
			$resultBlock = null;
			if($block->getTypeId() === BlockTypeIds::CANDLE){
				$resultBlock = VanillaBlocks::CAKE_WITH_CANDLE();
			}elseif($block instanceof DyedCandle){
				$resultBlock = VanillaBlocks::CAKE_WITH_DYED_CANDLE()->setColor($block->getColor());
			}

			if($resultBlock !== null){
				$this->position->getWorld()->setBlock($this->position, $resultBlock);
				$item->pop();
				return true;
			}
		}

		return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function getResidue() : Block{
		$clone = clone $this;
		$clone->bites++;
		if($clone->bites > self::MAX_BITES){
			$clone = VanillaBlocks::AIR();
		}
		return $clone;
	}
}
