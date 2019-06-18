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
use pocketmine\player\Player;

abstract class ContainerInventory extends BaseInventory{
	/** @var Vector3 */
	protected $holder;

	public function __construct(Vector3 $holder, int $size, array $items = []){
		$this->holder = $holder;
		parent::__construct($size, $items);
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);

		$windowId = $who->getWindowId($this);
		$holder = $this->getHolder();
		if($holder instanceof Entity){
			$who->sendDataPacket(ContainerOpenPacket::entityInv($windowId, $this->getNetworkType(), $holder->getId()));
		}elseif($holder instanceof Vector3){
			$who->sendDataPacket(ContainerOpenPacket::blockInv($windowId, $this->getNetworkType(), $holder->getFloorX(), $holder->getFloorY(), $holder->getFloorZ()));
		}

		$who->getNetworkSession()->syncInventoryContents($this);
	}

	public function onClose(Player $who) : void{
		$who->sendDataPacket(ContainerClosePacket::create($who->getWindowId($this)));
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
