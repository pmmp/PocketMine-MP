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

use pocketmine\block\BlockIds;
use pocketmine\math\Vector3;

class VoidGenerator extends Generator{

	public function __construct(array $options = []){
		// NOOP
	}

	public function getSettings() : array{
		return [];
	}

	public function getName() : string{
		return "void";
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$spawn = $this->getSpawn();

		if($spawn->x >> 4 === $chunkX and $spawn->z >> 4 === $chunkZ){
			$chunk->setBlock(0, $spawn->y - 1, 0, BlockIds::GRASS, 0);
		}

		$chunk->setGenerated(true);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		// NOOP
	}

	public function getSpawn() : Vector3{
		return new Vector3(128, 72, 128);
	}
}