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

namespace pocketmine\world\utils;

use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;

class SubChunkExplorer{
	/** @var ChunkManager */
	protected $world;

	/** @var Chunk|null */
	public $currentChunk;
	/** @var SubChunk|null */
	public $currentSubChunk;

	/** @var int */
	protected $currentX;
	/** @var int */
	protected $currentY;
	/** @var int */
	protected $currentZ;

	public function __construct(ChunkManager $world){
		$this->world = $world;
	}

	/**
	 * @phpstan-return SubChunkExplorerStatus::*
	 */
	public function moveTo(int $x, int $y, int $z) : int{
		$newChunkX = $x >> SubChunk::COORD_BIT_SIZE;
		$newChunkZ = $z >> SubChunk::COORD_BIT_SIZE;
		if($this->currentChunk === null or $this->currentX !== $newChunkX or $this->currentZ !== $newChunkZ){
			$this->currentX = $newChunkX;
			$this->currentZ = $newChunkZ;
			$this->currentSubChunk = null;

			$this->currentChunk = $this->world->getChunk($this->currentX, $this->currentZ);
			if($this->currentChunk === null){
				return SubChunkExplorerStatus::INVALID;
			}
		}

		$newChunkY = $y >> SubChunk::COORD_BIT_SIZE;
		if($this->currentSubChunk === null or $this->currentY !== $newChunkY){
			$this->currentY = $newChunkY;

			if($this->currentY < 0 or $this->currentY >= $this->currentChunk->getHeight()){
				$this->currentSubChunk = null;
				return SubChunkExplorerStatus::INVALID;
			}

			$this->currentSubChunk = $this->currentChunk->getSubChunk($newChunkY);
			return SubChunkExplorerStatus::MOVED;
		}

		return SubChunkExplorerStatus::OK;
	}

	/**
	 * @phpstan-return SubChunkExplorerStatus::*
	 */
	public function moveToChunk(int $chunkX, int $chunkY, int $chunkZ) : int{
		//this is a cold path, so we don't care much if it's a bit slower (extra fcall overhead)
		return $this->moveTo($chunkX << SubChunk::COORD_BIT_SIZE, $chunkY << SubChunk::COORD_BIT_SIZE, $chunkZ << SubChunk::COORD_BIT_SIZE);
	}

	/**
	 * Returns whether we currently have a valid terrain pointer.
	 */
	public function isValid() : bool{
		return $this->currentSubChunk !== null;
	}

	public function invalidate() : void{
		$this->currentChunk = null;
		$this->currentSubChunk = null;
	}
}
