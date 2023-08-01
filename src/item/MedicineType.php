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

use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static MedicineType ANTIDOTE()
 * @method static MedicineType ELIXIR()
 * @method static MedicineType EYE_DROPS()
 * @method static MedicineType TONIC()
 */
final class MedicineType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self('antidote', 'Antidote', VanillaEffects::POISON()),
			new self('elixir', 'Elixir', VanillaEffects::WEAKNESS()),
			new self('eye_drops', 'Eye Drops', VanillaEffects::BLINDNESS()),
			new self('tonic', 'Tonic', VanillaEffects::NAUSEA())
		);
	}

	private function __construct(
		string $enumName,
		private string $displayName,
		private Effect $curedEffect
	){
		$this->Enum___construct($enumName);
	}

	public function getDisplayName() : string{ return $this->displayName; }

	public function getCuredEffect() : Effect{ return $this->curedEffect; }
}
