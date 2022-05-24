<?php

declare(strict_types=1);

namespace pocketmine\data\bedrock\blockstate\convert;

use PHPUnit\Framework\TestCase;
use pocketmine\block\BlockFactory;
use pocketmine\data\bedrock\blockstate\BlockStateDeserializeException;
use pocketmine\data\bedrock\blockstate\BlockStateSerializeException;

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

			self::assertSame($block->getFullId(), $newBlock->getFullId(), "Mismatch of blockstate for " . $block->getName());
		}
	}
}
