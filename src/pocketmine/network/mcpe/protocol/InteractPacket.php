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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\network\mcpe\NetworkSession;

class InteractPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::INTERACT_PACKET;

	/**
	 * @deprecated
	 * This action is no longer used as of MCPE 1.2.0.2, this is now handled in InventoryTransactionPacket
	 */
	const ACTION_RIGHT_CLICK = 1;
	/**
	 * @deprecated
	 * This action is no longer used as of MCPE 1.2.0.2, this is now handled in InventoryTransactionPacket
	 */
	const ACTION_LEFT_CLICK = 2;

	const ACTION_LEAVE_VEHICLE = 3;
	const ACTION_MOUSEOVER = 4;

	const ACTION_OPEN_INVENTORY = 6;

	/** @var int */
	public $action;
	/** @var int */
	public $target;

	/** @var float */
	public $x;
	/** @var float */
	public $y;
	/** @var float */
	public $z;

	public function decodePayload(){
		$this->action = $this->getByte();
		$this->target = $this->getEntityRuntimeId();

		if($this->action === self::ACTION_MOUSEOVER){
			$this->x = $this->getLFloat();
			$this->y = $this->getLFloat();
			$this->z = $this->getLFloat();
		}
	}

	public function encodePayload(){
		$this->putByte($this->action);
		$this->putEntityRuntimeId($this->target);

		if($this->action === self::ACTION_MOUSEOVER){
			$this->putLFloat($this->x);
			$this->putLFloat($this->y);
			$this->putLFloat($this->z);
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleInteract($this);
	}

}
