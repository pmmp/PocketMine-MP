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

namespace pocketmine\network\mcpe\protocol\types\event;

use pocketmine\network\mcpe\protocol\EventPacket;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;

final class EntityInteractEventData implements EventData{
	public const TYPE_BREED_ENTITY = 1;
	public const TYPE_MOUNT_TAMING = 2;
	public const TYPE_TAME_WOLF_OR_OCELOT = 3;
	public const TYPE_CREATE_GOLEM = 4; // Place pumpkin block
	public const TYPE_TRIM_THE_SHEEP = 5;
	public const TYPE_MILK_A_COW = 6;
	public const TYPE_SUCCESS_TRADE = 7;
	public const TYPE_FEED_ENTITY = 8;
	public const TYPE_SET_FIRE_TO = 9; //TODO: Maybe use FlintAndSteel on Creeper

	public const TYPE_NAME_ENTITY = 11; // Use TAG item on actor
	public const TYPE_LEASH = 12;
	public const TYPE_UNLEASH = 13;

	public const TYPE_TRUST = 15;
	public const TYPE_INTERACT_DOG_OR_CAT = 16; // Sit or stand

	/** @var int */
	public $mobType;
	/** @var int */
	public $interactionType;
	/** @var int */
	public $mobVariant;
	/** @var int */
	public $mobColor;

	public function id() : int{
		return EventPacket::TYPE_ENTITY_INTERACT;
	}

	public function read(NetworkBinaryStream $in) : void{
		$this->mobType = $in->getVarInt();
		$this->interactionType = $in->getVarInt();
		$this->mobVariant = $in->getVarInt();
		$this->mobColor = $in->getByte();
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->mobType);
		$out->putVarInt($this->interactionType);
		$out->putVarInt($this->mobVariant);
		$out->putByte($this->mobColor);
	}
}
