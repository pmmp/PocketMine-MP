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

namespace pocketmine\data\bedrock\block\upgrade;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Utils;

final class BlockStateUpgradeSchemaBlockRemap{

	public CompoundTag $oldState;
	public CompoundTag $newState;

	/**
	 * @param Tag[] $oldState
	 * @param Tag[] $newState
	 * @phpstan-param array<string, Tag> $oldState
	 * @phpstan-param array<string, Tag> $newState
	 */
	public function __construct(
		array $oldState,
		public string $newName,
		array $newState
	){
		$this->oldState = CompoundTag::create();
		$this->newState = CompoundTag::create();
		foreach(Utils::stringifyKeys($oldState) as $k => $v){
			$this->oldState->setTag($k, $v);
		}
		foreach(Utils::stringifyKeys($newState) as $k => $v){
			$this->newState->setTag($k, $v);
		}
	}
}
