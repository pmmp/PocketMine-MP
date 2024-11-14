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

namespace pocketmine\entity;

use pocketmine\entity\utils\ExperienceUtils;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\utils\Limits;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;
use function array_rand;
use function ceil;
use function count;
use function max;
use function min;

class ExperienceManager{

	private Attribute $levelAttr;
	private Attribute $progressAttr;

	private int $totalXp = 0;

	private bool $canAttractXpOrbs = true;

	private int $xpCooldown = 0;

	public function __construct(
		private Human $entity
	){
		$this->levelAttr = self::fetchAttribute($entity, Attribute::EXPERIENCE_LEVEL);
		$this->progressAttr = self::fetchAttribute($entity, Attribute::EXPERIENCE);
	}

	private static function fetchAttribute(Entity $entity, string $attributeId) : Attribute{
		$attribute = AttributeFactory::getInstance()->mustGet($attributeId);
		$entity->getAttributeMap()->add($attribute);
		return $attribute;
	}

	/**
	 * Returns the player's experience level.
	 */
	public function getXpLevel() : int{
		return (int) $this->levelAttr->getValue();
	}

	/**
	 * Sets the player's experience level. This does not affect their total XP or their XP progress.
	 */
	public function setXpLevel(int $level) : bool{
		return $this->setXpAndProgress($level, null);
	}

