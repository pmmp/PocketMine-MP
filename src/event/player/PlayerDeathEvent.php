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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;

class PlayerDeathEvent extends EntityDeathEvent{
	/** @var Player */
	protected $player;

	private Translatable|string $deathMessage;
	private bool $keepInventory = false;
	private bool $keepXp = false;

	/**
	 * @param Item[]                   $drops
	 * @param string|Translatable|null $deathMessage Null will cause the default vanilla message to be used
	 */
	public function __construct(Player $entity, array $drops, int $xp, Translatable|string|null $deathMessage){
		parent::__construct($entity, $drops, $xp);
		$this->player = $entity;
		$this->deathMessage = $deathMessage ?? self::deriveMessage($entity->getDisplayName(), $entity->getLastDamageCause());
	}

	/**
	 * @return Player
	 */
	public function getEntity(){
		return $this->player;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getDeathMessage() : Translatable|string{
		return $this->deathMessage;
	}

	public function setDeathMessage(Translatable|string $deathMessage) : void{
		$this->deathMessage = $deathMessage;
	}

	public function getKeepInventory() : bool{
		return $this->keepInventory;
	}

	public function setKeepInventory(bool $keepInventory) : void{
		$this->keepInventory = $keepInventory;
	}

	public function getKeepXp() : bool{
		return $this->keepXp;
	}

	public function setKeepXp(bool $keepXp) : void{
		$this->keepXp = $keepXp;
	}

	/**
	 * Returns the vanilla death message for the given death cause.
	 */
	public static function deriveMessage(string $name, ?EntityDamageEvent $deathCause) : Translatable{
		switch($deathCause === null ? EntityDamageEvent::CAUSE_CUSTOM : $deathCause->getCause()){
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
				if($deathCause instanceof EntityDamageByEntityEvent){
					$e = $deathCause->getDamager();
					if($e instanceof Player){
						return KnownTranslationFactory::death_attack_player($name, $e->getDisplayName());
					}elseif($e instanceof Living){
						return KnownTranslationFactory::death_attack_mob($name, $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName());
					}
				}
				break;
			case EntityDamageEvent::CAUSE_PROJECTILE:
				if($deathCause instanceof EntityDamageByEntityEvent){
					$e = $deathCause->getDamager();
					if($e instanceof Player){
						return KnownTranslationFactory::death_attack_arrow($name, $e->getDisplayName());
					}elseif($e instanceof Living){
						return KnownTranslationFactory::death_attack_arrow($name, $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName());
					}
				}
				break;
			case EntityDamageEvent::CAUSE_SUICIDE:
				return KnownTranslationFactory::death_attack_generic($name);
			case EntityDamageEvent::CAUSE_VOID:
				return KnownTranslationFactory::death_attack_outOfWorld($name);
			case EntityDamageEvent::CAUSE_FALL:
				if($deathCause instanceof EntityDamageEvent && $deathCause->getFinalDamage() > 2){
					return KnownTranslationFactory::death_fell_accident_generic($name);
				}
				return KnownTranslationFactory::death_attack_fall($name);

			case EntityDamageEvent::CAUSE_SUFFOCATION:
				return KnownTranslationFactory::death_attack_inWall($name);

			case EntityDamageEvent::CAUSE_LAVA:
				return KnownTranslationFactory::death_attack_lava($name);

			case EntityDamageEvent::CAUSE_FIRE:
				return KnownTranslationFactory::death_attack_onFire($name);

			case EntityDamageEvent::CAUSE_FIRE_TICK:
				return KnownTranslationFactory::death_attack_inFire($name);

			case EntityDamageEvent::CAUSE_DROWNING:
				return KnownTranslationFactory::death_attack_drown($name);

			case EntityDamageEvent::CAUSE_CONTACT:
				if($deathCause instanceof EntityDamageByBlockEvent){
					if($deathCause->getDamager()->getId() === BlockLegacyIds::CACTUS){
						return KnownTranslationFactory::death_attack_cactus($name);
					}
				}
				break;

			case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
			case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
				if($deathCause instanceof EntityDamageByEntityEvent){
					$e = $deathCause->getDamager();
					if($e instanceof Player){
						return KnownTranslationFactory::death_attack_explosion_player($name, $e->getDisplayName());
					}elseif($e instanceof Living){
						return KnownTranslationFactory::death_attack_explosion_player($name, $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName());
					}
				}
				return KnownTranslationFactory::death_attack_explosion($name);

			case EntityDamageEvent::CAUSE_MAGIC:
				return KnownTranslationFactory::death_attack_magic($name);

			case EntityDamageEvent::CAUSE_CUSTOM:
				break;

			default:
				break;
		}

		return KnownTranslationFactory::death_attack_generic($name);
	}
}
