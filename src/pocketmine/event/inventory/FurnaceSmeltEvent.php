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

namespace pocketmine\event\inventory;

use pocketmine\block\tile\Furnace;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;

class FurnaceSmeltEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	/** @var Furnace */
	private $furnace;
	/** @var Item */
	private $source;
	/** @var Item */
	private $result;

	/**
	 * @param Furnace $furnace
	 * @param Item    $source
	 * @param Item    $result
	 */
	public function __construct(Furnace $furnace, Item $source, Item $result){
		parent::__construct($furnace->getBlock());
		$this->source = clone $source;
		$this->source->setCount(1);
		$this->result = $result;
		$this->furnace = $furnace;
	}

	/**
	 * @return Furnace
	 */
	public function getFurnace() : Furnace{
		return $this->furnace;
	}

	/**
	 * @return Item
	 */
	public function getSource() : Item{
		return $this->source;
	}

	/**
	 * @return Item
	 */
	public function getResult() : Item{
		return $this->result;
	}

	/**
	 * @param Item $result
	 */
	public function setResult(Item $result) : void{
		$this->result = $result;
	}
}
