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

class LecternUpdatePacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::LECTERN_UPDATE_PACKET;

	/** @var int */
	public $page;
	/** @var int */
	public $totalPages;
	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var bool */
	public $dropBook;

	protected function decodePayload() : void{
		$this->page = $this->buf->getByte();
		$this->totalPages = $this->buf->getByte();
		$this->buf->getBlockPosition($this->x, $this->y, $this->z);
		$this->dropBook = $this->buf->getBool();
	}

	protected function encodePayload() : void{
		$this->buf->putByte($this->page);
		$this->buf->putByte($this->totalPages);
		$this->buf->putBlockPosition($this->x, $this->y, $this->z);
		$this->buf->putBool($this->dropBook);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleLecternUpdate($this);
	}
}
