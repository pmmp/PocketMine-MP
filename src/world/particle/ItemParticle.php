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

namespace pocketmine\world\particle;

use pocketmine\data\bedrock\item\ItemTypeSerializeException;
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\ItemTranslator;

abstract class ItemParticle implements Particle{

	private int $protocolId;

	public function __construct(private Item $item){}

	public function setProtocolId(int $protocolId) : void{
		$this->protocolId = $protocolId;
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int, int}
	 *
	 * @throws ItemTypeSerializeException
	 */
	public function toNetworkId() : array{
		return ItemTranslator::getInstance($this->protocolId)->toNetworkId($this->item);
	}
}
