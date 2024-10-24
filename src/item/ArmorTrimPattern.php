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

namespace pocketmine\item;

class ArmorTrimPattern{

	public const COAST = "coast";
	public const DUNE = "dune";
	public const EYE = "eye";
	public const HOST = "host";
	public const RAISER = "raiser";
	public const RIB = "rib";
	public const SENTRY = "sentry";
	public const SHAPER = "shaper";
	public const SILENCE = "silence";
	public const SNOUT = "snout";
	public const SPIRE = "spire";
	public const TIDE = "tide";
	public const VEX = "vex";
	public const WARD = "ward";
	public const WAYFINDER = "wayfinder";
	public const WILD = "wild";

	public function __construct(
		private string $identifier,
		private string $itemName,
		private int $typeId
	){}

	public function getIdentifier() : string{
		return $this->identifier;
	}

	public function getItemName() : string{
		return $this->itemName;
	}

	public function getTypeId() : int{
		return $this->typeId;
	}
}
