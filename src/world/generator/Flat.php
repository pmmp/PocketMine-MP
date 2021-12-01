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

namespace pocketmine\world\generator;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;
use pocketmine\world\generator\object\OreType;
use pocketmine\world\generator\populator\Ore;
use pocketmine\world\generator\populator\Populator;
use function count;

class Flat extends Generator{

	/** @var Chunk */
	private $chunk;
	/** @var Populator[] */
	private $populators = [];

	private FlatGeneratorOptions $options;

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(int $seed, string $preset){
		parent::__construct($seed, $preset !== "" ? $preset : "2;bedrock,2xdirt,grass;1;");
		$this->options = FlatGeneratorOptions::parsePreset($this->preset);

		if(isset($this->options->getExtraOptions()["decoration"])){
			$ores = new Ore();
			$stone = VanillaBlocks::STONE();
			$ores->setOreTypes([
				new OreType(VanillaBlocks::COAL_ORE(), $stone, 20, 16, 0, 128),
				new OreType(VanillaBlocks::IRON_ORE(), $stone, 20, 8, 0, 64),
				new OreType(VanillaBlocks::REDSTONE_ORE(), $stone, 8, 7, 0, 16),
				new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), $stone, 1, 6, 0, 32),
				new OreType(VanillaBlocks::GOLD_ORE(), $stone, 2, 8, 0, 32),
				new OreType(VanillaBlocks::DIAMOND_ORE(), $stone, 1, 7, 0, 16),
				new OreType(VanillaBlocks::DIRT(), $stone, 20, 32, 0, 128),
				new OreType(VanillaBlocks::GRAVEL(), $stone, 10, 16, 0, 128)
			]);
			$this->populators[] = $ores;
		}

		$this->generateBaseChunk();
	}

	protected function generateBaseChunk() : void{
		$this->chunk = new Chunk([], BiomeArray::fill($this->options->getBiomeId()), false);

		$structure = $this->options->getStructure();
		$count = count($structure);
		for($sy = 0; $sy < $count; $sy += SubChunk::EDGE_LENGTH){
			$subchunk = $this->chunk->getSubChunk($sy >> SubChunk::COORD_BIT_SIZE);
			for($y = 0; $y < SubChunk::EDGE_LENGTH and isset($structure[$y | $sy]); ++$y){
				$id = $structure[$y | $sy];

				for($Z = 0; $Z < SubChunk::EDGE_LENGTH; ++$Z){
					for($X = 0; $X < SubChunk::EDGE_LENGTH; ++$X){
						$subchunk->setFullBlock($X, $y, $Z, $id);
					}
				}
			}
		}
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$world->setChunk($chunkX, $chunkZ, clone $this->chunk);
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		foreach($this->populators as $populator){
			$populator->populate($world, $chunkX, $chunkZ, $this->random);
		}

	}
}
