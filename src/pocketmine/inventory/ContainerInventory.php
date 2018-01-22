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

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\Player;

abstract class ContainerInventory extends BaseInventory{
	/** @var Vector3 */
	protected $holder;
	
	public function __construct(Vector3 $holder, array $items = [], int $size = null, string $title = null){
		$this->holder = $holder;
		parent::__construct($items, $size, $title);
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);
		$pk = new ContainerOpenPacket();
		$pk->windowId = $who->getWindowId($this);
		$pk->type = $this->getNetworkType();
		$holder = $this->getHolder();

		$pk->x = $pk->y = $pk->z = 0;
		$pk->entityUniqueId = -1;

		if($holder instanceof Entity){
			$pk->entityUniqueId = $holder->getId();
		}elseif($holder instanceof Vector3){
			$pk->x = (int) $holder->getX();
			$pk->y = (int) $holder->getY();
			$pk->z = (int) $holder->getZ();
		}

		$who->dataPacket($pk);

		$this->sendContents($who);
	}

	public function onClose(Player $who) : void{
		$pk = new ContainerClosePacket();
		$pk->windowId = $who->getWindowId($this);
		$who->dataPacket($pk);
		parent::onClose($who);
	}

	/**
	 * Returns the Minecraft PE inventory type used to show the inventory window to clients.
	 * @return int
	 */
	abstract public function getNetworkType() : int;

	/**
	 * @return Vector3
	 */
	public function getHolder(){
		return $this->holder;
	}
}
