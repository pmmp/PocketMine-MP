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

class PlayerArmorDamagePacket extends DataPacket/* implements ClientboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::PLAYER_ARMOR_DAMAGE_PACKET;

	private const FLAG_HEAD = 0;
	private const FLAG_CHEST = 1;
	private const FLAG_LEGS = 2;
	private const FLAG_FEET = 3;

	/** @var int|null */
	private $headSlotDamage;
	/** @var int|null */
	private $chestSlotDamage;
	/** @var int|null */
	private $legsSlotDamage;
	/** @var int|null */
	private $feetSlotDamage;

	public static function create(?int $headSlotDamage, ?int $chestSlotDamage, ?int $legsSlotDamage, ?int $feetSlotDamage) : self{
		$result = new self;
		$result->headSlotDamage = $headSlotDamage;
		$result->chestSlotDamage = $chestSlotDamage;
		$result->legsSlotDamage = $legsSlotDamage;
		$result->feetSlotDamage = $feetSlotDamage;

		return $result;
	}

	public function getHeadSlotDamage() : ?int{ return $this->headSlotDamage; }

	public function getChestSlotDamage() : ?int{ return $this->chestSlotDamage; }

	public function getLegsSlotDamage() : ?int{ return $this->legsSlotDamage; }

	public function getFeetSlotDamage() : ?int{ return $this->feetSlotDamage; }

	private function maybeReadDamage(int $flags, int $flag) : ?int{
		if(($flags & (1 << $flag)) !== 0){
			return $this->getVarInt();
		}
		return null;
	}

	protected function decodePayload() : void{
		$flags = $this->getByte();

		$this->headSlotDamage = $this->maybeReadDamage($flags, self::FLAG_HEAD);
		$this->chestSlotDamage = $this->maybeReadDamage($flags, self::FLAG_CHEST);
		$this->legsSlotDamage = $this->maybeReadDamage($flags, self::FLAG_LEGS);
		$this->feetSlotDamage = $this->maybeReadDamage($flags, self::FLAG_FEET);
	}

	private function composeFlag(?int $field, int $flag) : int{
		return $field !== null ? (1 << $flag) : 0;
	}

	private function maybeWriteDamage(?int $field) : void{
		if($field !== null){
			$this->putVarInt($field);
		}
	}

	protected function encodePayload() : void{
		$this->putByte(
			$this->composeFlag($this->headSlotDamage, self::FLAG_HEAD) |
			$this->composeFlag($this->chestSlotDamage, self::FLAG_CHEST) |
			$this->composeFlag($this->legsSlotDamage, self::FLAG_LEGS) |
			$this->composeFlag($this->feetSlotDamage, self::FLAG_FEET)
		);

		$this->maybeWriteDamage($this->headSlotDamage);
		$this->maybeWriteDamage($this->chestSlotDamage);
		$this->maybeWriteDamage($this->legsSlotDamage);
		$this->maybeWriteDamage($this->feetSlotDamage);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handlePlayerArmorDamage($this);
	}
}
