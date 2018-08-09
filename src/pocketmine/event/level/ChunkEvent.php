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

namespace pocketmine\event\level;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;

/**
 * Chunk-related events
 */
abstract class ChunkEvent extends LevelEvent{
	/** @var Chunk */
	private $chunk;

	/**
	 * @param Level $level
	 * @param Chunk $chunk
	 */
	public function __construct(Level $level, Chunk $chunk){
		parent::__construct($level);
		$this->chunk = $chunk;
	}

	/**
	 * @return Chunk
	 */
	public function getChunk() : Chunk{
		return $this->chunk;
	}
}