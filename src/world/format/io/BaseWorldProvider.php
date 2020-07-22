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

namespace pocketmine\world\format\io;

use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\WorldException;
use SOFe\Pathetique\Path;
use function file_exists;

abstract class BaseWorldProvider implements WorldProvider{
	/** @var Path */
	protected $path;
	/** @var WorldData */
	protected $worldData;

	public function __construct(Path $path){
		if(!$path->exists()){
			throw new WorldException("World does not exist");
		}

		$this->path = $path;
		$this->worldData = $this->loadLevelData();
	}

	/**
	 * @throws CorruptedWorldException
	 * @throws UnsupportedWorldFormatException
	 */
	abstract protected function loadLevelData() : WorldData;

	public function getPath() : Path{
		return $this->path;
	}

	public function getWorldData() : WorldData{
		return $this->worldData;
	}

	/**
	 * @throws CorruptedChunkException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?Chunk{
		return $this->readChunk($chunkX, $chunkZ);
	}

	public function saveChunk(Chunk $chunk) : void{
		if(!$chunk->isGenerated()){
			throw new \InvalidStateException("Cannot save un-generated chunk");
		}
		$this->writeChunk($chunk);
	}

	/**
	 * @throws CorruptedChunkException
	 */
	abstract protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk;

	abstract protected function writeChunk(Chunk $chunk) : void;
}
