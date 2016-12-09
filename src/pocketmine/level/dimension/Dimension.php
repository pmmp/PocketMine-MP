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

	const SKY_COLOUR_BLUE = 0;
	const SKY_COLOUR_RED = 1;
	const SKY_COLOUR_PURPLE_STATIC = 2;

	/** @var Level */
	protected $level;
	/** @var string */
	protected $name;
	/** @var DimensionType */
	protected $dimensionType;
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
	 * @param string $name   the dimension's display name
	 * @param int    $typeId defaults to Overworld, used to initialise dimension properties. Must be a constant from {@link DimensionType}
	 */
	public function __construct(string $name, int $typeId = DimensionType::OVERWORLD){
		$this->name = $name;
		$this->dimensionType = DimensionType::get($typeId);
		if($this->dimensionType === null){ //invalid dimension type
			throw new \InvalidArgumentException("Invalid dimension type ID $typeId");
		}
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
	 * Returns a DimensionType object containing immutable dimension properties
	 *
	 * @return DimensionType
	 */
	public function getDimensionType() : DimensionType{
		return $this->dimensionType;
	}

	/**
	 * Sets the dimension type of this dimension.
	 *
	 * @param int $typeId the ID of the dimension type. See {@link DimensionType} for a list of possible constant values.
	 *
	 * @throws \InvalidArgumentException if the specified dimension type ID was not recognised
	 */
	public function setDimensionType(int $typeId){
		if(!(($type = DimensionType::get($typeId)) instanceof DimensionType)){
			throw new \InvalidArgumentException("Invalid dimension type ID $typeId");
		}
		$this->dimensionType = $type;
		//TODO: update sky colours seen by clients, remove skylight from chunks for The End and Nether
	}

	/**
	 * Returns the dimension's ID. Unique within levels only.
	 * This is used for world saves.
	 *
	 * NOTE: For vanilla dimensions, this will NOT match the folder name in Anvil/MCRegion formats due to inconsistencies in dimension
	 * IDs between PC and PE. For example the Nether has saveID 1, but will be saved in the DIM-1 folder in the world save.
	 * Custom dimensions will be consistent.
	 *
	 * @return int
	 */
	public function getSaveId() : int{
		return $this->saveId;
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
}