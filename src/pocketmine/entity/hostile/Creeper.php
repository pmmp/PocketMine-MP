<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\hostile;

use pocketmine\block\Block;
use pocketmine\entity\Ageable;
use pocketmine\entity\behavior\AvoidMobTypeBehavior;
use pocketmine\entity\behavior\CreeperSwellBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\HurtByTargetBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MeleeAttackBehavior;
use pocketmine\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\entity\Monster;
use pocketmine\entity\passive\Cat;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Explosion;
use pocketmine\level\GameRules;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\tile\Skull;
use function boolval;
use function intval;
use function rand;

class Creeper extends Monster implements Ageable{

	public const NETWORK_ID = self::CREEPER;

	public $width = 0.6;
	public $height = 1.7;

	protected $lastActiveTime = 0;
	protected $timeSinceIgnited = 0;
	protected $fuseTime = 30;
	protected $explosionRadius = 3;

	protected function initEntity() : void{
		$this->setMaxHealth(20);
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(35);
		$this->setAttackDamage(1.0);

		$this->setIgnited(boolval($this->namedtag->getByte("ignited", 0)));
		$this->setPowered(boolval($this->namedtag->getByte("powered", 0)));

		$this->explosionRadius = $this->namedtag->getByte("ExplosionRadius", $this->explosionRadius);
		$this->fuseTime = $this->namedtag->getShort("Fuse", $this->fuseTime);

		parent::initEntity();
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setByte("ignited", intval($this->isIgnited()));
		$this->namedtag->setByte("powered", intval($this->isPowered()));
		$this->namedtag->setShort("Fuse", $this->fuseTime);
		$this->namedtag->setByte("ExplosionRadius", $this->explosionRadius);
	}

	public function getName() : string{
		return "Creeper";
	}

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new CreeperSwellBehavior($this));
		// TODO: Avoid from ocelot
		$this->behaviorPool->setBehavior(2, new AvoidMobTypeBehavior($this, Cat::class, null, 6, 1, 1.2));
		$this->behaviorPool->setBehavior(3, new MeleeAttackBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(4, new RandomStrollBehavior($this, 0.8));
		$this->behaviorPool->setBehavior(5, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(6, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new NearestAttackableTargetBehavior($this, Player::class, true));
		$this->targetBehaviorPool->setBehavior(1, new HurtByTargetBehavior($this));
	}

	public function getXpDropAmount() : int{
		return $this->getRevengeTarget() instanceof Player ? 5 : 0;
	}

	public function getDrops() : array{
		$drops = [];
		if($this->timeSinceIgnited !== $this->fuseTime){
			$drops[] = ItemFactory::get(Item::GUNPOWDER, 0, rand(0, 2));
		}
		$attacker = $this->getRevengeTarget();
		if($attacker instanceof Skeleton){
			$drops[] = ItemFactory::get(rand(Item::RECORD_13, Item::RECORD_WAIT));
		}elseif($attacker instanceof Creeper and $attacker->isPowered()){
			$drops[] = ItemFactory::get(Block::SKULL_BLOCK, Skull::TYPE_CREEPER);
		}
		return $drops;
	}

	public function isIgnited() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_IGNITED);
	}

	public function isPowered() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_POWERED);
	}

	public function setPowered(bool $value) : void{
		$this->setGenericFlag(self::DATA_FLAG_POWERED, $value);
	}

	public function setIgnited(bool $value) : void{
		$this->setGenericFlag(self::DATA_FLAG_IGNITED, $value);
	}

	public function explode() : void{
		if($this->isValid()){
			$f = $this->isPowered() ? 2 : 1;
			$exp = new Explosion($this, $this->explosionRadius * $f, $this);
			$this->flagForDespawn();

			if($this->level->getGameRules()->getBool(GameRules::RULE_MOB_GRIEFING, true)){
				$exp->explodeA();
			}
			$exp->explodeB();
		}
	}

	public function entityBaseTick(int $diff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($diff);

		$this->lastActiveTime = $this->timeSinceIgnited;

		if($this->isIgnited()){
			if($this->timeSinceIgnited === 0){
				$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_IGNITE);
			}

			if($this->timeSinceIgnited % 5 == 0){
				$this->propertyManager->setInt(self::DATA_FUSE_LENGTH, $this->fuseTime - $this->timeSinceIgnited);
			}

			if($this->timeSinceIgnited++ >= $this->fuseTime){
				$this->timeSinceIgnited = $this->fuseTime;
				$this->explode();
			}
		}else{
			$this->timeSinceIgnited = 0;
		}

		return $hasUpdate;
	}

	public function fall(float $fallDistance) : void{
		parent::fall($fallDistance);

		$this->timeSinceIgnited += intval($fallDistance * 1.5);

		if($this->timeSinceIgnited >= $this->fuseTime - 5){
			$this->timeSinceIgnited = $this->fuseTime - 5;
		}
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		if($item instanceof FlintSteel){
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_IGNITE);

			if($this->isValid()){
				$this->setIgnited(true);
				$item->applyDamage(1);

				return true;
			}
		}

		return parent::onInteract($player, $item, $clickPos);
	}
}