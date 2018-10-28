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

namespace pocketmine\entity\projectile;

use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerFishingEvent;
use pocketmine\item\FishingRod;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\Player;

class FishingHook extends Projectile{

	public const NETWORK_ID = self::FISHING_HOOK;

	/** @var float */
	public $height = 0.25;
	public $width = 0.25;
	protected $gravity = 0.1;
	protected $drag = 0.05;

	/** @var Entity|null */
	protected $caughtEntity;
	/** @var int */
	protected $ticksCatchable = 0;
	protected $ticksCaughtDelay = 0;
	protected $ticksCatchableDelay = 0;
	/** @var int */
	protected $fishApproachAngle = 0;

	public function attack(EntityDamageEvent $source) : void{
		if($source instanceof EntityDamageByEntityEvent){
			$source->setCancelled();
		}
		parent::attack($source);
	}

	/**
	 * FishingHook constructor.
	 *
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param null|Entity $owner
	 */
	public function __construct(Level $level, CompoundTag $nbt, ?Entity $owner = null){
		parent::__construct($level, $nbt, $owner);
		if($owner instanceof Player){
			$this->setPosition($this->add(0, $owner->getEyeHeight() - 0.1, 0));
			$this->setMotion($owner->getDirectionVector()->multiply(0.4));
			$owner->setFishingHook($this);
			$this->handleHookCasting($this->motion->x, $this->motion->y, $this->motion->z, 1.5, 1.0);
		}
	}

