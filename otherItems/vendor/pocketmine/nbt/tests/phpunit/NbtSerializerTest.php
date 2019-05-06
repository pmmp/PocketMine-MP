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

namespace pocketmine\nbt;

use PHPUnit\Framework\TestCase;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class NbtSerializerTest extends TestCase{

	/**
	 * @return CompoundTag
	 * @throws \RuntimeException
	 */
	private static function maxDepthProvider() : CompoundTag{
		$root = new CompoundTag("");
		$root->setTag($current = new CompoundTag("child"));
		for($depth = 0; $depth < 512; ++$depth){
			$current->setTag($current = new CompoundTag("child"));
			$current->setTag($list = new ListTag("childList"));
			$list->push($current = new CompoundTag(""));
		}
		return $root;
	}

	public function serializerProvider() : \Generator{
		yield [new BigEndianNBTStream()];
		yield [new LittleEndianNBTStream()];
		yield [new NetworkLittleEndianNBTStream()];
	}

	/**
	 * @param NBTStream $serializer
	 *
	 * @dataProvider serializerProvider
	 * @throws \InvalidArgumentException
	 */
	public function testMaxDepthDecode(NBTStream $serializer) : void{
		$reader = clone $serializer;
		$data = $serializer->write(self::maxDepthProvider());

		$this->expectException(\UnexpectedValueException::class);

		$offset = 0;
		$reader->read($data, false, $offset, 512);
	}
}
