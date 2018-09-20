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

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class DoubleSlab extends Solid{
	/** @var int */
	protected $singleId;

	public function __construct(int $id, int $singleId, int $variant = 0){
		parent::__construct($id, $variant);
		$this->singleId = $singleId;
	}

	protected function getSingle() : Block{
		return BlockFactory::get($this->singleId, $this->variant);
	}

	public function getHardness() : float{
		return $this->getSingle()->getHardness();
	}

	public function getToolType() : int{
		return $this->getSingle()->getToolType();
	}

	public function getToolHarvestLevel() : int{
		return $this->getSingle()->getToolHarvestLevel();
	}

	public function getFlameEncouragement() : int{
		return $this->getSingle()->getFlameEncouragement();
	}

	public function getFlammability() : int{
		return $this->getSingle()->getFlammability();
	}

	public function getName() : string{
		return "Double " . $this->getSingle()->getName();
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get($this->singleId, $this->variant, 2)
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function getPickedItem() : Item{
		return ItemFactory::get($this->singleId, $this->getVariant());
	}
}