	/**
	 * @param Entity         $entityHit
	 * @param RayTraceResult $hitResult
	 */
	public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$this->mountEntity($entityHit);
	}

	/**
	 * @return bool
	 */
	public function canBePushed() : bool{
		return false;
	}

	/**
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 * @param float $f1
	 * @param float $f2
	 */
	public function handleHookCasting(float $x, float $y, float $z, float $f1, float $f2){
		$f = sqrt($x * $x + $y * $y + $z * $z);
		$x = $x / (float) $f;
		$y = $y / (float) $f;
		$z = $z / (float) $f;
		$x = $x + $this->random->nextSignedFloat() * 0.007499999832361937 * (float) $f2;
		$y = $y + $this->random->nextSignedFloat() * 0.007499999832361937 * (float) $f2;
		$z = $z + $this->random->nextSignedFloat() * 0.007499999832361937 * (float) $f2;
		$x = $x * (float) $f1;
		$y = $y * (float) $f1;
		$z = $z * (float) $f1;
		$this->motion->x += $x;
		$this->motion->y += $y;
		$this->motion->z += $z;
	}

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		$owner = $this->getOwningEntity();

		if($owner instanceof Player){
			if(!($owner->getInventory()->getItemInHand() instanceof FishingRod) or !$owner->isAlive() or $owner->isClosed() or $owner->distanceSquared($this) > 1024){
				$this->flagForDespawn();
			}

			if($this->isUnderwater()){
				$f6 = 0.92;

				if($this->onGround or $this->isCollidedHorizontally){
					$f6 = 0.5;
				}

				$j = 5;
				$d10 = 0;

				for($k = 0; $k < $j; $k++){
					$b = $this->level->getBlock($this->add(0, $k, 0));
					if($b instanceof Water){
						$d10 += 1.0 / $j;
					}else{
						break;
					}
				}

				if($this->isValid() and $d10 > 0){
					$l = 1;

					// TODO: lightninstrike

					if($this->ticksCatchable > 0){
						--$this->ticksCatchable;

						if($this->ticksCatchable <= 0){
							$this->ticksCaughtDelay = 0;
							$this->ticksCatchableDelay = 0;
						}
					}elseif($this->ticksCatchableDelay > 0){
						$this->ticksCatchableDelay -= $l;

						if($this->ticksCatchableDelay <= 0){
							$this->broadcastEntityEvent(EntityEventPacket::FISH_HOOK_HOOK);
							$this->motion->y -= 0.2;
							$this->ticksCatchable = mt_rand(10, 30);
						}else{
							$this->fishApproachAngle = (float) ((int) $this->fishApproachAngle + $this->random->nextSignedFloat() * 4.0);
							$f7 = $this->fishApproachAngle * 0.017453292;
							$f10 = sin($f7);
							$f11 = cos($f7);
							$d13 = $this->x + (int) ($f10 * (float) $this->ticksCatchableDelay * 0.1);
							$d15 = (int) $this->y + 1;
							$d16 = $this->z + (int) ($f11 * (float) $this->ticksCatchableDelay * 0.1);
							$block1 = $this->level->getBlock(new Vector3($d13, $d15 - 1, $d16));

							if($block1 instanceof Water){
								if($this->random->nextFloat() < 0.15){
									$this->level->addParticle(new GenericParticle(new Vector3($d13, $d15 - 0.1, $d16), Particle::TYPE_BUBBLE));
								}

								$this->level->addParticle(new GenericParticle(new Vector3($d13, $d15, $d16), Particle::TYPE_WATER_WAKE));
							}
						}
					}elseif($this->ticksCaughtDelay > 0){
						$this->ticksCaughtDelay -= $l;
						$f1 = 0.15;

						if($this->ticksCaughtDelay < 20){
							$f1 = (float) ((float) $f1 + (int) (20 - $this->ticksCaughtDelay) * 0.05);
						}elseif($this->ticksCaughtDelay < 40){
							$f1 = (float) ((float) $f1 + (int) (40 - $this->ticksCaughtDelay) * 0.02);
						}elseif($this->ticksCaughtDelay < 60){
							$f1 = (float) ((float) $f1 + (int) (60 - $this->ticksCaughtDelay) * 0.01);
						}

						if($this->random->nextFloat() < $f1){
							$f9 = mt_rand(0, 360) * 0.017453292;
							$f2 = mt_rand(25, 60);
							$d12 = $this->x + (int) (sin($f9) * $f2 * 0.1);
							$d14 = (int) ((float) floor($this->y) + 1.0);
							$d6 = $this->z + (int) (cos($f9) * $f2 * 0.1);
							$block = $this->level->getBlock(new Vector3($d12, $d14 - 1, $d6));

							if($block instanceof Water){
								$this->level->addParticle(new GenericParticle(new Vector3($d12, $d14, $d6), Particle::TYPE_SPLASH));
							}
						}

						if($this->ticksCaughtDelay <= 0){
							$this->ticksCatchableDelay = mt_rand(20, 80);
							$this->fishApproachAngle = mt_rand(0, 360);
						}
					}else{
						$this->ticksCaughtDelay = mt_rand(100, 900);
						$this->ticksCaughtDelay -= 20 * 5; // TODO: Lure
					}

					if($this->ticksCatchable > 0){
						$this->motion->y -= (int) ($this->random->nextFloat() * $this->random->nextFloat() * $this->random->nextFloat()) * 0.2;
					}

					$d11 = $d10 * 2.0 - 1.0;
					$this->motion->y += 0.03999999910593033 * $d11;

					if($d10 > 0.0){
						$f6 = (float) ((int) $f6 * 0.9);
						$this->motion->y *= 0.8;
					}

					$this->motion->x *= (int) $f6;
					$this->motion->y *= (int) $f6;
					$this->motion->z *= (int) $f6;
				}
			}
		}else{
			$this->flagForDespawn();
		}

		return $hasUpdate;
	}

	public function close() : void{
		parent::close();

		$owner = $this->getOwningEntity();
		if($owner instanceof Player){
			$owner->setFishingHook(null);
		}
		$this->dismountEntity();
	}

	public function handleHookRetraction() : void{
		$angler = $this->getOwningEntity();
		if($this->isValid() and $angler instanceof Player){
			if($this->ridingEntity != null){
				$d0 = $angler->x - $this->x;
				$d2 = $angler->y - $this->y;
				$d4 = $angler->z - $this->z;
				$d6 = (int) sqrt($d0 * $d0 + $d2 * $d2 + $d4 * $d4);
				$d8 = 0.1;
				$this->ridingEntity->setMotion(new Vector3($d0 * $d8, $d2 * $d8 + (int) sqrt($d6) * 0.08, $d4 * $d8));
			}elseif($this->ticksCatchable > 0){
				// TODO: Random weighted items
				$items = [
					Item::RAW_FISH,
					Item::PUFFERFISH,
					Item::RAW_SALMON,
					Item::CLOWNFISH
				];
				$randomFish = $items[mt_rand(0, count($items) - 1)];

				$ev = new PlayerFishingEvent($angler, $this, ItemFactory::get($randomFish), $this->random->nextBoundedInt(6) + 1);
				$ev->call();

				if(!$ev->isCancelled()){
					$nbt = Entity::createBaseNBT($this);
					$nbt->setTag($ev->getResultItem()->nbtSerialize(-1, "Item"));
					$entityitem = new ItemEntity($this->level, $nbt);
					$d0 = $angler->x - $this->x;
					$d2 = $angler->y - $this->y;
					$d4 = $angler->z - $this->z;
					$d6 = (int) sqrt($d0 * $d0 + $d2 * $d2 + $d4 * $d4);
					$d8 = 0.1;
					$entityitem->setMotion(new Vector3($d0 * $d8, $d2 * $d8 + (int) sqrt($d6) * 0.08, $d4 * $d8));
					$entityitem->spawnToAll();
					$this->level->dropExperience($angler, $ev->getXpDropAmount());
				}
			}

			$this->flagForDespawn();
		}
	}

	public function applyGravity() : void{
		if(!$this->isUnderwater()){
			parent::applyGravity();
		}else{
			$this->motion->y += $this->gravity;
		}
	}
}