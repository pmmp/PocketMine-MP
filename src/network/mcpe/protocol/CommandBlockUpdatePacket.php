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

class CommandBlockUpdatePacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::COMMAND_BLOCK_UPDATE_PACKET;

	/** @var bool */
	public $isBlock;

	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $commandBlockMode;
	/** @var bool */
	public $isRedstoneMode;
	/** @var bool */
	public $isConditional;

	/** @var int */
	public $minecartEid;

	/** @var string */
	public $command;
	/** @var string */
	public $lastOutput;
	/** @var string */
	public $name;
	/** @var bool */
	public $shouldTrackOutput;
	/** @var int */
	public $tickDelay;
	/** @var bool */
	public $executeOnFirstTick;

	protected function decodePayload() : void{
		$this->isBlock = $this->buf->getBool();

		if($this->isBlock){
			$this->buf->getBlockPosition($this->x, $this->y, $this->z);
			$this->commandBlockMode = $this->buf->getUnsignedVarInt();
			$this->isRedstoneMode = $this->buf->getBool();
			$this->isConditional = $this->buf->getBool();
		}else{
			//Minecart with command block
			$this->minecartEid = $this->buf->getEntityRuntimeId();
		}

		$this->command = $this->buf->getString();
		$this->lastOutput = $this->buf->getString();
		$this->name = $this->buf->getString();

		$this->shouldTrackOutput = $this->buf->getBool();
		$this->tickDelay = $this->buf->getLInt();
		$this->executeOnFirstTick = $this->buf->getBool();
	}

	protected function encodePayload() : void{
		$this->buf->putBool($this->isBlock);

		if($this->isBlock){
			$this->buf->putBlockPosition($this->x, $this->y, $this->z);
			$this->buf->putUnsignedVarInt($this->commandBlockMode);
			$this->buf->putBool($this->isRedstoneMode);
			$this->buf->putBool($this->isConditional);
		}else{
			$this->buf->putEntityRuntimeId($this->minecartEid);
		}

		$this->buf->putString($this->command);
		$this->buf->putString($this->lastOutput);
		$this->buf->putString($this->name);

		$this->buf->putBool($this->shouldTrackOutput);
		$this->buf->putLInt($this->tickDelay);
		$this->buf->putBool($this->executeOnFirstTick);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleCommandBlockUpdate($this);
	}
}
