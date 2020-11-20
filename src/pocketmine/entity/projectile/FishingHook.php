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
use pocketmine\event\player\PlayerFishEvent;
use pocketmine\item\FishingRod;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;
use function cos;
use function floor;
use function mt_rand;
use function sin;
use function sqrt;

class FishingHook extends Projectile{

	public const NETWORK_ID = self::FISHING_HOOK;

	/** @var float */
	public $height = 0.15;
	public $width = 0.15;
	protected $gravity = 0.1;
	protected $drag = 0.05;

	/** @var Entity|null */
	protected $caughtEntity;
	/** @var int */
	protected $ticksCatchable = 0;
	protected $ticksCaughtDelay = 0;
	protected $ticksCatchableDelay = 0;
	/** @var float */
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
			$owner->setFishingHook($this);

			$this->handleHookCasting($this->motion->x, $this->motion->y, $this->motion->z, 1.5, 1.0);
		}
	}

	/**
	 * @param Entity         $entityHit
	 * @param RayTraceResult $hitResult
	 */
	public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$entityHit->attack(new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 0));

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
		$x = $x / $f;
		$y = $y / $f;
		$z = $z / $f;
		$x = $x + $this->random->nextSignedFloat() * 0.0075 * $f2;
		$y = $y + $this->random->nextSignedFloat() * 0.0075 * $f2;
		$z = $z + $this->random->nextSignedFloat() * 0.0075 * $f2;
		$x = $x * $f1;
		$y = $y * $f1;
		$z = $z * $f1;
		$this->motion->x += $x;
		$this->motion->y += $y;
		$this->motion->z += $z;
	}

	public function onUpdate(int $currentTick) : bool{
		if($this->closed) return false;

		$owner = $this->getOwningEntity();

		$inGround = $this->level->getBlock($this)->isSolid();

		if($inGround){
			$this->motion->x *= $this->random->nextFloat() * 0.2;
			$this->motion->y *= $this->random->nextFloat() * 0.2;
			$this->motion->z *= $this->random->nextFloat() * 0.2;
		}

		$hasUpdate = parent::onUpdate($currentTick);

		if($owner instanceof Player){
			if($owner->isClosed() or !$owner->isAlive() or !($owner->getInventory()->getItemInHand() instanceof FishingRod) or $owner->distanceSquared($this) > 1024){
				$this->flagForDespawn();
			}

			if(!$inGround){
				$hasUpdate = true;

				$f6 = 0.92;

				if($this->onGround or $this->isCollidedHorizontally){
					$f6 = 0.5;
				}

				$d10 = 0;

				$bb = $this->getBoundingBox();

				for($j = 0; $j < 5; ++$j){
					$d1 = $bb->minY + ($bb->maxY - $bb->minY) * $j / 5;
					$d3 = $bb->minY + ($bb->maxY - $bb->minY) * ($j + 1) / 5;

					$bb2 = new AxisAlignedBB($bb->minX, $d1, $bb->minZ, $bb->maxX, $d3, $bb->maxZ);

					if($this->level->isLiquidInBoundingBox($bb2, new Water())){
						$d10 += 0.2;
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
							$this->broadcastEntityEvent(ActorEventPacket::FISH_HOOK_HOOK);
							$this->motion->y -= 0.2;
							$this->ticksCatchable = mt_rand(10, 30);
						}else{
							$this->fishApproachAngle = $this->fishApproachAngle + $this->random->nextSignedFloat() * 4.0;
							$f7 = $this->fishApproachAngle * 0.01745;
							$f10 = sin($f7);
							$f11 = cos($f7);
							$d13 = $this->x + ($f10 * $this->ticksCatchableDelay * 0.1);
							$d15 = $this->y + 1;
							$d16 = $this->z + ($f11 * $this->ticksCatchableDelay * 0.1);
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
							$f1 = ($f1 + (20 - $this->ticksCaughtDelay) * 0.05);
						}elseif($this->ticksCaughtDelay < 40){
							$f1 = ($f1 + (40 - $this->ticksCaughtDelay) * 0.02);
						}elseif($this->ticksCaughtDelay < 60){
							$f1 = ($f1 + (60 - $this->ticksCaughtDelay) * 0.01);
						}

						if($this->random->nextFloat() < $f1){
							$f9 = mt_rand(0, 360) * 0.01745;
							$f2 = mt_rand(25, 60);
							$d12 = $this->x + (sin($f9) * $f2 * 0.1);
							$d14 = floor($this->y) + 1.0;
							$d6 = $this->z + (cos($f9) * $f2 * 0.1);
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
						$this->motion->y -= ($this->random->nextFloat() * $this->random->nextFloat() * $this->random->nextFloat()) * 0.2;
					}
				}

				$d11 = $d10 * 2.0 - 1.0;
				$this->motion->y += 0.04 * $d11;

				if($d10 > 0.0){
					$f6 = $f6 * 0.9;
					$this->motion->y *= 0.8;
				}

				$this->motion->x *= $f6;
				$this->motion->y *= $f6;
				$this->motion->z *= $f6;
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
			if($this->getRidingEntity() != null){
				$ev = new PlayerFishEvent($angler, $this, PlayerFishEvent::STATE_CAUGHT_ENTITY);
				$ev->call();

				if(!$ev->isCancelled()){
					$d0 = $angler->x - $this->x;
					$d2 = $angler->y - $this->y;
					$d4 = $angler->z - $this->z;
					$d6 = sqrt($d0 * $d0 + $d2 * $d2 + $d4 * $d4);
					$d8 = 0.1;
					$this->getRidingEntity()->setMotion(new Vector3($d0 * $d8, $d2 * $d8 + sqrt($d6) * 0.08, $d4 * $d8));
				}
			}elseif($this->ticksCatchable > 0){
				// TODO: Random weighted items
				$items = [
					Item::RAW_FISH, Item::PUFFERFISH, Item::RAW_SALMON, Item::CLOWNFISH
				];
				$randomFish = $items[mt_rand(0, count($items) - 1)];
				$result = ItemFactory::get($randomFish);

				$ev = new PlayerFishEvent($angler, $this, PlayerFishEvent::STATE_CAUGHT_FISH, $this->random->nextBoundedInt(6) + 1);
				$ev->call();

				if(!$ev->isCancelled()){
					$nbt = Entity::createBaseNBT($this);
					$nbt->setTag($result->nbtSerialize(-1, "Item"));

					$entityitem = new ItemEntity($this->level, $nbt);
					$d0 = $angler->x - $this->x;
					$d2 = $angler->y - $this->y;
					$d4 = $angler->z - $this->z;
					$d6 = sqrt($d0 * $d0 + $d2 * $d2 + $d4 * $d4);
					$d8 = 0.1;
					$entityitem->setMotion(new Vector3($d0 * $d8, $d2 * $d8 + sqrt($d6) * 0.08, $d4 * $d8));
					$entityitem->spawnToAll();
					$this->level->dropExperience($angler, $ev->getXpDropAmount());
				}
			}

			$this->flagForDespawn();
		}
	}

	protected function tryChangeMovement() : void{
		// NOOP
	}
}