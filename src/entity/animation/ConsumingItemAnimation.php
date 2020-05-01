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

namespace pocketmine\entity\animation;

use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ActorEventPacket;

final class ConsumingItemAnimation implements Animation{

	/** @var Human */
	private $human;
	/** @var Item */
	private $item;

	public function __construct(Human $human, Item $item){
		//TODO: maybe this can be expanded to more than just player entities?
		$this->human = $human;
		$this->item = $item;
	}

	public function encode() : array{
		return [
			//TODO: need to check the data values
			ActorEventPacket::create($this->human->getId(), ActorEventPacket::EATING_ITEM, ($this->item->getId() << 16) | $this->item->getMeta())
		];
	}
}
