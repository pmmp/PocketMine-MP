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

use pocketmine\block\utils\PlanksType;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static BoatType ACACIA()
 * @method static BoatType BIRCH()
 * @method static BoatType DARK_OAK()
 * @method static BoatType JUNGLE()
 * @method static BoatType MANGROVE()
 * @method static BoatType OAK()
 * @method static BoatType SPRUCE()
 */
final class BoatType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("oak", PlanksType::OAK()),
			new self("spruce", PlanksType::SPRUCE()),
			new self("birch", PlanksType::BIRCH()),
			new self("jungle", PlanksType::JUNGLE()),
			new self("acacia", PlanksType::ACACIA()),
			new self("dark_oak", PlanksType::DARK_OAK()),
			new self("mangrove", PlanksType::MANGROVE()),
		);
	}

	private function __construct(
		string $enumName,
		private PlanksType $planksType,
	){
		$this->Enum___construct($enumName);
	}

	public function getLogType() : PlanksType{ return $this->planksType; }

	public function getDisplayName() : string{
		return $this->planksType->getDisplayName();
	}
}
