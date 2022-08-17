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

use pocketmine\utils\EnumTrait;
use pocketmine\world\sound\GoatHornAdmireSound;
use pocketmine\world\sound\GoatHornCallSound;
use pocketmine\world\sound\GoatHornDreamSound;
use pocketmine\world\sound\GoatHornFeelSound;
use pocketmine\world\sound\GoatHornPonderSound;
use pocketmine\world\sound\GoatHornSeekSound;
use pocketmine\world\sound\GoatHornSingSound;
use pocketmine\world\sound\GoatHornYearnSound;
use pocketmine\world\sound\Sound;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static GoatHornType ADMIRE()
 * @method static GoatHornType CALL()
 * @method static GoatHornType DREAM()
 * @method static GoatHornType FEEL()
 * @method static GoatHornType PONDER()
 * @method static GoatHornType SEEK()
 * @method static GoatHornType SING()
 * @method static GoatHornType YEARN()
 */
final class GoatHornType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("ponder", fn() => new GoatHornPonderSound()),
			new self("sing", fn() => new GoatHornSingSound()),
			new self("seek", fn() => new GoatHornSeekSound()),
			new self("feel", fn() => new GoatHornFeelSound()),
			new self("admire", fn() => new GoatHornAdmireSound()),
			new self("call", fn() => new GoatHornCallSound()),
			new self("yearn", fn() => new GoatHornYearnSound()),
			new self("dream", fn() => new GoatHornDreamSound())
		);
	}

	/**
	 * @phpstan-param \Closure() : Sound $soundGetter
	 */
	private function __construct(
		string $enumName,
		private \Closure $soundGetter
	){
		$this->Enum___construct($enumName);
	}

	public function getSound() : Sound{
		return ($this->soundGetter)();
	}
}