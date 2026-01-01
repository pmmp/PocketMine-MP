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

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

class ComposterEmptyEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	/**
	 * @param Item[] $drops
	 */
	public function __construct(
		Block $block,
		protected int $compostLayer,
		protected array $drops
	){
		return parent::__construct($block);
	}

	public function getLayer() : int{ return $this->compostLayer; }

	public function setLayer(int $layer) : void{
		$this->compostLayer = $layer;
	}

	public function getDrops() : array{ return $this->drops; }

	/**
	 * @param Item[] $drops
	 */
	public function setDrops(array $drops) : void{
		Utils::validateArrayValueType($drops, function(Item $_) : void{});
		$this->drops = $drops;
	}
}
