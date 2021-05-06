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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class MineBlockStackRequestAction extends ItemStackRequestAction{

	/** @var int */
	private $unknown1;
	/** @var int */
	private $predictedDurability;
	/** @var int */
	private $stackId;

	public function __construct(int $unknown1, int $predictedDurability, int $stackId){
		$this->unknown1 = $unknown1;
		$this->predictedDurability = $predictedDurability;
		$this->stackId = $stackId;
	}

	public function getUnknown1() : int{ return $this->unknown1; }

	public function getPredictedDurability() : int{ return $this->predictedDurability; }

	public function getStackId() : int{ return $this->stackId; }

	public static function getTypeId() : int{ return ItemStackRequestActionType::MINE_BLOCK; }

	public static function read(PacketSerializer $in) : self{
		$unknown1 = $in->getVarInt();
		$predictedDurability = $in->getVarInt();
		$stackId = $in->readGenericTypeNetworkId();
		return new self($unknown1, $predictedDurability, $stackId);
	}

	public function write(PacketSerializer $out) : void{
		$out->putVarInt($this->unknown1);
		$out->putVarInt($this->predictedDurability);
		$out->writeGenericTypeNetworkId($this->stackId);
	}
}
