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

namespace pocketmine\maps;

use pocketmine\Player;

class MapInfo{

	/** @var Player */
	public $player;
	public $textureCheckCounter = 0;
	public $packetSendTimer = 0;
	public $forceUpdate = true;

	public $minX = 0;
	public $minY = 0;
	public $maxX = 127;
	public $maxY = 127;

	public function __construct(Player $player){
		$this->player = $player;
	}

	/**
	 * Calculates map canvas
	 *
	 * @param int $x
	 * @param int $y
	 */
	public function updateTextureAt(int $x, int $y) : void{
		if($this->forceUpdate){
			$this->minX = min($this->minX, $x);
			$this->minY = min($this->minY, $y);
			$this->maxX = min($this->maxX, $x);
			$this->maxY = min($this->maxY, $y);
		}else{
			$this->forceUpdate = true;
			$this->minX = $x;
			$this->minY = $y;
			$this->maxX = $x;
			$this->maxY = $y;
		}
	}
}