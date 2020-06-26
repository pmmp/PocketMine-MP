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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackresponse;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use function count;

final class ItemStackResponse{

	/** @var bool */
	private $ok;
	/** @var int */
	private $requestId;
	/** @var ItemStackResponseContainerInfo[] */
	private $containerInfos;

	/**
	 * @param ItemStackResponseContainerInfo[] $containerInfos
	 */
	public function __construct(bool $ok, int $requestId, array $containerInfos){
		$this->ok = $ok;
		$this->requestId = $requestId;
		$this->containerInfos = $containerInfos;
	}

	public function isOk() : bool{ return $this->ok; }

	public function getRequestId() : int{ return $this->requestId; }

	/** @return ItemStackResponseContainerInfo[] */
	public function getContainerInfos() : array{ return $this->containerInfos; }

	public static function read(PacketSerializer $in) : self{
		$ok = $in->getBool();
		$requestId = $in->readGenericTypeNetworkId();
		$containerInfos = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$containerInfos[] = ItemStackResponseContainerInfo::read($in);
		}
		return new self($ok, $requestId, $containerInfos);
	}

	public function write(PacketSerializer $out) : void{
		$out->putBool($this->ok);
		$out->writeGenericTypeNetworkId($this->requestId);
		$out->putUnsignedVarInt(count($this->containerInfos));
		foreach($this->containerInfos as $containerInfo){
			$containerInfo->write($out);
		}
	}
}
