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
use pocketmine\network\mcpe\protocol\types\command\CommandOriginData;
use pocketmine\network\mcpe\protocol\types\command\CommandOutputMessage;
use pocketmine\utils\BinaryDataException;
use function count;

class CommandOutputPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::COMMAND_OUTPUT_PACKET;

	/** @var CommandOriginData */
	public $originData;
	/** @var int */
	public $outputType;
	/** @var int */
	public $successCount;
	/** @var CommandOutputMessage[] */
	public $messages = [];
	/** @var string */
	public $unknownString;

	protected function decodePayload() : void{
		$this->originData = $this->buf->getCommandOriginData();
		$this->outputType = $this->buf->getByte();
		$this->successCount = $this->buf->getUnsignedVarInt();

		for($i = 0, $size = $this->buf->getUnsignedVarInt(); $i < $size; ++$i){
			$this->messages[] = $this->getCommandMessage();
		}

		if($this->outputType === 4){
			$this->unknownString = $this->buf->getString();
		}
	}

	/**
	 * @throws BinaryDataException
	 */
	protected function getCommandMessage() : CommandOutputMessage{
		$message = new CommandOutputMessage();

		$message->isInternal = $this->buf->getBool();
		$message->messageId = $this->buf->getString();

		for($i = 0, $size = $this->buf->getUnsignedVarInt(); $i < $size; ++$i){
			$message->parameters[] = $this->buf->getString();
		}

		return $message;
	}

	protected function encodePayload() : void{
		$this->buf->putCommandOriginData($this->originData);
		$this->buf->putByte($this->outputType);
		$this->buf->putUnsignedVarInt($this->successCount);

		$this->buf->putUnsignedVarInt(count($this->messages));
		foreach($this->messages as $message){
			$this->putCommandMessage($message);
		}

		if($this->outputType === 4){
			$this->buf->putString($this->unknownString);
		}
	}

	protected function putCommandMessage(CommandOutputMessage $message) : void{
		$this->buf->putBool($message->isInternal);
		$this->buf->putString($message->messageId);

		$this->buf->putUnsignedVarInt(count($message->parameters));
		foreach($message->parameters as $parameter){
			$this->buf->putString($parameter);
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleCommandOutput($this);
	}
}
