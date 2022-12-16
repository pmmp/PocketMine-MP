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
use pocketmine\world\sound\FireworkBigExplosionSound;
use pocketmine\world\sound\FireworkExplosionSound;
use pocketmine\world\sound\Sound;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static FireworkRocketType BIG_SPHERE()
 * @method static FireworkRocketType BURST()
 * @method static FireworkRocketType CREEPER()
 * @method static FireworkRocketType SMALL_SPHERE()
 * @method static FireworkRocketType STAR()
 */
final class FireworkRocketType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("small_sphere", fn() => new FireworkExplosionSound()),
			new self("big_sphere", fn() => new FireworkBigExplosionSound()),
			new self("star", fn() => new FireworkExplosionSound()),
			new self("creeper", fn() => new FireworkExplosionSound()),
			new self("burst", fn() => new FireworkExplosionSound()),
		);
	}

	/**
	 * @phpstan-param \Closure() : Sound
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
