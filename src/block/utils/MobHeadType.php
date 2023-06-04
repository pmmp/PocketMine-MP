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

namespace pocketmine\block\utils;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static MobHeadType CREEPER()
 * @method static MobHeadType DRAGON()
 * @method static MobHeadType PLAYER()
 * @method static MobHeadType SKELETON()
 * @method static MobHeadType WITHER_SKELETON()
 * @method static MobHeadType ZOMBIE()
 */
final class MobHeadType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new MobHeadType("skeleton", "Skeleton Skull"),
			new MobHeadType("wither_skeleton", "Wither Skeleton Skull"),
			new MobHeadType("zombie", "Zombie Head"),
			new MobHeadType("player", "Player Head"),
			new MobHeadType("creeper", "Creeper Head"),
			new MobHeadType("dragon", "Dragon Head")
		);
	}

	private function __construct(
		string $enumName,
		private string $displayName
	){
		$this->Enum___construct($enumName);
	}

	public function getDisplayName() : string{
		return $this->displayName;
	}
}
