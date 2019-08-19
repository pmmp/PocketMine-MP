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
use pocketmine\item\enchantment\Enchantment;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;
use function array_rand;
use function ceil;
use function max;
use function min;

class ExperienceManager{

	/** @var Human */
	private $entity;

	/** @var Attribute */
	private $levelAttr;
	/** @var Attribute */
	private $progressAttr;

	/** @var int */
	private $totalXp = 0;

	/** @var int */
	private $xpCooldown = 0;

	public function __construct(Human $entity){
		$this->entity = $entity;

		$this->levelAttr = self::fetchAttribute($entity, Attribute::EXPERIENCE_LEVEL);
		$this->progressAttr = self::fetchAttribute($entity, Attribute::EXPERIENCE);
	}

	private static function fetchAttribute(Entity $entity, string $attributeId) : Attribute{
		$entity->getAttributeMap()->add(Attribute::get($attributeId));
		return $entity->getAttributeMap()->get($attributeId);
	}

	/**
	 * Returns the player's experience level.
	 * @return int
	 */
	public function getXpLevel() : int{
		return (int) $this->levelAttr->getValue();
	}

	/**
	 * Sets the player's experience level. This does not affect their total XP or their XP progress.
	 *
	 * @param int $level
	 *
	 * @return bool
	 */
	public function setXpLevel(int $level) : bool{
		return $this->setXpAndProgress($level, null);
	}

	/**
	 * Adds a number of XP levels to the player.
	 *
	 * @param int  $amount
	 * @param bool $playSound
	 *
	 * @return bool
	 */
	public function addXpLevels(int $amount, bool $playSound = true) : bool{
		$oldLevel = $this->getXpLevel();
		if($this->setXpLevel($oldLevel + $amount)){
			if($playSound){
				$newLevel = $this->getXpLevel();
				if((int) ($newLevel / 5) > (int) ($oldLevel / 5)){
					$this->entity->getWorld()->addSound($this->entity->getPosition(), new XpLevelUpSound($newLevel));
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Subtracts a number of XP levels from the player.
	 *
	 * @param int $amount
	 *
	 * @return bool
	 */
	public function subtractXpLevels(int $amount) : bool{
		return $this->addXpLevels(-$amount);
	}

	/**
	 * Returns a value between 0.0 and 1.0 to indicate how far through the current level the player is.
	 * @return float
	 */
	public function getXpProgress() : float{
		return $this->progressAttr->getValue();
	}

	/**
	 * Sets the player's progress through the current level to a value between 0.0 and 1.0.
	 *
	 * @param float $progress
	 *
	 * @return bool
	 */
	public function setXpProgress(float $progress) : bool{
		return $this->setXpAndProgress(null, $progress);
	}

	/**
	 * Returns the number of XP points the player has progressed into their current level.
	 * @return int
	 */
	public function getRemainderXp() : int{
		return (int) (ExperienceUtils::getXpToCompleteLevel($this->getXpLevel()) * $this->getXpProgress());
	}

	/**
	 * Returns the amount of XP points the player currently has, calculated from their current level and progress
	 * through their current level. This will be reduced by enchanting deducting levels and is used to calculate the
	 * amount of XP the player drops on death.
	 *
	 * @return int
	 */
	public function getCurrentTotalXp() : int{
		return ExperienceUtils::getXpToReachLevel($this->getXpLevel()) + $this->getRemainderXp();
	}

	/**
	 * Sets the current total of XP the player has, recalculating their XP level and progress.
	 * Note that this DOES NOT update the player's lifetime total XP.
	 *
	 * @param int $amount
	 *
	 * @return bool
	 */
	public function setCurrentTotalXp(int $amount) : bool{
		$newLevel = ExperienceUtils::getLevelFromXp($amount);

		return $this->setXpAndProgress((int) $newLevel, $newLevel - ((int) $newLevel));
	}

	/**
	 * Adds an amount of XP to the player, recalculating their XP level and progress. XP amount will be added to the
	 * player's lifetime XP.
	 *
	 * @param int  $amount
	 * @param bool $playSound Whether to play level-up and XP gained sounds.
	 *
	 * @return bool
	 */
	public function addXp(int $amount, bool $playSound = true) : bool{
		$this->totalXp += $amount;

		$oldLevel = $this->getXpLevel();
		$oldTotal = $this->getCurrentTotalXp();

		if($this->setCurrentTotalXp($oldTotal + $amount)){
			if($playSound){
				$newLevel = $this->getXpLevel();
				if((int) ($newLevel / 5) > (int) ($oldLevel / 5)){
					$this->entity->getWorld()->addSound($this->entity->getPosition(), new XpLevelUpSound($newLevel));
				}elseif($this->getCurrentTotalXp() > $oldTotal){
					$this->entity->getWorld()->addSound($this->entity->getPosition(), new XpCollectSound());
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Takes an amount of XP from the player, recalculating their XP level and progress.
	 *
	 * @param int $amount
	 *
	 * @return bool
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
	 *
	 * @param int   $level
	 * @param float $progress
	 */
	public function setXpAndProgressNoEvent(int $level, float $progress) : void{
		$this->levelAttr->setValue($level);
		$this->progressAttr->setValue($progress);
	}

	/**
	 * Returns the total XP the player has collected in their lifetime. Resets when the player dies.
	 * XP levels being removed in enchanting do not reduce this number.
	 *
	 * @return int
	 */
	public function getLifetimeTotalXp() : int{
		return $this->totalXp;
	}

	/**
	 * Sets the lifetime total XP of the player. This does not recalculate their level or progress. Used for player
	 * score when they die. (TODO: add this when MCPE supports it)
	 *
	 * @param int $amount
	 */
	public function setLifetimeTotalXp(int $amount) : void{
		if($amount < 0){
			throw new \InvalidArgumentException("XP must be greater than 0");
		}

		$this->totalXp = $amount;
	}


	/**
	 * Returns whether the human can pickup XP orbs (checks cooldown time)
	 * @return bool
	 */
	public function canPickupXp() : bool{
		return $this->xpCooldown === 0;
	}

	public function onPickupXp(int $xpValue) : void{
		static $mainHandIndex = -1;

		//TODO: replace this with a more generic equipment getting/setting interface
		/** @var Durable[] $equipment */
		$equipment = [];

		if(($item = $this->entity->getInventory()->getItemInHand()) instanceof Durable and $item->hasEnchantment(Enchantment::MENDING())){
			$equipment[$mainHandIndex] = $item;
		}
		//TODO: check offhand
		foreach($this->entity->getArmorInventory()->getContents() as $k => $item){
			if($item instanceof Durable and $item->hasEnchantment(Enchantment::MENDING())){
				$equipment[$k] = $item;
			}
		}

		if(!empty($equipment)){
			$repairItem = $equipment[$k = array_rand($equipment)];
			if($repairItem->getDamage() > 0){
				$repairAmount = min($repairItem->getDamage(), $xpValue * 2);
				$repairItem->setDamage($repairItem->getDamage() - $repairAmount);
				$xpValue -= (int) ceil($repairAmount / 2);

				if($k === $mainHandIndex){
					$this->entity->getInventory()->setItemInHand($repairItem);
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
	 *
	 * @param int $value
	 */
	public function resetXpCooldown(int $value = 2) : void{
		$this->xpCooldown = $value;
	}

	public function tick(int $tickDiff = 1) : void{
		if($this->xpCooldown > 0){
			$this->xpCooldown = max(0, $this->xpCooldown - $tickDiff);
		}
	}
}
