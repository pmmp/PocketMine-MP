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

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static SuspiciousStewType ALLIUM()
 * @method static SuspiciousStewType AZURE_BLUET()
 * @method static SuspiciousStewType BLUE_ORCHID()
 * @method static SuspiciousStewType CORNFLOWER()
 * @method static SuspiciousStewType DANDELION()
 * @method static SuspiciousStewType LILY_OF_THE_VALLEY()
 * @method static SuspiciousStewType OXEYE_DAISY()
 * @method static SuspiciousStewType POPPY()
 * @method static SuspiciousStewType TULIP()
 * @method static SuspiciousStewType WITHER_ROSE()
 */
final class SuspiciousStewType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("poppy", fn() => [
				new EffectInstance(VanillaEffects::NIGHT_VISION(), 80)
			]),
			new self("cornflower", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 80)
			]),
			new self("tulip", fn() => [
				new EffectInstance(VanillaEffects::WEAKNESS(), 140)
			]),
			new self("azure_bluet", fn() => [
				new EffectInstance(VanillaEffects::BLINDNESS(), 120)
			]),
			new self("lily_of_the_valley", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 200)
			]),
			new self("dandelion", fn() => [
				new EffectInstance(VanillaEffects::SATURATION(), 6)
			]),
			new self("blue_orchid", fn() => [
				new EffectInstance(VanillaEffects::SATURATION(), 6)
			]),
			new self("allium", fn() => [
				new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 40)
			]),
			new self("oxeye_daisy", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 120)
			]),
			new self("wither_rose", fn() => [
				new EffectInstance(VanillaEffects::WITHER(), 120)
			])
		);
	}

	/**
	 * @phpstan-param \Closure() : list<EffectInstance> $effectsGetter
	 */
	private function __construct(
		string $enumName,
		private \Closure $effectsGetter
	){
		$this->Enum___construct($enumName);
	}

	/**
	 * @return EffectInstance[]
	 * @phpstan-return list<EffectInstance>
	 */
	public function getEffects() : array{
		return ($this->effectsGetter)();
	}
}
