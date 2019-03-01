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

namespace pocketmine\event\player;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Called when a player interacts with an entity
 */
class PlayerInteractEntityEvent extends PlayerEvent implements Cancellable{

	/** @var Entity */
	protected $entity;
	/** @var Item */
	protected $item;
	/** @var Vector3 */
	protected $clickPos;

	/**
	 * @param Player  $player
	 * @param Entity  $entity
	 * @param Item    $item
	 * @param Vector3 $clickPos
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
	public function getClickPosition() : Vector3{
		return $this->clickPos;
	}
}