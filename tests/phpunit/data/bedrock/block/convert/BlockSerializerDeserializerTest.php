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

namespace pocketmine\data\bedrock\block\convert;

use PHPUnit\Framework\TestCase;
use pocketmine\block\BlockFactory;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateSerializeException;
use function print_r;

final class BlockSerializerDeserializerTest extends TestCase{
	private BlockStateToBlockObjectDeserializer $deserializer;
	private BlockObjectToBlockStateSerializer $serializer;

	public function setUp() : void{
		$this->deserializer = new BlockStateToBlockObjectDeserializer();
		$this->serializer = new BlockObjectToBlockStateSerializer();
	}

	public function testAllKnownBlockStatesSerializableAndDeserializable() : void{
		foreach(BlockFactory::getInstance()->getAllKnownStates() as $block){
			try{
				$blockStateData = $this->serializer->serializeBlock($block);
			}catch(BlockStateSerializeException $e){
				self::fail($e->getMessage());
			}
			try{
				$newBlock = $this->deserializer->deserializeBlock($blockStateData);
			}catch(BlockStateDeserializeException $e){
				self::fail($e->getMessage());
			}

			self::assertSame($block->getStateId(), $newBlock->getStateId(), "Mismatch of blockstate for " . $block->getName() . ", " . print_r($block, true) . " vs " . print_r($newBlock, true));
		}
	}
}
