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
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\object\OreType;
use pocketmine\world\generator\populator\Ore;
use pocketmine\world\generator\populator\Populator;
use function array_map;
use function count;
use function explode;
use function preg_match;
use function preg_match_all;

class Flat extends Generator{
	/** @var Chunk */
	private $chunk;
	/** @var Populator[] */
	private $populators = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private $structure;
	/** @var int */
	private $biome;
	/** @var string */
	private $preset;

	/**
	 * @param mixed[] $options
	 * @phpstan-param array<string, mixed> $options
	 *
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(int $seed, array $options = []){
		parent::__construct($seed, $options);

		if(isset($this->options["preset"]) and $this->options["preset"] != ""){
			$this->preset = $this->options["preset"];
		}else{
			$this->preset = "2;bedrock,2xdirt,grass;1;";
			//$this->preset = "2;bedrock,59xstone,3xdirt,grass;1;spawn(radius=10 block=89),decoration(treecount=80 grasscount=45)";
		}

		$this->parsePreset();

		if(isset($this->options["decoration"])){
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

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 *
	 * @throws InvalidGeneratorOptionsException
	 */
	public static function parseLayers(string $layers) : array{
		$result = [];
		$split = array_map('\trim', explode(',', $layers));
		$y = 0;
		$itemParser = LegacyStringToItemParser::getInstance();
		foreach($split as $line){
			preg_match('#^(?:(\d+)[x|*])?(.+)$#', $line, $matches);
			if(count($matches) !== 3){
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\"");
			}

			$cnt = $matches[1] !== "" ? (int) $matches[1] : 1;
			try{
				$b = $itemParser->parse($matches[2])->getBlock();
			}catch(\InvalidArgumentException $e){
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\": " . $e->getMessage(), 0, $e);
			}
			for($cY = $y, $y += $cnt; $cY < $y; ++$cY){
				$result[$cY] = $b->getFullId();
			}
		}

		return $result;
	}

	protected function parsePreset() : void{
		$preset = explode(";", $this->preset);
		$blocks = $preset[1] ?? "";
		$this->biome = (int) ($preset[2] ?? 1);
		$options = $preset[3] ?? "";
		$this->structure = self::parseLayers($blocks);

		//TODO: more error checking
		preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:]{0,})\)?),?#', $options, $matches);
		foreach($matches[2] as $i => $option){
			$params = true;
			if($matches[3][$i] !== ""){
				$params = [];
				$p = explode(" ", $matches[3][$i]);
				foreach($p as $k){
					$k = explode("=", $k);
					if(isset($k[1])){
						$params[$k[0]] = $k[1];
					}
				}
			}
			$this->options[$option] = $params;
		}
	}

	protected function generateBaseChunk() : void{
		$this->chunk = new Chunk(0, 0);
		$this->chunk->setGenerated();

		for($Z = 0; $Z < 16; ++$Z){
			for($X = 0; $X < 16; ++$X){
				$this->chunk->setBiomeId($X, $Z, $this->biome);
			}
		}

		$count = count($this->structure);
		for($sy = 0; $sy < $count; $sy += 16){
			$subchunk = $this->chunk->getSubChunk($sy >> 4);
			for($y = 0; $y < 16 and isset($this->structure[$y | $sy]); ++$y){
				$id = $this->structure[$y | $sy];

				for($Z = 0; $Z < 16; ++$Z){
					for($X = 0; $X < 16; ++$X){
						$subchunk->setFullBlock($X, $y, $Z, $id);
					}
				}
			}
		}
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$chunk = clone $this->chunk;
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$world->setChunk($chunkX, $chunkZ, $chunk);
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		foreach($this->populators as $populator){
			$populator->populate($world, $chunkX, $chunkZ, $this->random);
		}

	}
}
