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

use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\StructureSettings;

class StructureTemplateDataRequestPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_TEMPLATE_DATA_REQUEST_PACKET;

	public const TYPE_ALWAYS_LOAD = 1;
	public const TYPE_CREATE_AND_LOAD = 2;

	/** @var string */
	public $structureTemplateName;
	/** @var int */
	public $structureBlockX;
	/** @var int */
	public $structureBlockY;
	/** @var int */
	public $structureBlockZ;
	/** @var StructureSettings */
	public $structureSettings;
	/** @var int */
	public $structureTemplateResponseType;

	protected function decodePayload() : void{
		$this->structureTemplateName = $this->getString();
		$this->getBlockPosition($this->structureBlockX, $this->structureBlockY, $this->structureBlockZ);
		$this->structureSettings = $this->getStructureSettings();
		$this->structureTemplateResponseType = $this->getByte();
	}

	protected function encodePayload() : void{
		$this->putString($this->structureTemplateName);
		$this->putBlockPosition($this->structureBlockX, $this->structureBlockY, $this->structureBlockZ);
		$this->putStructureSettings($this->structureSettings);
		$this->putByte($this->structureTemplateResponseType);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleStructureTemplateDataRequest($this);
	}
}
