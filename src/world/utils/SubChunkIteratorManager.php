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

use pocketmine\utils\Utils;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;
use function assert;

class SubChunkIteratorManager{
	/** @var ChunkManager */
	public $world;

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

	/** @var \Closure|null */
	private $onSubChunkChangeFunc = null;

	public function __construct(ChunkManager $world){
		$this->world = $world;
	}

	public function moveTo(int $x, int $y, int $z, bool $create) : bool{
		if($this->currentChunk === null or $this->currentX !== ($x >> 4) or $this->currentZ !== ($z >> 4)){
			$this->currentX = $x >> 4;
			$this->currentZ = $z >> 4;
			$this->currentSubChunk = null;

			$this->currentChunk = $this->world->getChunk($this->currentX, $this->currentZ, $create);
			if($this->currentChunk === null){
				return false;
			}
		}

		if($this->currentSubChunk === null or $this->currentY !== ($y >> 4)){
			$this->currentY = $y >> 4;

			if($this->currentY < 0 or $this->currentY >= $this->currentChunk->getHeight()){
				$this->currentSubChunk = null;
				return false;
			}

			$newSubChunk = $this->currentChunk->getSubChunk($y >> 4);
			assert($newSubChunk instanceof SubChunk, "chunk inside valid bounds should always be a SubChunk instance");
			$this->currentSubChunk = $newSubChunk;
			if($this->onSubChunkChangeFunc !== null){
				($this->onSubChunkChangeFunc)();
			}
		}

		return true;
	}

	public function onSubChunkChange(\Closure $callback) : void{
		Utils::validateCallableSignature(function(){}, $callback);
		$this->onSubChunkChangeFunc = $callback;
	}

	/**
	 * Returns whether we currently have a valid terrain pointer.
	 *
	 * @return bool
	 */
	public function isValid() : bool{
		return $this->currentSubChunk !== null;
	}

	public function invalidate() : void{
		$this->currentChunk = null;
		$this->currentSubChunk = null;
	}
}
