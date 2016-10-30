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

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>


class AdventureSettingsPacket extends DataPacket{
	const NETWORK_ID = Info::ADVENTURE_SETTINGS_PACKET;

	const PERMISSION_NORMAL = 0;
	const PERMISSION_OPERATOR = 1;
	const PERMISSION_HOST = 2;
	const PERMISSION_AUTOMATION = 3;
	const PERMISSION_ADMIN = 4;

	public $worldImmutable;
	public $noPvp;
	public $noPvm;
	public $noMvp;

	public $autoJump;
	public $allowFlight;
	public $noClip;
	public $isFlying;

	/*
	 bit mask | flag name
	0x00000001 world_immutable
	0x00000002 no_pvp
	0x00000004 no_pvm
	0x00000008 no_mvp
	0x00000010 ?
	0x00000020 auto_jump
	0x00000040 allow_fly
	0x00000080 noclip
	0x00000100 ?
	0x00000200 is_flying
	*/

	public $flags = 0;
	public $userPermission;

	public function decode(){
		$this->flags = $this->getUnsignedVarInt();
		$this->userPermission = $this->getUnsignedVarInt();

		$this->worldImmutable = (bool) ($this->flags & 1);
		$this->noPvp          = (bool) ($this->flags & (1 << 1));
		$this->noPvm          = (bool) ($this->flags & (1 << 2));
		$this->noMvp          = (bool) ($this->flags & (1 << 3));

		$this->autoJump       = (bool) ($this->flags & (1 << 5));
		$this->allowFlight    = (bool) ($this->flags & (1 << 6));
		$this->noClip         = (bool) ($this->flags & (1 << 7));

		$this->isFlying       = (bool) ($this->flags & (1 << 9));
	}

	public function encode(){
		$this->reset();

		$this->flags |= ((int) $this->worldImmutable);
		$this->flags |= ((int) $this->noPvp)       << 1;
		$this->flags |= ((int) $this->noPvm)       << 2;
		$this->flags |= ((int) $this->noMvp)       << 3;

		$this->flags |= ((int) $this->autoJump)    << 5;
		$this->flags |= ((int) $this->allowFlight) << 6;
		$this->flags |= ((int) $this->noClip)      << 7;

		$this->flags |= ((int) $this->isFlying)    << 9;

		$this->putUnsignedVarInt($this->flags);
		$this->putUnsignedVarInt($this->userPermission);
	}

}