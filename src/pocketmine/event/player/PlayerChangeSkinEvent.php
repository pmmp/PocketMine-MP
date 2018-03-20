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

namespace pocketmine\event\player;

use pocketmine\entity\Skin;
use pocketmine\event\Cancellable;
use pocketmine\Player;

/**
 * Called when a player changes their skin in-game.
 */
class PlayerChangeSkinEvent extends PlayerEvent implements Cancellable{
	/** @var Skin */
	private $oldSkin;
	/** @var Skin */
	private $newSkin;

	/**
	 * @param Player $player
	 * @param Skin   $oldSkin
	 * @param Skin   $newSkin
	 */
	public function __construct(Player $player, Skin $oldSkin, Skin $newSkin){
		$this->player = $player;
		$this->oldSkin = $oldSkin;
		$this->newSkin = $newSkin;
	}

	/**
	 * @return Skin
	 */
	public function getOldSkin() : Skin{
		return $this->oldSkin;
	}

	/**
	 * @return Skin
	 */
	public function getNewSkin() : Skin{
		return $this->newSkin;
	}

	/**
	 * @param Skin $skin
	 * @throws \InvalidArgumentException if the specified skin is not valid
	 */
	public function setNewSkin(Skin $skin) : void{
		if(!$skin->isValid()){
			throw new \InvalidArgumentException("Skin format is invalid");
		}

		$this->newSkin = $skin;
	}

}
