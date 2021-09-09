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

use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;
use function spl_object_id;

final class EffectIdMap{
	use SingletonTrait;

	/**
	 * @var Effect[]
	 * @phpstan-var array<int, Effect>
	 */
	private $idToEffect = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private $effectToId = [];

	private function __construct(){
		$this->register(EffectIds::SPEED, VanillaEffects::SPEED());
		$this->register(EffectIds::SLOWNESS, VanillaEffects::SLOWNESS());
		$this->register(EffectIds::HASTE, VanillaEffects::HASTE());
		$this->register(EffectIds::MINING_FATIGUE, VanillaEffects::MINING_FATIGUE());
		$this->register(EffectIds::STRENGTH, VanillaEffects::STRENGTH());
		$this->register(EffectIds::INSTANT_HEALTH, VanillaEffects::INSTANT_HEALTH());
		$this->register(EffectIds::INSTANT_DAMAGE, VanillaEffects::INSTANT_DAMAGE());
		$this->register(EffectIds::JUMP_BOOST, VanillaEffects::JUMP_BOOST());
		$this->register(EffectIds::NAUSEA, VanillaEffects::NAUSEA());
		$this->register(EffectIds::REGENERATION, VanillaEffects::REGENERATION());
		$this->register(EffectIds::RESISTANCE, VanillaEffects::RESISTANCE());
		$this->register(EffectIds::FIRE_RESISTANCE, VanillaEffects::FIRE_RESISTANCE());
		$this->register(EffectIds::WATER_BREATHING, VanillaEffects::WATER_BREATHING());
		$this->register(EffectIds::INVISIBILITY, VanillaEffects::INVISIBILITY());
		$this->register(EffectIds::BLINDNESS, VanillaEffects::BLINDNESS());
		$this->register(EffectIds::NIGHT_VISION, VanillaEffects::NIGHT_VISION());
		$this->register(EffectIds::HUNGER, VanillaEffects::HUNGER());
		$this->register(EffectIds::WEAKNESS, VanillaEffects::WEAKNESS());
		$this->register(EffectIds::POISON, VanillaEffects::POISON());
		$this->register(EffectIds::WITHER, VanillaEffects::WITHER());
		$this->register(EffectIds::HEALTH_BOOST, VanillaEffects::HEALTH_BOOST());
		$this->register(EffectIds::ABSORPTION, VanillaEffects::ABSORPTION());
		$this->register(EffectIds::SATURATION, VanillaEffects::SATURATION());
		$this->register(EffectIds::LEVITATION, VanillaEffects::LEVITATION());
		$this->register(EffectIds::FATAL_POISON, VanillaEffects::FATAL_POISON());
		$this->register(EffectIds::CONDUIT_POWER, VanillaEffects::CONDUIT_POWER());
		//TODO: SLOW_FALLING
		//TODO: BAD_OMEN
		//TODO: VILLAGE_HERO
	}

	//TODO: not a big fan of the code duplication here :(

	public function register(int $mcpeId, Effect $effect) : void{
		$this->idToEffect[$mcpeId] = $effect;
		$this->effectToId[spl_object_id($effect)] = $mcpeId;
	}

	public function fromId(int $id) : ?Effect{
		//we might not have all the effect IDs registered
		return $this->idToEffect[$id] ?? null;
	}

	public function toId(Effect $effect) : int{
		if(!array_key_exists(spl_object_id($effect), $this->effectToId)){
			//this should never happen, so we treat it as an exceptional condition
			throw new \InvalidArgumentException("Effect does not have a mapped ID");
		}
		return $this->effectToId[spl_object_id($effect)];
	}
}
