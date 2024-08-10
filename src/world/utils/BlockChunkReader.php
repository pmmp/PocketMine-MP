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

namespace pocketmine\world\utils;

use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\utils\Waterloggable;
use pocketmine\block\Water;
use pocketmine\world\format\Chunk;

class BlockChunkReader{
	/**
	 * @param int $x The block's X coordinate masked to the chunk's bounds
	 * @param int $z The block's Z coordinate masked to the chunk's bounds
	 */
	public static function getBlock(Chunk $chunk, int $x, int $y, int $z) : Block {
		$block = RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getBlockStateId($x, $y, $z));
		if($block instanceof Waterloggable){
			$waterState = RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getWaterStateId($x, $y, $z));
			if($waterState instanceof Water){
				$block->setWaterState($waterState);
			}
		}

		return $block;
	}
}
