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

class EnchantedBook extends Item{
	public function getMaxStackSize() : int{
		return 1;
	}

	/**
	 * Return lore for enchanted book: list of enchantments and levels when no custom lore is set.
	 * This provides a visible hint in UIs (non-localized fallback).
	 *
	 * @return string[]
	 */
	public function getLore() : array{
		$existing = parent::getLore();
		if(count($existing) > 0){
			return $existing;
		}

		if(!$this->hasEnchantments()){
			return [];
		}

		$lines = [];
		foreach($this->getEnchantments() as $enchantment){
			$type = $enchantment->getType();
			$name = $type->getName();
			if(is_string($name)){
				$base = $name;
			}else{
				// Translatable fallback: use translation key as fallback text
				$base = $name->getText();
			}
			$level = $enchantment->getLevel();
			$lines[] = $base . " " . $this->toRoman($level);
		}

		return $lines;
	}

	private function toRoman(int $num) : string{
		$map = [1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'];
		$res = "";
		foreach($map as $val => $rom){
			while($num >= $val){
				$res .= $rom;
				$num -= $val;
			}
		}
		return $res === '' ? 'I' : $res;
	}
}
