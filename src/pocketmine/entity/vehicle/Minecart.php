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

namespace pocketmine\entity\vehicle;

use pocketmine\entity\Vehicle;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;

class Minecart extends Vehicle{
	public const NETWORK_ID = self::MINECART;

	public $height = 0.7;
	public $width = 0.98;

	protected $gravity = 0.5;
	protected $drag = 0.1;

	protected function initEntity() : void{
		$this->setHealth(6);

		parent::initEntity();
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3{
		return new Vector3($seatNumber * 0.8, 0, 0);
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::MINECART)
		];
	}
}