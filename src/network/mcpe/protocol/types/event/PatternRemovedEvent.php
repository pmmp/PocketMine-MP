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

final class PatternRemovedEvent implements EventData{
	/** @var int */
	public $itemId;
	/** @var int */
	public $itemAux;
	/** @var int */
	public $layerIndex;
	/** @var int */
	public $patternId;
	/** @var int */
	public $patternColor;

	public static function id() : int{
		return EventPacket::TYPE_PATTERN_REMOVED;
	}

	public function read(NetworkBinaryStream $in) : void{
		$this->itemId = $in->getVarInt();
		$this->itemAux = $in->getVarInt();
		$this->layerIndex = $in->getVarInt();
		$this->patternId = $in->getVarInt();
		$this->patternColor = $in->getVarInt();
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->itemId);
		$out->putVarInt($this->itemAux);
		$out->putVarInt($this->layerIndex);
		$out->putVarInt($this->patternId);
		$out->putVarInt($this->patternColor);
	}

	public function equals(EventData $other) : bool{
		return $other instanceof $this and $other->itemId === $this->itemId and $other->itemAux === $this->itemAux and $other->layerIndex === $this->layerIndex and $other->patternId === $this->patternId and $other->patternColor === $this->patternColor;
	}
}
