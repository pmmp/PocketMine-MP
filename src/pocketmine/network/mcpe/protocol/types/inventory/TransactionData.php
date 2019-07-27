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

namespace pocketmine\network\mcpe\protocol\types\inventory;

use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;
use pocketmine\utils\BinaryDataException;
use function count;

abstract class TransactionData{
	/** @var NetworkInventoryAction[] */
	protected $actions = [];

	/**
	 * @return NetworkInventoryAction[]
	 */
	final public function getActions() : array{
		return $this->actions;
	}

	/**
	 * @return int
	 */
	abstract public function getTypeId() : int;

	/**
	 * @param NetworkBinaryStream $stream
	 *
	 * @throws BinaryDataException
	 * @throws BadPacketException
	 */
	final public function decode(NetworkBinaryStream $stream) : void{
		$actionCount = $stream->getUnsignedVarInt();
		for($i = 0; $i < $actionCount; ++$i){
			$this->actions[] = (new NetworkInventoryAction())->read($stream);
		}
		$this->decodeData($stream);
	}

	/**
	 * @param NetworkBinaryStream $stream
	 *
	 * @throws BinaryDataException
	 * @throws BadPacketException
	 */
	abstract protected function decodeData(NetworkBinaryStream $stream) : void;

	final public function encode(NetworkBinaryStream $stream) : void{
		$stream->putUnsignedVarInt(count($this->actions));
		foreach($this->actions as $action){
			$action->write($stream);
		}
		$this->encodeData($stream);
	}

	abstract protected function encodeData(NetworkBinaryStream $stream) : void;
}
