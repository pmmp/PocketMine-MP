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
use pocketmine\block\BaseBanner;
use pocketmine\block\Bed;
use pocketmine\block\BlockFactory;
use pocketmine\block\Skull;
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

			//The following are workarounds for differences in blockstate representation in Bedrock vs PM
			//In these cases, some properties are not stored in the blockstate (but rather in the block entity NBT), but
			//they do form part of the internal blockstate hash in PM. This leads to inconsistencies when serializing
			//and deserializing blockstates.
			if(
				($block instanceof BaseBanner && $newBlock instanceof BaseBanner) ||
				($block instanceof Bed && $newBlock instanceof Bed)
			){
				$newBlock->setColor($block->getColor());
			}elseif($block instanceof Skull && $newBlock instanceof Skull){
				$newBlock->setSkullType($block->getSkullType());
			}

			self::assertSame($block->getStateId(), $newBlock->getStateId(), "Mismatch of blockstate for " . $block->getName() . ", " . print_r($block, true) . " vs " . print_r($newBlock, true));
		}
	}
}
