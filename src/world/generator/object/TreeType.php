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

namespace pocketmine\world\generator\object;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static TreeType ACACIA()
 * @method static TreeType BIRCH()
 * @method static TreeType DARK_OAK()
 * @method static TreeType JUNGLE()
 * @method static TreeType OAK()
 * @method static TreeType SPRUCE()
 */
final class TreeType{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new TreeType("oak", "Oak"),
			new TreeType("spruce", "Spruce"),
			new TreeType("birch", "Birch"),
			new TreeType("jungle", "Jungle"),
			new TreeType("acacia", "Acacia"),
			new TreeType("dark_oak", "Dark Oak"),
			//TODO: cherry blossom, mangrove, azalea
			//TODO: do crimson and warped "trees" belong here? I'm not sure if they're actually trees or just fungi
			//TODO: perhaps huge mushrooms should be here too???
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
