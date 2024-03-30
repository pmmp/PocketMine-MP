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

use pocketmine\color\Color;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Binary;

class HorseArmor extends Item{

	public const TAG_CUSTOM_COLOR = "customColor"; //TAG_Int

	protected ?Color $customColor = null;

	public function getMaxStackSize() : int{
		return 1;
	}

	/**
	 * Returns the dyed colour of this armour piece. This generally only applies to leather armour.
	 */
	public function getCustomColor() : ?Color{
		return $this->customColor;
	}

	/**
	 * Sets the dyed colour of this armour piece. This generally only applies to leather armour.
	 *
	 * @return $this
	 */
	public function setCustomColor(?Color $color) : self{
		if($this->getTypeId() === ItemTypeIds::LEATHER_HORSE_ARMOR){
			$this->customColor = $color;
		}
		return $this;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);
		if(($colorTag = $tag->getTag(self::TAG_CUSTOM_COLOR)) instanceof IntTag){
			$this->customColor = Color::fromARGB(Binary::unsignInt($colorTag->getValue()));
		}else{
			$this->customColor = null;
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);
		if($this->customColor !== null){
			$tag->setInt(self::TAG_CUSTOM_COLOR, Binary::signInt($this->customColor->toARGB()));
		}else{
			$tag->removeTag(self::TAG_CUSTOM_COLOR);
		}
	}
}
