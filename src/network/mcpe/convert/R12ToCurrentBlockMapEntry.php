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

namespace pocketmine\network\mcpe\convert;

use pocketmine\nbt\tag\CompoundTag;

final class R12ToCurrentBlockMapEntry{
	public function __construct(
		private string $id,
		private int $meta,
		private CompoundTag $blockState
	){}

	public function getId() : string{
		return $this->id;
	}

	public function getMeta() : int{
		return $this->meta;
	}

	public function getBlockState() : CompoundTag{
		return $this->blockState;
	}

	public function __toString(){
		return "id=$this->id, meta=$this->meta, nbt=$this->blockState";
	}
}