	/**
	 * Adds a number of XP levels to the player.
	 */
	public function addXpLevels(int $amount, bool $playSound = true) : bool{
		$oldLevel = $this->getXpLevel();
		if($this->setXpLevel($oldLevel + $amount)){
			if($playSound){
				$newLevel = $this->getXpLevel();
				if((int) ($newLevel / 5) > (int) ($oldLevel / 5)){
					$this->entity->broadcastSound(new XpLevelUpSound($newLevel));
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Subtracts a number of XP levels from the player.
	 */
	public function subtractXpLevels(int $amount) : bool{
		return $this->addXpLevels(-$amount);
	}

	/**
	 * Returns a value between 0.0 and 1.0 to indicate how far through the current level the player is.
	 */
	public function getXpProgress() : float{
		return $this->progressAttr->getValue();
	}

	/**
	 * Sets the player's progress through the current level to a value between 0.0 and 1.0.
	 */
	public function setXpProgress(float $progress) : bool{
		return $this->setXpAndProgress(null, $progress);
	}

	/**
	 * Returns the number of XP points the player has progressed into their current level.
	 */
	public function getRemainderXp() : int{
		return (int) (ExperienceUtils::getXpToCompleteLevel($this->getXpLevel()) * $this->getXpProgress());
	}

	/**
	 * Returns the amount of XP points the player currently has, calculated from their current level and progress
	 * through their current level. This will be reduced by enchanting deducting levels and is used to calculate the
	 * amount of XP the player drops on death.
	 */
	public function getCurrentTotalXp() : int{
		return ExperienceUtils::getXpToReachLevel($this->getXpLevel()) + $this->getRemainderXp();
	}

	/**
	 * Sets the current total of XP the player has, recalculating their XP level and progress.
	 * Note that this DOES NOT update the player's lifetime total XP.
	 */
	public function setCurrentTotalXp(int $amount) : bool{
		$newLevel = ExperienceUtils::getLevelFromXp($amount);

		$xpLevel = (int) $newLevel;
		$xpProgress = $newLevel - (int) $newLevel;
		return $this->setXpAndProgress($xpLevel, $xpProgress);
	}

	/**
	 * Adds an amount of XP to the player, recalculating their XP level and progress. XP amount will be added to the
	 * player's lifetime XP.
	 *
	 * @param bool $playSound Whether to play level-up and XP gained sounds.
	 */
	public function addXp(int $amount, bool $playSound = true) : bool{
		$amount = min($amount, Limits::INT32_MAX - $this->totalXp);
		$oldLevel = $this->getXpLevel();
		$oldTotal = $this->getCurrentTotalXp();

		if($this->setCurrentTotalXp($oldTotal + $amount)){
			if($amount > 0){
				$this->totalXp += $amount;
			}
			if($playSound){
				$newLevel = $this->getXpLevel();
				if((int) ($newLevel / 5) > (int) ($oldLevel / 5)){
					$this->entity->broadcastSound(new XpLevelUpSound($newLevel));
				}elseif($this->getCurrentTotalXp() > $oldTotal){
					$this->entity->broadcastSound(new XpCollectSound());
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Takes an amount of XP from the player, recalculating their XP level and progress.
	 */
	public function subtractXp(int $amount) : bool{
		return $this->addXp(-$amount);
	}

	public function setXpAndProgress(?int $level, ?float $progress) : bool{
		$ev = new PlayerExperienceChangeEvent($this->entity, $this->getXpLevel(), $this->getXpProgress(), $level, $progress);
		$ev->call();

		if($ev->isCancelled()){
			return false;
		}

		$level = $ev->getNewLevel();
		$progress = $ev->getNewProgress();

		if($level !== null){
			$this->levelAttr->setValue($level);
		}

		if($progress !== null){
			$this->progressAttr->setValue($progress);
		}

		return true;
	}

	/**
	 * @internal
	 */
	public function setXpAndProgressNoEvent(int $level, float $progress) : void{
		$this->levelAttr->setValue($level);
		$this->progressAttr->setValue($progress);
	}

	/**
	 * Returns the total XP the player has collected in their lifetime. Resets when the player dies.
	 * XP levels being removed in enchanting do not reduce this number.
	 */
	public function getLifetimeTotalXp() : int{
		return $this->totalXp;
	}

	/**
	 * Sets the lifetime total XP of the player. This does not recalculate their level or progress. Used for player
	 * score when they die. (TODO: add this when MCPE supports it)
	 */
	public function setLifetimeTotalXp(int $amount) : void{
		if($amount < 0 || $amount > Limits::INT32_MAX){
			throw new \InvalidArgumentException("XP must be greater than 0 and less than " . Limits::INT32_MAX);
		}

		$this->totalXp = $amount;
	}

	/**
	 * Returns whether the human can pickup XP orbs (checks cooldown time)
	 */
	public function canPickupXp() : bool{
		return $this->xpCooldown === 0;
	}

	public function onPickupXp(int $xpValue) : void{
		$mainHandIndex = -1;
		$offHandIndex = -2;

		//TODO: replace this with a more generic equipment getting/setting interface
		$equipment = [];

		if(($item = $this->entity->getInventory()->getItemInHand()) instanceof Durable && $item->hasEnchantment(VanillaEnchantments::MENDING())){
			$equipment[$mainHandIndex] = $item;
		}
		if(($item = $this->entity->getOffHandInventory()->getItem(0)) instanceof Durable && $item->hasEnchantment(VanillaEnchantments::MENDING())){
			$equipment[$offHandIndex] = $item;
		}
		foreach($this->entity->getArmorInventory()->getContents() as $k => $armorItem){
			if($armorItem instanceof Durable && $armorItem->hasEnchantment(VanillaEnchantments::MENDING())){
				$equipment[$k] = $armorItem;
			}
		}

		if(count($equipment) > 0){
			$repairItem = $equipment[$k = array_rand($equipment)];
			if($repairItem->getDamage() > 0){
				$repairAmount = min($repairItem->getDamage(), $xpValue * 2);
				$repairItem->setDamage($repairItem->getDamage() - $repairAmount);
				$xpValue -= (int) ceil($repairAmount / 2);

				if($k === $mainHandIndex){
					$this->entity->getInventory()->setItemInHand($repairItem);
				}elseif($k === $offHandIndex){
					$this->entity->getOffHandInventory()->setItem(0, $repairItem);
				}else{
					$this->entity->getArmorInventory()->setItem($k, $repairItem);
				}
			}
		}

		$this->addXp($xpValue); //this will still get fired even if the value is 0 due to mending, to play sounds
		$this->resetXpCooldown();
	}

	/**
	 * Sets the duration in ticks until the human can pick up another XP orb.
	 */
	public function resetXpCooldown(int $value = 2) : void{
		$this->xpCooldown = $value;
	}

	public function tick(int $tickDiff = 1) : void{
		if($this->xpCooldown > 0){
			$this->xpCooldown = max(0, $this->xpCooldown - $tickDiff);
		}
	}

	public function canAttractXpOrbs() : bool{
		return $this->canAttractXpOrbs;
	}

	public function setCanAttractXpOrbs(bool $v = true) : void{
		$this->canAttractXpOrbs = $v;
	}
}
