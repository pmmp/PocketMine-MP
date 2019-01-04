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

use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\protocol\types\CommandOriginData;
use pocketmine\network\mcpe\protocol\types\CommandOutputMessage;
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
		$this->originData = $this->getCommandOriginData();
		$this->outputType = $this->getByte();
		$this->successCount = $this->getUnsignedVarInt();

		for($i = 0, $size = $this->getUnsignedVarInt(); $i < $size; ++$i){
			$this->messages[] = $this->getCommandMessage();
		}

		if($this->outputType === 4){
			$this->unknownString = $this->getString();
		}
	}

	/**
	 * @return CommandOutputMessage
	 * @throws BinaryDataException
	 */
	protected function getCommandMessage() : CommandOutputMessage{
		$message = new CommandOutputMessage();

		$message->isInternal = $this->getBool();
		$message->messageId = $this->getString();

		for($i = 0, $size = $this->getUnsignedVarInt(); $i < $size; ++$i){
			$message->parameters[] = $this->getString();
		}

		return $message;
	}

	protected function encodePayload() : void{
		$this->putCommandOriginData($this->originData);
		$this->putByte($this->outputType);
		$this->putUnsignedVarInt($this->successCount);

		$this->putUnsignedVarInt(count($this->messages));
		foreach($this->messages as $message){
			$this->putCommandMessage($message);
		}

		if($this->outputType === 4){
			$this->putString($this->unknownString);
		}
	}

	protected function putCommandMessage(CommandOutputMessage $message) : void{
		$this->putBool($message->isInternal);
		$this->putString($message->messageId);

		$this->putUnsignedVarInt(count($message->parameters));
		foreach($message->parameters as $parameter){
			$this->putString($parameter);
		}
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleCommandOutput($this);
	}
}
