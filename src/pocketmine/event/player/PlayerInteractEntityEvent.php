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


use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class PlayerInteractEntityEvent extends PlayerEvent implements Cancellable{
	/** @var Entity */
	protected $entity;

	/** @var Item */
	protected $item;

	/** @var Vector3 */
	protected $clickPos;

	/**
	 * PlayerInteractEntityEvent constructor.
	 * @param Player 	$player
	 * @param Entity  	$entity
	 * @param Item 		$item
	 * @param Vector3 	$clickPos
	 */
	public function __construct(Player $player, Entity $entity, Item $item, Vector3 $clickPos){
		$this->player = $player;
		$this->entity = $entity;
		$this->item = $item;
		$this->clickPos = $clickPos;
	}

	/**
	 * @return Entity
	 */
	public function getEntity() : Entity{
		return $this->entity;
	}

	/**
	 * @return Item
	 */
	public function getItem() : Item{
		return $this->item;
	}

	/**
	 * @return Vector3
	 */
	public function getClickPos() : Vector3{
		return $this->clickPos;
	}
}