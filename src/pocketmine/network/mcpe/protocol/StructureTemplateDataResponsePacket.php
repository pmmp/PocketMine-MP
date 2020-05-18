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

class StructureTemplateDataResponsePacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_TEMPLATE_DATA_RESPONSE_PACKET;

	/** @var string */
	public $structureTemplateName;
	/** @var string|null */
	public $namedtag;

	protected function decodePayload() : void{
		$this->structureTemplateName = $this->getString();
		if($this->getBool()){
			$this->namedtag = $this->getRemaining();
		}
	}

	protected function encodePayload() : void{
		$this->putString($this->structureTemplateName);
		$this->putBool($this->namedtag !== null);
		if($this->namedtag !== null){
			$this->put($this->namedtag);
		}
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleStructureTemplateDataResponse($this);
	}
}
