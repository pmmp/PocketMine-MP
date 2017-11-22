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

use pocketmine\entity\Effect;

class Fish extends Food{
	public const FISH_FISH = 0;
	public const FISH_SALMON = 1;
	public const FISH_CLOWNFISH = 2;
	public const FISH_PUFFERFISH = 3;

	public function __construct(int $meta = 0){
		$name = "Raw Fish";
		if($this->meta === self::FISH_SALMON){
			$name = "Raw Salmon";
		}elseif($this->meta === self::FISH_CLOWNFISH){
			$name = "Clownfish";
		}elseif($this->meta === self::FISH_PUFFERFISH){
			$name = "Pufferfish";
		}
		parent::__construct(self::RAW_FISH, $meta, $name);
	}

	public function getFoodRestore() : int{
		if($this->meta === self::FISH_FISH){
			return 2;
		}elseif($this->meta === self::FISH_SALMON){
			return 2;
		}elseif($this->meta === self::FISH_CLOWNFISH){
			return 1;
		}elseif($this->meta === self::FISH_PUFFERFISH){
			return 1;
		}
		return 0;
	}

	public function getSaturationRestore() : float{
		if($this->meta === self::FISH_FISH){
			return 0.4;
		}elseif($this->meta === self::FISH_SALMON){
			return 0.4;
		}elseif($this->meta === self::FISH_CLOWNFISH){
			return 0.2;
		}elseif($this->meta === self::FISH_PUFFERFISH){
			return 0.2;
		}
		return 0;
	}

	public function getAdditionalEffects() : array{
		return $this->meta === self::FISH_PUFFERFISH ? [
			Effect::getEffect(Effect::HUNGER)->setDuration(300)->setAmplifier(2),
			Effect::getEffect(Effect::NAUSEA)->setDuration(300)->setAmplifier(1),
			Effect::getEffect(Effect::POISON)->setDuration(1200)->setAmplifier(3),
		] : [];
	}
}
