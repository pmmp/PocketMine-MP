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
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class AdventureSettingsPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::ADVENTURE_SETTINGS_PACKET;

	const PERMISSION_NORMAL = 0;
	const PERMISSION_OPERATOR = 1;
	const PERMISSION_HOST = 2;
	const PERMISSION_AUTOMATION = 3;
	const PERMISSION_ADMIN = 4;

	//TODO: check level 3

	/**
	 * This constant is used to identify flags that should be set on the second field. In a sensible world, these
	 * flags would all be set on the same packet field, but as of MCPE 1.2, the new abilities flags have for some
	 * reason been assigned a separate field.
	 */
	const BITFLAG_SECOND_SET = 1 << 16;

	const WORLD_IMMUTABLE = 0x01;
	const NO_PVP = 0x02;

	const AUTO_JUMP = 0x20;
	const ALLOW_FLIGHT = 0x40;
	const NO_CLIP = 0x80;
	const WORLD_BUILDER = 0x100;
	const FLYING = 0x200;
	const MUTED = 0x400;

	const BUILD_AND_MINE = 0x01 | self::BITFLAG_SECOND_SET;
	const DOORS_AND_SWITCHES = 0x02 | self::BITFLAG_SECOND_SET;
	const OPEN_CONTAINERS = 0x04 | self::BITFLAG_SECOND_SET;
	const ATTACK_PLAYERS = 0x08 | self::BITFLAG_SECOND_SET;
	const ATTACK_MOBS = 0x10 | self::BITFLAG_SECOND_SET;
	const OPERATOR = 0x20 | self::BITFLAG_SECOND_SET;
	const TELEPORT = 0x80 | self::BITFLAG_SECOND_SET;

	/** @var int */
	public $flags = 0;
	/** @var int */
	public $commandPermission = self::PERMISSION_NORMAL;
	/** @var int */
	public $flags2 = -1;
	/** @var int */
	public $playerPermission = PlayerPermissions::MEMBER;
	/** @var int */
	public $customFlags = 0; //...
	/** @var int */
	public $entityUniqueId; //This is a little-endian long, NOT a var-long. (WTF Mojang)

	protected function decodePayload(){
		$this->flags = $this->getUnsignedVarInt();
		$this->commandPermission = $this->getUnsignedVarInt();
		$this->flags2 = $this->getUnsignedVarInt();
		$this->playerPermission = $this->getUnsignedVarInt();
		$this->customFlags = $this->getUnsignedVarInt();
		$this->entityUniqueId = $this->getLLong();
	}

	protected function encodePayload(){
		$this->putUnsignedVarInt($this->flags);
		$this->putUnsignedVarInt($this->commandPermission);
		$this->putUnsignedVarInt($this->flags2);
		$this->putUnsignedVarInt($this->playerPermission);
		$this->putUnsignedVarInt($this->customFlags);
		$this->putLLong($this->entityUniqueId);
	}

	public function getFlag(int $flag) : bool{
		if($flag & self::BITFLAG_SECOND_SET){
			return ($this->flags2 & $flag) !== 0;
		}

		return ($this->flags & $flag) !== 0;
	}

	public function setFlag(int $flag, bool $value){
		if($flag & self::BITFLAG_SECOND_SET){
			$flagSet =& $this->flags2;
		}else{
			$flagSet =& $this->flags;
		}

		if($value){
			$flagSet |= $flag;
		}else{
			$flagSet &= ~$flag;
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleAdventureSettings($this);
	}

}