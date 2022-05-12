<?php

declare(strict_types=1);

namespace pocketmine\data\bedrock\blockstate\convert;

use PHPUnit\Framework\TestCase;
use pocketmine\block\BlockFactory;

final class BlockSerializerDeserializerTest extends TestCase{
	private BlockStateToBlockObjectDeserializer $deserializer;
	private BlockObjectToBlockStateSerializer $serializer;

	public function setUp() : void{
		$this->deserializer = new BlockStateToBlockObjectDeserializer();
		$this->serializer = new BlockObjectToBlockStateSerializer();
	}

	public function testAllKnownBlockStatesSerializableAndDeserializable() : void{
		foreach(BlockFactory::getInstance()->getAllKnownStates() as $block){
			$blockStateData = $this->serializer->serializeBlock($block);
			$newBlock = $this->deserializer->deserializeBlock($blockStateData);

			self::assertSame($block->getFullId(), $newBlock->getFullId(), "Mismatch of blockstate for " . $block->getName());
		}
	}
}
