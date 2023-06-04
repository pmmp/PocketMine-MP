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

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\nbt\tag\CompoundTag;

final class BlockDataUpgrader{

	public function __construct(
		private BlockIdMetaUpgrader $blockIdMetaUpgrader,
		private BlockStateUpgrader $blockStateUpgrader
	){}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function upgradeIntIdMeta(int $id, int $meta) : BlockStateData{
		return $this->blockIdMetaUpgrader->fromIntIdMeta($id, $meta);
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function upgradeStringIdMeta(string $id, int $meta) : BlockStateData{
		return $this->blockIdMetaUpgrader->fromStringIdMeta($id, $meta);
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function upgradeBlockStateNbt(CompoundTag $tag) : BlockStateData{
		if($tag->getTag("name") !== null && $tag->getTag("val") !== null){
			//Legacy (pre-1.13) blockstate - upgrade it to a version we understand
			$id = $tag->getString("name");
			$data = $tag->getShort("val");

			$blockStateData = $this->upgradeStringIdMeta($id, $data);
		}else{
			//Modern (post-1.13) blockstate
			$blockStateData = BlockStateData::fromNbt($tag);
		}

		return $this->blockStateUpgrader->upgrade($blockStateData);
	}

	public function getBlockStateUpgrader() : BlockStateUpgrader{ return $this->blockStateUpgrader; }

	public function getBlockIdMetaUpgrader() : BlockIdMetaUpgrader{ return $this->blockIdMetaUpgrader; }
}
