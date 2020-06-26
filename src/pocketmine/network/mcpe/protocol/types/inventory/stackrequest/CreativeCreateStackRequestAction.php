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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\NetworkBinaryStream;

/**
 * Creates an item by copying it from the creative inventory. This is treated as a crafting action by vanilla.
 */
final class CreativeCreateStackRequestAction extends ItemStackRequestAction{

	/** @var int */
	private $creativeItemId;

	public function __construct(int $creativeItemId){
		$this->creativeItemId = $creativeItemId;
	}

	public function getCreativeItemId() : int{ return $this->creativeItemId; }

	public static function getTypeId() : int{ return ItemStackRequestActionType::CREATIVE_CREATE; }

	public static function read(NetworkBinaryStream $in) : self{
		$creativeItemId = $in->readGenericTypeNetworkId();
		return new self($creativeItemId);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->writeGenericTypeNetworkId($this->creativeItemId);
	}
}
