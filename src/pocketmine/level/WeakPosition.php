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

namespace pocketmine\level;

use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\LevelException;

class WeakPosition extends Position{

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 * @param Level $level
	 */
	public function __construct($x = 0, $y = 0, $z = 0, Level $level = null){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->levelId = ($level !== null ? $level->getId() : -1);
	}

	public static function fromObject(Vector3 $pos, Level $level = null){
		return new WeakPosition($pos->x, $pos->y, $pos->z, $level);
	}

	/**
	 * @return Level|null
	 */
	public function getLevel(){
		return Server::getInstance()->getLevel($this->levelId);
	}

	public function setLevel(Level $level){
		$this->levelId = ($level !== null ? $level->getId() : -1);
		return $this;
	}

	/**
	 * Returns a side Vector
	 *
	 * @param int $side
	 * @param int $step
	 *
	 * @return WeakPosition
	 *
	 * @throws LevelException
	 */
	public function getSide($side, $step = 1){
		assert($this->isValid());

		return WeakPosition::fromObject(parent::getSide($side, $step), $this->level);
	}

	public function __toString(){
		return "Weak" . parent::__toString();
	}
}