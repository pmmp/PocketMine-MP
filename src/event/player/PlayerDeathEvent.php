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

namespace pocketmine\event\player;

use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\item\Item;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\lang\TranslationContainer;
use pocketmine\player\Player;

class PlayerDeathEvent extends EntityDeathEvent{
	/** @var Player */
	protected $entity;

	/** @var TranslationContainer|string */
	private $deathMessage;
	/** @var bool */
	private $keepInventory = false;

	/**
	 * @param Item[]                           $drops
	 * @param string|TranslationContainer|null $deathMessage Null will cause the default vanilla message to be used
	 */
	public function __construct(Player $entity, array $drops, int $xp, $deathMessage){
		parent::__construct($entity, $drops, $xp);
		$this->deathMessage = $deathMessage ?? self::deriveMessage($entity->getDisplayName(), $entity->getLastDamageCause());
	}

	/**
	 * @return Player
	 */
	public function getEntity(){
		return $this->entity;
	}

	public function getPlayer() : Player{
		return $this->entity;
	}

	/**
	 * @return TranslationContainer|string
	 */
	public function getDeathMessage(){
		return $this->deathMessage;
	}

	/**
	 * @param TranslationContainer|string $deathMessage
	 */
	public function setDeathMessage($deathMessage) : void{
		$this->deathMessage = $deathMessage;
	}

	public function getKeepInventory() : bool{
		return $this->keepInventory;
	}

	public function setKeepInventory(bool $keepInventory) : void{
		$this->keepInventory = $keepInventory;
	}

	/**
	 * Returns the vanilla death message for the given death cause.
	 */
	public static function deriveMessage(string $name, ?EntityDamageEvent $deathCause) : TranslationContainer{
		$message = KnownTranslationKeys::DEATH_ATTACK_GENERIC;
		$params = [$name];

		switch($deathCause === null ? EntityDamageEvent::CAUSE_CUSTOM : $deathCause->getCause()){
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
				if($deathCause instanceof EntityDamageByEntityEvent){
					$e = $deathCause->getDamager();
					if($e instanceof Player){
						$message = KnownTranslationKeys::DEATH_ATTACK_PLAYER;
						$params[] = $e->getDisplayName();
						break;
					}elseif($e instanceof Living){
						$message = KnownTranslationKeys::DEATH_ATTACK_MOB;
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}else{
						$params[] = "Unknown";
					}
				}
				break;
			case EntityDamageEvent::CAUSE_PROJECTILE:
				if($deathCause instanceof EntityDamageByEntityEvent){
					$e = $deathCause->getDamager();
					if($e instanceof Player){
						$message = KnownTranslationKeys::DEATH_ATTACK_ARROW;
						$params[] = $e->getDisplayName();
					}elseif($e instanceof Living){
						$message = KnownTranslationKeys::DEATH_ATTACK_ARROW;
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}else{
						$params[] = "Unknown";
					}
				}
				break;
			case EntityDamageEvent::CAUSE_SUICIDE:
				$message = KnownTranslationKeys::DEATH_ATTACK_GENERIC;
				break;
			case EntityDamageEvent::CAUSE_VOID:
				$message = KnownTranslationKeys::DEATH_ATTACK_OUTOFWORLD;
				break;
			case EntityDamageEvent::CAUSE_FALL:
				if($deathCause instanceof EntityDamageEvent){
					if($deathCause->getFinalDamage() > 2){
						$message = KnownTranslationKeys::DEATH_FELL_ACCIDENT_GENERIC;
						break;
					}
				}
				$message = KnownTranslationKeys::DEATH_ATTACK_FALL;
				break;

			case EntityDamageEvent::CAUSE_SUFFOCATION:
				$message = KnownTranslationKeys::DEATH_ATTACK_INWALL;
				break;

			case EntityDamageEvent::CAUSE_LAVA:
				$message = KnownTranslationKeys::DEATH_ATTACK_LAVA;
				break;

			case EntityDamageEvent::CAUSE_FIRE:
				$message = KnownTranslationKeys::DEATH_ATTACK_ONFIRE;
				break;

			case EntityDamageEvent::CAUSE_FIRE_TICK:
				$message = KnownTranslationKeys::DEATH_ATTACK_INFIRE;
				break;

			case EntityDamageEvent::CAUSE_DROWNING:
				$message = KnownTranslationKeys::DEATH_ATTACK_DROWN;
				break;

			case EntityDamageEvent::CAUSE_CONTACT:
				if($deathCause instanceof EntityDamageByBlockEvent){
					if($deathCause->getDamager()->getId() === BlockLegacyIds::CACTUS){
						$message = KnownTranslationKeys::DEATH_ATTACK_CACTUS;
					}
				}
				break;

			case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
			case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
				if($deathCause instanceof EntityDamageByEntityEvent){
					$e = $deathCause->getDamager();
					if($e instanceof Player){
						$message = KnownTranslationKeys::DEATH_ATTACK_EXPLOSION_PLAYER;
						$params[] = $e->getDisplayName();
					}elseif($e instanceof Living){
						$message = KnownTranslationKeys::DEATH_ATTACK_EXPLOSION_PLAYER;
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}
				}else{
					$message = KnownTranslationKeys::DEATH_ATTACK_EXPLOSION;
				}
				break;

			case EntityDamageEvent::CAUSE_MAGIC:
				$message = KnownTranslationKeys::DEATH_ATTACK_MAGIC;
				break;

			case EntityDamageEvent::CAUSE_CUSTOM:
				break;

			default:
				break;
		}

		return new TranslationContainer($message, $params);
	}
}
