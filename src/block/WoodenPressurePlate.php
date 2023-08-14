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

namespace pocketmine\block;

use pocketmine\block\utils\WoodType;
use pocketmine\block\utils\WoodTypeTrait;

class WoodenPressurePlate extends SimplePressurePlate{
	use WoodTypeTrait;

	public function __construct(
		BlockIdentifier $idInfo,
		string $name,
		BlockTypeInfo $typeInfo,
		WoodType $woodType,
		int $deactivationDelayTicks = 20 //TODO: make this mandatory in PM6
	){
		$this->woodType = $woodType;
		parent::__construct($idInfo, $name, $typeInfo, $deactivationDelayTicks);
	}

	public function getFuelTime() : int{
		return 300;
	}
}
