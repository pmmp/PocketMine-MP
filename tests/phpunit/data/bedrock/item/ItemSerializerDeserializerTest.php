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

namespace pocketmine\data\bedrock\item;

use PHPUnit\Framework\TestCase;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

final class ItemSerializerDeserializerTest extends TestCase{

	private ItemDeserializer $deserializer;
	private ItemSerializer $serializer;

	public function setUp() : void{
		$this->deserializer = new ItemDeserializer();
		$this->serializer = new ItemSerializer();
	}

	public function testAllVanillaItemsSerializableAndDeserializable() : void{
		foreach(VanillaItems::getAll() as $item){
			if($item->isNull()){
				continue;
			}

			$itemData = $this->serializer->serialize($item);
			$newItem = $this->deserializer->deserialize($itemData);

			self::assertTrue($item->equalsExact($newItem));
		}
	}

	public function testAllVanillaBlocksSerializableAndDeserializable() : void{
		foreach(VanillaBlocks::getAll() as $block){
			$item = $block->asItem();
			if($item->isNull()){
				continue;
			}

			$itemData = $this->serializer->serialize($item);
			$newItem = $this->deserializer->deserialize($itemData);

			self::assertTrue($item->equalsExact($newItem));
		}
	}
}
