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

namespace pocketmine\item;

use pocketmine\block\Block;

class ItemIdentifier{
	private int $legacyId;
	private int $legacyMeta;

	public function __construct(
		private int $typeId,
		int $legacyId,
		int $legacyMeta
	){
		if($legacyId < -0x8000 || $legacyId > 0x7fff){ //signed short range
			throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		if($legacyMeta < 0 || $legacyMeta > 0x7ffe){
			throw new \InvalidArgumentException("Meta must be in range 0 - " . 0x7ffe);
		}
		$this->legacyId = $legacyId;
		$this->legacyMeta = $legacyMeta;
	}

	public static function fromBlock(Block $block) : self{
		//negative item type IDs are treated as block IDs
		//TODO: maybe an ItemBlockIdentifier is in order?
		//TODO: this isn't vanilla-compliant, but it'll do for now - we only use the "legacy" item ID/meta for full type
		//indexing right now, because item type IDs aren't granular enough
		//this should be removed once that's addressed
		return new self(-$block->getTypeId(), -$block->getTypeId(), $block->computeTypeData());
	}

	public function getTypeId() : int{ return $this->typeId; }

	public function getLegacyId() : int{
		return $this->legacyId;
	}

	public function getLegacyMeta() : int{
		return $this->legacyMeta;
	}
}
