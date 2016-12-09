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

namespace pocketmine\level\dimension;

/**
 * Manages dimension properties that cannot be modified freely due to client limitations.
 * For example, the Nether build height is capped at 128 and has no sky light.
 */
class DimensionType{

	const OVERWORLD = 0;
	const NETHER = 1;
	const THE_END = 2;

	/** @var DimensionType[] */
	private static $types = [];

	public static function init(){
		self::$types = [];
		self::$types[self::OVERWORLD] = new DimensionType(Dimension::SKY_COLOUR_BLUE, 256, true, true);
		self::$types[self::NETHER] = new DimensionType(Dimension::SKY_COLOUR_RED, 128, false, false);
		self::$types[self::THE_END] = new DimensionType(Dimension::SKY_COLOUR_PURPLE_STATIC, 256, false, false);
	}

	/**
	 * Returns a dimension type by dimension ID.
	 *
	 * @return DimensionType|null
	 */
	public static function get(int $type){
		return self::$types[$type] ?? null;
	}

	private $skyColour;
	private $buildHeight;
	private $hasSkyLight;
	private $hasWeather;

	private function __construct(int $skyColour, int $buildHeight, bool $hasSkyLight, bool $hasWeather){
		$this->skyColour = $skyColour;
		$this->buildHeight = $buildHeight;
		$this->hasSkyLight = $hasSkyLight;
		$this->hasWeather = $hasWeather;
	}

	public function getSkyColour() : int{
		return $this->skyColour;
	}

	public function getMaxBuildHeight() : int{
		return $this->buildHeight;
	}

	public function hasSkyLight() : bool{
		return $this->hasSkyLight;
	}

	public function hasWeather() : bool{
		return $this->hasWeather;
	}
}
