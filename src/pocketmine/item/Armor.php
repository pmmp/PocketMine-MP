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

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ProtectionEnchantment;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Binary;
use pocketmine\utils\Color;
use function lcg_value;
use function mt_rand;

abstract class Armor extends Durable{

	public const TAG_CUSTOM_COLOR = "customColor"; //TAG_Int

	public function getMaxStackSize() : int{
		return 1;
	}

	/**
	 * Returns the dyed colour of this armour piece. This generally only applies to leather armour.
	 * @return Color|null
	 */
	public function getCustomColor() : ?Color{
		if($this->getNamedTag()->hasTag(self::TAG_CUSTOM_COLOR, IntTag::class)){
			return Color::fromARGB(Binary::unsignInt($this->getNamedTag()->getInt(self::TAG_CUSTOM_COLOR)));
		}

		return null;
	}

	/**
	 * Sets the dyed colour of this armour piece. This generally only applies to leather armour.
	 *
	 * @param Color $color
	 */
	public function setCustomColor(Color $color) : void{
		$this->setNamedTagEntry(new IntTag(self::TAG_CUSTOM_COLOR, Binary::signInt($color->toARGB())));
	}

	/**
	 * Returns the total enchantment protection factor this armour piece offers from all applicable protection
	 * enchantments on the item.
	 *
	 * @param EntityDamageEvent $event
	 *
	 * @return int
	 */
	public function getEnchantmentProtectionFactor(EntityDamageEvent $event) : int{
		$epf = 0;

		foreach($this->getEnchantments() as $enchantment){
			$type = $enchantment->getType();
			if($type instanceof ProtectionEnchantment and $type->isApplicable($event)){
				$epf += $type->getProtectionFactor($enchantment->getLevel());
			}
		}

		return $epf;
	}

	protected function getUnbreakingDamageReduction(int $amount) : int{
		if(($unbreakingLevel = $this->getEnchantmentLevel(Enchantment::UNBREAKING())) > 0){
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for($i = 0; $i < $amount; ++$i){
				if(mt_rand(1, 100) > 60 and lcg_value() > $chance){ //unbreaking only applies to armor 40% of the time at best
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}
}
