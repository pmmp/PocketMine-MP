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

abstract class DoubleSlab extends Solid{
	/** @var int */
	protected $variant = 0;

	public function __construct(int $meta = 0){
		$this->setDamage($meta);
	}

	public function getDamage() : int{
		return $this->variant;
	}

	public function setDamage(int $meta) : void{
		$this->variant = $meta;
	}

	public function getVariant() : int{
		return $this->variant;
	}

	abstract public function getSlabId() : int;

	public function getName() : string{
		return "Double " . BlockFactory::get($this->getSlabId(), $this->variant)->getName();
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get($this->getSlabId(), $this->variant, 2)
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
