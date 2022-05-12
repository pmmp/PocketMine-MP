<?php

declare(strict_types=1);

namespace pocketmine\data\bedrock\blockstate\convert;

use PHPUnit\Framework\TestCase;
use pocketmine\block\VanillaBlocks;

final class BlockSerializerDeserializerTest extends TestCase{
	private BlockStateToBlockObjectDeserializer $deserializer;
	private BlockObjectToBlockStateSerializer $serializer;

	public function setUp() : void{
		$this->deserializer = new BlockStateToBlockObjectDeserializer();
		$this->serializer = new BlockObjectToBlockStateSerializer();
	}

	public function testAllVanillaBlocksSerializableAndDeserializable() : void{
		foreach(VanillaBlocks::getAll() as $block){
			$blockStateData = $this->serializer->serializeBlock($block);
			$newBlock = $this->deserializer->deserializeBlock($blockStateData);

			self::assertSame($block->getFullId(), $newBlock->getFullId());
		}
	}
}
