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

declare(strict_types = 1);

namespace pocketmine\level\dimension;

use pocketmine\entity\Entity;
use pocketmine\level\format\generic\GenericChunk;
use pocketmine\level\Level;
use pocketmine\network\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\tile\Tile;

abstract class Dimension{

	/** These are the Pocket Edition dimension IDs. Blame Mojang for making them different to PC. */
	const TYPE_OVERWORLD = 0;
	const TYPE_NETHER = 1;
	const TYPE_THE_END = 2;

	/** @var Level */
	protected $level;
	/** @var string */
	protected $name;
	/** @var int */
	protected $type = Dimension::TYPE_OVERWORLD;
	/** @var int */
	protected $saveId;

	/** @var int */
	protected $buildHeight = 256;

	/** @var GenericChunk[] */
	protected $chunks = [];

	/** @var DataPacket[] */
	protected $chunkCache = [];

	protected $blockCache = [];

	/** @var Player[] */
	protected $players = [];

	/** @var Entity[] */
	protected $entities = [];
	/** @var Tile[] */
	protected $tiles = [];

	/** @var Entity[] */
	public $updateEntities = [];
	/** @var Tile[] */
	public $updateTiles = [];

	protected $motionToSend = [];
	protected $moveToSend = [];

	/**
	 * @param string $name  the dimension's display name
	 * @param int    $type  defaults to Overworld, must be one of the dimension type constants at the top of this file. This will affect sky colour (blue, red, black)
	 */
	public function __construct(string $name, int $type = Dimension::TYPE_OVERWORLD){
		$this->name = $name;
		$this->type = $type;
	}

	/**
	 * Returns the parent level of this dimension, or null if the dimension has not yet been attached to a Level.
	 *
	 * @return Level|null
	 */
	public function getLevel(){
		return $this->level;
	}

	/**
	 * Sets the parent level of this dimension.
	 *
	 * @param Level $level
	 *
	 * @return bool indication of success
	 *
	 * @internal
	 */
	public function setLevel(Level $level) : bool{
		if($this->level instanceof Level){
			return false;
		}

		if(($saveId = $level->addDimension($this)) !== false){
			$this->level = $level;
			$this->saveId = $saveId;
			return true;
		}

		return false;
	}

	/**
	 * Returns the dimension's network type
	 * See the top of this file for a list of available constant values
	 * This value affects the sky colour seen by clients (blue, red, black)
	 *
	 * @return int
	 */
	public function getDimensionType() : int{
		return $this->type;
	}

	/**
	 * Returns the friendly name of this dimension.
	 *
	 * @return string
	 */
	public function getDimensionName() : string{
		return $this->name;
	}

	/**
	 * Executes ticks on this dimension
	 *
	 * @param int $currentTick
	 */
	public function doTick(int $currentTick){
		$this->doWeatherTick($currentTick);
		//TODO: More stuff
	}

	/**
	 * Performs weather ticks
	 *
	 * @param int $currentTick
	 */
	protected function doWeatherTick(int $currentTick){
		
	}

	/**
	 * Returns whether time ticking affects this Dimension. Used to decide whether
	 * to bother sending time to clients in this dimension.
	 *
	 * @internal
	 *
	 * @return bool
	 */
	public function hasTimeTicks() : bool{
		return true;
	}
}