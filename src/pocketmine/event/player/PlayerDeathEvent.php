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

use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\TextContainer;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerDeathEvent extends EntityDeathEvent{
	public static $handlerList = null;

	/** @var Player */
	protected $entity;

	/** @var TextContainer|string */
	private $deathMessage;
	private $keepInventory = false;

	/**
	 * @param Player               $entity
	 * @param Item[]               $drops
	 * @param string|TextContainer $deathMessage
	 */
	public function __construct(Player $entity, array $drops, $deathMessage){
		parent::__construct($entity, $drops);
		$this->deathMessage = $deathMessage;
	}

	/**
	 * @return Player
	 */
	public function getEntity(){
		return $this->entity;
	}

	/**
	 * @return Player
	 */
	public function getPlayer() : Player{
		return $this->entity;
	}

	/**
	 * @return TextContainer|string
	 */
	public function getDeathMessage(){
		return $this->deathMessage;
	}

	/**
	 * @param TextContainer|string $deathMessage
	 */
	public function setDeathMessage($deathMessage){
		$this->deathMessage = $deathMessage;
	}

	public function getKeepInventory() : bool{
		return $this->keepInventory;
	}

	public function setKeepInventory(bool $keepInventory){
		$this->keepInventory = $keepInventory;
	}

}
