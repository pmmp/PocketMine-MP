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
 * Completes a transaction involving a beacon consuming input to produce effects.
 */
final class BeaconPaymentStackRequestAction extends ItemStackRequestAction{

	/** @var int */
	private $primaryEffectId;
	/** @var int */
	private $secondaryEffectId;

	public function __construct(int $primaryEffectId, int $secondaryEffectId){
		$this->primaryEffectId = $primaryEffectId;
		$this->secondaryEffectId = $secondaryEffectId;
	}

	public function getPrimaryEffectId() : int{ return $this->primaryEffectId; }

	public function getSecondaryEffectId() : int{ return $this->secondaryEffectId; }

	public static function getTypeId() : int{ return ItemStackRequestActionType::BEACON_PAYMENT; }

	public static function read(NetworkBinaryStream $in) : self{
		$primary = $in->getVarInt();
		$secondary = $in->getVarInt();
		return new self($primary, $secondary);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->primaryEffectId);
		$out->putVarInt($this->secondaryEffectId);
	}
}
