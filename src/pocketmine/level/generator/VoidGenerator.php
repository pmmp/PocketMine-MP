<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class VoidGenerator extends Generator{
	/** @var Chunk */
	private $emptyChunk = null;

	public function __construct(array $options = []){
		$this->generateEmptyChunk();
	}

	public function init(ChunkManager $level, Random $random) : void{
		parent::init($level, $random);
	}

	public function getSettings() : array{
		return [];
	}

	public function getName() : string{
		return "Void";
	}

	public function generateEmptyChunk() : void{
		$chunk = new Chunk(0, 0);
		$chunk->setGenerated();

		for($Z = 0; $Z < 16; ++$Z){
			for($X = 0; $X < 16; ++$X){
				$chunk->setBiomeId($X, $Z, 1);
				for($y = 0; $y < 256; ++$y){
					$chunk->setBlock($X, $y, $Z, Block::AIR, 0);
				}
			}
		}

		$this->emptyChunk = $chunk;
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$chunk = clone $this->emptyChunk;

		$spawn = $this->getSpawn();
		if(($spawn->x >> 4) === $chunkX and ($spawn->z >> 4) === $chunkZ){
			//why?
		}

		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);

		$this->level->setChunk($chunkX, $chunkZ, $chunk);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{

	}

	public function getSpawn() : Vector3{
		return new Vector3(128, 72, 128);
	}

}