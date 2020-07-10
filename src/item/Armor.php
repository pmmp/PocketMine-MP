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

use pocketmine\block\Block;
use pocketmine\color\Color;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ProtectionEnchantment;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\player\Player;
use pocketmine\utils\Binary;
use function lcg_value;
use function mt_rand;

class Armor extends Durable{

	public const TAG_CUSTOM_COLOR = "customColor"; //TAG_Int

	/** @var ArmorTypeInfo */
	private $armorInfo;

	/** @var Color|null */
	protected $customColor = null;

	public function __construct(ItemIdentifier $identifier, string $name, ArmorTypeInfo $info){
		parent::__construct($identifier, $name);
		$this->armorInfo = $info;
	}

	public function getMaxDurability() : int{
		return $this->armorInfo->getMaxDurability();
	}

	public function getDefensePoints() : int{
		return $this->armorInfo->getDefensePoints();
	}

	/**
	 * @see ArmorInventory
	 */
	public function getArmorSlot() : int{
		return $this->armorInfo->getArmorSlot();
	}

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
	public function setCustomColor(Color $color) : self{
		$this->customColor = $color;
		return $this;
	}

	/**
	 * Returns the total enchantment protection factor this armour piece offers from all applicable protection
	 * enchantments on the item.
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

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		$existing = $player->getArmorInventory()->getItem($this->getArmorSlot());
		if(!$existing->isNull()){
			return ItemUseResult::FAIL();
		}
		$player->getArmorInventory()->setItem($this->getArmorSlot(), $this->pop());
		return ItemUseResult::SUCCESS();
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
		$this->customColor !== null ?
			$tag->setInt(self::TAG_CUSTOM_COLOR, Binary::signInt($this->customColor->toARGB())) :
			$tag->removeTag(self::TAG_CUSTOM_COLOR);
	}
}
