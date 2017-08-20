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

namespace pocketmine\level\generator;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\item\ItemFactory;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Flat extends Generator{
	/** @var ChunkManager */
	private $level;
	/** @var Chunk */
	private $chunk;
	/** @var Random */
	private $random;
	/** @var Populator[] */
	private $populators = [];
	private $structure, $chunks, $options, $floorLevel, $preset;

	public function getSettings() : array{
		return $this->options;
	}

	public function getName() : string{
		return "flat";
	}

	public function __construct(array $options = []){
		$this->preset = "2;7,2x3,2;1;";
		//$this->preset = "2;7,59x1,3x3,2;1;spawn(radius=10 block=89),decoration(treecount=80 grasscount=45)";
		$this->options = $options;
		$this->chunk = null;

		if(isset($this->options["decoration"])){
			$ores = new Ore();
			$ores->setOreTypes([
				new OreType(BlockFactory::get(Block::COAL_ORE), 20, 16, 0, 128),
				new OreType(BlockFactory::get(Block::IRON_ORE), 20, 8, 0, 64),
				new OreType(BlockFactory::get(Block::REDSTONE_ORE), 8, 7, 0, 16),
				new OreType(BlockFactory::get(Block::LAPIS_ORE), 1, 6, 0, 32),
				new OreType(BlockFactory::get(Block::GOLD_ORE), 2, 8, 0, 32),
				new OreType(BlockFactory::get(Block::DIAMOND_ORE), 1, 7, 0, 16),
				new OreType(BlockFactory::get(Block::DIRT), 20, 32, 0, 128),
				new OreType(BlockFactory::get(Block::GRAVEL), 10, 16, 0, 128)
			]);
			$this->populators[] = $ores;
		}

		/*if(isset($this->options["mineshaft"])){
			$this->populators[] = new MineshaftPopulator(isset($this->options["mineshaft"]["chance"]) ? floatval($this->options["mineshaft"]["chance"]) : 0.01);
		}*/
	}

	public static function parseLayers(string $layers) : array{
		$result = [];
		preg_match_all('#^(([0-9]*x|)([0-9]{1,3})(|:[0-9]{0,2}))$#m', str_replace(",", "\n", $layers), $matches);
		$y = 0;
		foreach($matches[3] as $i => $b){
			$b = ItemFactory::fromString($b . $matches[4][$i]);
			$cnt = $matches[2][$i] === "" ? 1 : (int) $matches[2][$i];
			for($cY = $y, $y += $cnt; $cY < $y; ++$cY){
				$result[$cY] = [$b->getId(), $b->getDamage()];
			}
		}

		return $result;
	}

	protected function parsePreset($preset, $chunkX, $chunkZ){
		$this->preset = $preset;
		$preset = explode(";", $preset);
		$version = (int) $preset[0];
		$blocks = (string) ($preset[1] ?? "");
		$biome = (int) ($preset[2] ?? 1);
		$options = (string) ($preset[3] ?? "");
		$this->structure = self::parseLayers($blocks);

		$this->chunks = [];

		$this->floorLevel = $y = count($this->structure);

		$this->chunk = clone $this->level->getChunk($chunkX, $chunkZ);
		$this->chunk->setGenerated();

		for($Z = 0; $Z < 16; ++$Z){
			for($X = 0; $X < 16; ++$X){
				$this->chunk->setBiomeId($X, $Z, $biome);
			}
		}

		$count = count($this->structure);
		for($sy = 0; $sy < $count; $sy += 16){
			$subchunk = $this->chunk->getSubChunk($sy >> 4, true);
			for($y = 0; $y < 16 and isset($this->structure[$y | $sy]); ++$y){
				list($id, $meta) = $this->structure[$y | $sy];

				for($Z = 0; $Z < 16; ++$Z){
					for($X = 0; $X < 16; ++$X){
						$subchunk->setBlock($X, $y, $Z, $id, $meta);
					}
				}
			}
		}

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

	public function init(ChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;

		/*
		  // Commented out : We want to delay this
		if(isset($this->options["preset"]) and $this->options["preset"] != ""){
			$this->parsePreset($this->options["preset"]);
		}else{
			$this->parsePreset($this->preset);
		}
		*/
	}

	public function generateChunk(int $chunkX, int $chunkZ){
		if($this->chunk === null){
			if(isset($this->options["preset"]) and $this->options["preset"] != ""){
				$this->parsePreset($this->options["preset"], $chunkX, $chunkZ);
			}else{
				$this->parsePreset($this->preset, $chunkX, $chunkZ);
			}
		}
		$chunk = clone $this->chunk;
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->level->setChunk($chunkX, $chunkZ, $chunk);
	}

	public function populateChunk(int $chunkX, int $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

	}

	public function getSpawn() : Vector3{
		return new Vector3(128, $this->floorLevel, 128);
	}
}
