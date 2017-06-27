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

namespace pocketmine\entity\object;


class PaintingMotive{
	/** @var PaintingMotive[] */
	protected static $motives = [];

	public static function init(){
		foreach([
			new PaintingMotive("Kebab"),
			new PaintingMotive("Aztec"),
			new PaintingMotive("Alban"),
			new PaintingMotive("Aztec2"),
			new PaintingMotive("Bomb"),
			new PaintingMotive("Plant"),
			new PaintingMotive("Wasteland"),
			new PaintingMotive("Pool", 2, 1),
			new PaintingMotive("Courbet", 2, 1),
			new PaintingMotive("Sea", 2, 1),
			new PaintingMotive("Sunset", 2, 1),
			new PaintingMotive("Creebet", 2, 1),
			new PaintingMotive("Wanderer", 1, 2),
			new PaintingMotive("Graham", 1, 2),
			new PaintingMotive("Match", 2, 2),
			new PaintingMotive("Bust", 2, 2),
			new PaintingMotive("Stage", 2, 2),
			new PaintingMotive("Void", 2, 2),
			new PaintingMotive("SkullAndRoses", 2, 2),
			new PaintingMotive("Wither", 2, 2),
			new PaintingMotive("Fighters", 4, 2),
			new PaintingMotive("Pointer", 4, 4),
			new PaintingMotive("Pigscene", 4, 4),
			new PaintingMotive("BurningSkull", 4, 4),
			new PaintingMotive("Skeleton", 4, 3),
			new PaintingMotive("DonkeyKong", 4, 3),
			new PaintingMotive("Earth", 2, 2),
			new PaintingMotive("Wind", 2, 2),
			new PaintingMotive("Fire", 2, 2),
			new PaintingMotive("Water", 2, 2)
		] as $motive){
			self::registerMotive($motive);
		}
	}

	/**
	 * @param PaintingMotive $motive
	 */
	public static function registerMotive(PaintingMotive $motive){
		self::$motives[$motive->getName()] = $motive;
	}

	/**
	 * @return PaintingMotive
	 */
	public static function pickRandomMotive() : PaintingMotive{
		return self::$motives[array_rand(self::$motives)];
	}

	/**
	 * @param string $name
	 * @return PaintingMotive|null
	 */
	public static function getMotiveByName(string $name){
		return self::$motives[$name] ?? null;
	}

	/** @var string */
	protected $name;
	/** @var int */
	protected $width;
	/** @var int */
	protected $height;


	public function __construct(string $name, int $width = 1, int $height = 1){
		$this->name = $name;
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getWidth() : int{
		return $this->width;
	}

	/**
	 * @return int
	 */
	public function getHeight() : int{
		return $this->height;
	}

	public function __toString() : string{
		return "PaintingMotive(name: " . $this->getName() . ", height: " . $this->getHeight() . ", width: " . $this->getWidth() . ")";
	}


}