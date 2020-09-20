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

namespace pocketmine\world;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Limits;
use pocketmine\world\format\Chunk;
use pocketmine\world\utils\SubChunkExplorer;

class SimpleChunkManager implements ChunkManager{

	/** @var Chunk[] */
	protected $chunks = [];

	/** @var int */
	protected $worldHeight;

	/** @var SubChunkExplorer */
	protected $terrainPointer;

	/**
	 * SimpleChunkManager constructor.
	 */
	public function __construct(int $worldHeight = World::Y_MAX){
		$this->worldHeight = $worldHeight;
		$this->terrainPointer = new SubChunkExplorer($this);
	}

	public function getBlockAt(int $x, int $y, int $z) : Block{
		if($this->terrainPointer->moveTo($x, $y, $z, false)){
			return BlockFactory::getInstance()->fromFullBlock($this->terrainPointer->currentSubChunk->getFullBlock($x & 0xf, $y & 0xf, $z & 0xf));
		}
		return VanillaBlocks::AIR();
	}

	public function setBlockAt(int $x, int $y, int $z, Block $block) : void{
		if($this->terrainPointer->moveTo($x, $y, $z, true)){
			$this->terrainPointer->currentSubChunk->setFullBlock($x & 0xf, $y & 0xf, $z & 0xf, $block->getFullId());
			$this->terrainPointer->currentChunk->setDirtyFlag(Chunk::DIRTY_FLAG_TERRAIN, true);
		}else{
			throw new \InvalidArgumentException("Cannot set block at coordinates x=$x,y=$y,z=$z, terrain is not loaded or out of bounds");
		}
	}

	public function getChunk(int $chunkX, int $chunkZ, bool $create = false) : ?Chunk{
		$hash = World::chunkHash($chunkX, $chunkZ);
		return $this->chunks[$hash] ?? ($create ? $this->chunks[$hash] = new Chunk($chunkX, $chunkZ) : null);
	}

	public function setChunk(int $chunkX, int $chunkZ, ?Chunk $chunk) : void{
		if($chunk === null){
			unset($this->chunks[World::chunkHash($chunkX, $chunkZ)]);
			return;
		}
		$this->chunks[World::chunkHash($chunkX, $chunkZ)] = $chunk;
	}

	public function cleanChunks() : void{
		$this->chunks = [];
	}

	public function getWorldHeight() : int{
		return $this->worldHeight;
	}

	public function isInWorld(int $x, int $y, int $z) : bool{
		return (
			$x <= Limits::INT32_MAX and $x >= Limits::INT32_MIN and
			$y < $this->worldHeight and $y >= 0 and
			$z <= Limits::INT32_MAX and $z >= Limits::INT32_MIN
		);
	}
}
