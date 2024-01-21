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

namespace pocketmine\data\bedrock;

use pocketmine\item\ArmorTrimPattern;
use pocketmine\utils\SingletonTrait;

final class TrimPatternTypeIdMap{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<ArmorTrimPattern> */
	use IntSaveIdMapTrait;

	private function __construct(){
		$this->register(TrimPatternTypeIds::COAST, ArmorTrimPattern::COAST);
		$this->register(TrimPatternTypeIds::DUNE, ArmorTrimPattern::DUNE);
		$this->register(TrimPatternTypeIds::EYE, ArmorTrimPattern::EYE);
		$this->register(TrimPatternTypeIds::HOST, ArmorTrimPattern::HOST);
		$this->register(TrimPatternTypeIds::RAISER, ArmorTrimPattern::RAISER);
		$this->register(TrimPatternTypeIds::RIB, ArmorTrimPattern::RIB);
		$this->register(TrimPatternTypeIds::SENTRY, ArmorTrimPattern::SENTRY);
		$this->register(TrimPatternTypeIds::SHAPER, ArmorTrimPattern::SHAPER);
		$this->register(TrimPatternTypeIds::SILENCE, ArmorTrimPattern::SILENCE);
		$this->register(TrimPatternTypeIds::SNOUT, ArmorTrimPattern::SNOUT);
		$this->register(TrimPatternTypeIds::SPIRE, ArmorTrimPattern::SPIRE);
		$this->register(TrimPatternTypeIds::TIDE, ArmorTrimPattern::TIDE);
		$this->register(TrimPatternTypeIds::VEX, ArmorTrimPattern::VEX);
		$this->register(TrimPatternTypeIds::WARD, ArmorTrimPattern::WARD);
		$this->register(TrimPatternTypeIds::WAYFINDER, ArmorTrimPattern::WAYFINDER);
		$this->register(TrimPatternTypeIds::WILD, ArmorTrimPattern::WILD);
	}
}
