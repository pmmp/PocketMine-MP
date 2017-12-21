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

namespace pocketmine\item\enchantment;

use pocketmine\event\entity\EntityDamageEvent;

class ProtectionEnchantment extends Enchantment{
	/** @var float */
	protected $typeModifier;
	/** @var int[]|null */
	protected $applicableDamageTypes = null;

	public function __construct(int $id, string $name, int $rarity, int $slot, int $maxLevel, float $typeModifier, ?array $applicableDamageTypes){
		parent::__construct($id, $name, $rarity, $slot, $maxLevel);

		$this->typeModifier = $typeModifier;
		if($applicableDamageTypes !== null){
			$this->applicableDamageTypes = array_flip($applicableDamageTypes);
		}
	}

	public function getTypeModifier() : float{
		return $this->typeModifier;
	}

	public function getProtectionFactor(int $level) : int{
		return (int) floor((6 + $level ** 2) * $this->typeModifier / 3);
	}

	public function isApplicable(EntityDamageEvent $event) : bool{
		return $this->applicableDamageTypes === null or isset($this->applicableDamageTypes[$event->getCause()]);
	}
}
