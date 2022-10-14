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
 * @method static WoodType ACACIA()
 * @method static WoodType BIRCH()
 * @method static WoodType CRIMSON()
 * @method static WoodType DARK_OAK()
 * @method static WoodType JUNGLE()
 * @method static WoodType MANGROVE()
 * @method static WoodType OAK()
 * @method static WoodType SPRUCE()
 * @method static WoodType WARPED()
 */
final class WoodType{
	use EnumTrait {
		__construct as private Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("oak", "Oak", true),
			new self("spruce", "Spruce", true),
			new self("birch", "Birch", true),
			new self("jungle", "Jungle", true),
			new self("acacia", "Acacia", true),
			new self("dark_oak", "Dark Oak", true),
			new self("mangrove", "Mangrove", true),
			new self("crimson", "Crimson", false, "Stem", "Hyphae"),
			new self("warped", "Warped", false, "Stem", "Hyphae")
		);
	}

	private function __construct(
		string $enumName,
		private string $displayName,
		private bool $flammable,
		private ?string $standardLogSuffix = null,
		private ?string $allSidedLogSuffix = null,
	){
		$this->Enum___construct($enumName);
	}

	public function getDisplayName() : string{ return $this->displayName; }

	public function isFlammable() : bool{ return $this->flammable; }

	public function getStandardLogSuffix() : ?string{ return $this->standardLogSuffix; }

	public function getAllSidedLogSuffix() : ?string{ return $this->allSidedLogSuffix; }
}
