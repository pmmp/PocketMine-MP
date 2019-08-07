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

use pocketmine\math\Vector3;

interface WorldData{

	/**
	 * Saves information about the world state, such as weather, time, etc.
	 */
	public function save() : void;

	/**
	 * @return string
	 */
	public function getName() : string;

	/**
	 * Returns the generator name
	 *
	 * @return string
	 */
	public function getGenerator() : string;

	/**
	 * @return array
	 */
	public function getGeneratorOptions() : array;

	/**
	 * @return int
	 */
	public function getSeed() : int;



	/**
	 * @return int
	 */
	public function getTime() : int;

	/**
	 * @param int $value
	 */
	public function setTime(int $value) : void;


	/**
	 * @return Vector3
	 */
	public function getSpawn() : Vector3;

	/**
	 * @param Vector3 $pos
	 */
	public function setSpawn(Vector3 $pos) : void;

	/**
	 * Returns the world difficulty. This will be one of the World constants.
	 * @return int
	 */
	public function getDifficulty() : int;

	/**
	 * Sets the world difficulty.
	 *
	 * @param int $difficulty
	 */
	public function setDifficulty(int $difficulty) : void;

	/**
	 * Returns the time in ticks to the next rain level change.
	 * @return int
	 */
	public function getRainTime() : int;

	/**
	 * Sets the time in ticks to the next rain level change.
	 * @param int $ticks
	 */
	public function setRainTime(int $ticks) : void;

	/**
	 * @return float 0.0 - 1.0
	 */
	public function getRainLevel() : float;

	/**
	 * @param float $level 0.0 - 1.0
	 */
	public function setRainLevel(float $level) : void;

	/**
	 * Returns the time in ticks to the next lightning level change.
	 * @return int
	 */
	public function getLightningTime() : int;

	/**
	 * Sets the time in ticks to the next lightning level change.
	 * @param int $ticks
	 */
	public function setLightningTime(int $ticks) : void;

	/**
	 * @return float 0.0 - 1.0
	 */
	public function getLightningLevel() : float;

	/**
	 * @param float $level 0.0 - 1.0
	 */
	public function setLightningLevel(float $level) : void;
}
