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

namespace pocketmine\inventory;

use pocketmine\entity\passive\AbstractHorse;
use pocketmine\network\mcpe\protocol\types\WindowTypes;

class HorseInventory extends ContainerInventory{
	/** @var AbstractHorse */
	protected $holder;

	public function getName() : string{
		return "Horse";
	}

	public function getDefaultSize() : int{
		return 1;
	}

	public function getNetworkType() : int{
		return WindowTypes::HORSE;
	}

	/**
	 * @return AbstractHorse
	 */
	public function getHolder(){
		return $this->holder;
	}
}