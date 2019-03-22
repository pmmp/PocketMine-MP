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

namespace pocketmine\inventory;

use pocketmine\Player;

class PlayerCursorInventory extends BaseInventory{
	/** @var Player */
	protected $holder;

	public function __construct(Player $holder){
		$this->holder = $holder;
		parent::__construct(1);
	}

	public function setSize(int $size) : void{
		throw new \BadMethodCallException("Cursor can only carry one item at a time");
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Player
	 */
	public function getHolder(){
		return $this->holder;
	}
}
