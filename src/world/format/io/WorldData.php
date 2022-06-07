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

	public function getName() : string;

	/**
	 * Returns the generator name
	 */
	public function getGenerator() : string;

	public function getGeneratorOptions() : string;

	public function getSeed() : int;

	public function getTime() : int;

	public function setTime(int $value) : void;

	public function getSpawn() : Vector3;

	public function setSpawn(Vector3 $pos) : void;

	/**
	 * Returns the world difficulty. This will be one of the World constants.
	 */
	public function getDifficulty() : int;

	/**
	 * Sets the world difficulty.
	 */
	public function setDifficulty(int $difficulty) : void;

	/**
	 * Returns the time in ticks to the next rain level change.
	 */
	public function getRainTime() : int;

	/**
	 * Sets the time in ticks to the next rain level change.
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
	 */
	public function getLightningTime() : int;

	/**
	 * Sets the time in ticks to the next lightning level change.
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
