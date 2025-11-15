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

namespace pocketmine\entity\projectile;

use pocketmine\block\Block;
use pocketmine\entity\animation\ArrowShakeAnimation;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\item\Item;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\EntityEventBroadcaster;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\world\sound\ArrowHitSound;
use function ceil;
use function mt_rand;
use function sqrt;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\PotionType;
use pocketmine\entity\effect\InstantEffect;

class Arrow extends Projectile{

	public static function getNetworkTypeId() : string{ return EntityIds::ARROW; }

	public const PICKUP_NONE = 0;
	public const PICKUP_ANY = 1;
	public const PICKUP_CREATIVE = 2;

	private const TAG_PICKUP = "pickup"; //TAG_Byte
	public const TAG_CRIT = "crit"; //TAG_Byte
	private const TAG_LIFE = "life"; //TAG_Short

	protected float $damage = 2.0;
	protected int $pickupMode = self::PICKUP_ANY;
	protected float $punchKnockback = 0.0;
	protected int $collideTicks = 0;
	protected bool $critical = false;
	/** @var Item|null The item that spawned this arrow (preserves custom arrow types) */
	private ?Item $projectileItem = null;

	public function __construct(Location $location, ?Entity $shootingEntity, bool $critical, ?CompoundTag $nbt = null){
		parent::__construct($location, $shootingEntity, $nbt);
		$this->setCritical($critical);
	}

	public function setProjectileItem(Item $item): void{
		$this->projectileItem = clone $item;
	}

	public function getProjectileItem(): ?Item{
		return $this->projectileItem !== null ? clone $this->projectileItem : null;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.25, 0.25); }

	protected function getInitialDragMultiplier() : float{ return 0.01; }

	protected function getInitialGravity() : float{ return 0.05; }

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->pickupMode = $nbt->getByte(self::TAG_PICKUP, self::PICKUP_ANY);
		$this->critical = $nbt->getByte(self::TAG_CRIT, 0) === 1;
		$this->collideTicks = $nbt->getShort(self::TAG_LIFE, $this->collideTicks);
		// restore projectile item if present
		$itemTag = $nbt->getCompoundTag("projectileItem");
		if($itemTag !== null){
			try{
				$item = Item::nbtDeserialize($itemTag);
				if($item !== null){
					$this->projectileItem = $item;
				}
			}catch(\Throwable $e){
				// ignore malformed item data
			}
		}
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setByte(self::TAG_PICKUP, $this->pickupMode);
		$nbt->setByte(self::TAG_CRIT, $this->critical ? 1 : 0);
		$nbt->setShort(self::TAG_LIFE, $this->collideTicks);
		if($this->projectileItem !== null){
			$nbt->setTag("projectileItem", $this->projectileItem->nbtSerialize());
		}
		return $nbt;
	}

	public function isCritical() : bool{
		return $this->critical;
	}

	public function setCritical(bool $value = true) : void{
		$this->critical = $value;
		$this->networkPropertiesDirty = true;
	}

	public function getResultDamage() : int{
		$base = (int) ceil($this->motion->length() * parent::getResultDamage());
		if($this->isCritical()){
			return ($base + mt_rand(0, (int) ($base / 2) + 1));
		}else{
			return $base;
		}
	}

	public function getPunchKnockback() : float{
		return $this->punchKnockback;
	}

	public function setPunchKnockback(float $punchKnockback) : void{
		$this->punchKnockback = $punchKnockback;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->blockHit !== null){
			$this->collideTicks += $tickDiff;
			if($this->collideTicks > 1200){
				$this->flagForDespawn();
				$hasUpdate = true;
			}
		}else{
			$this->collideTicks = 0;
		}

		return $hasUpdate;
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$this->setCritical(false);
		$this->broadcastSound(new ArrowHitSound());
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->broadcastAnimation(new ArrowShakeAnimation($this, 7));
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		parent::onHitEntity($entityHit, $hitResult);
		if($this->punchKnockback > 0){
			$horizontalSpeed = sqrt($this->motion->x ** 2 + $this->motion->z ** 2);
			if($horizontalSpeed > 0){
				$multiplier = $this->punchKnockback * 0.6 / $horizontalSpeed;
				$entityHit->setMotion($entityHit->getMotion()->add($this->motion->x * $multiplier, 0.1, $this->motion->z * $multiplier));
			}

			$projectileItem = $this->getProjectileItem();
			if($projectileItem !== null && $entityHit instanceof \pocketmine\entity\Living){
				$potionType = null;
				switch($projectileItem->getTypeId()){
					case ItemTypeIds::TIPPED_ARROW_NIGHT_VISION:
						$potionType = PotionType::NIGHT_VISION();
						break;
					case ItemTypeIds::TIPPED_ARROW_LONG_NIGHT_VISION:
						$potionType = PotionType::LONG_NIGHT_VISION();
						break;
					case ItemTypeIds::TIPPED_ARROW_INVISIBILITY:
						$potionType = PotionType::INVISIBILITY();
						break;
					case ItemTypeIds::TIPPED_ARROW_LONG_INVISIBILITY:
						$potionType = PotionType::LONG_INVISIBILITY();
						break;
					case ItemTypeIds::TIPPED_ARROW_LEAPING:
						$potionType = PotionType::LEAPING();
						break;
					case ItemTypeIds::TIPPED_ARROW_STRONG_LEAPING:
						$potionType = PotionType::STRONG_LEAPING();
						break;
					case ItemTypeIds::TIPPED_ARROW_SLOW_FALLING:
						$potionType = PotionType::SLOW_FALLING();
						break;
					case ItemTypeIds::TIPPED_ARROW_FIRE_RESISTANCE:
						$potionType = PotionType::FIRE_RESISTANCE();
						break;
					case ItemTypeIds::TIPPED_ARROW_SWIFTNESS:
						$potionType = PotionType::SWIFTNESS();
						break;
					case ItemTypeIds::TIPPED_ARROW_LONG_SWIFTNESS:
						$potionType = PotionType::LONG_SWIFTNESS();
						break;
					case ItemTypeIds::TIPPED_ARROW_STRONG_SWIFTNESS:
						$potionType = PotionType::STRONG_SWIFTNESS();
						break;
					case ItemTypeIds::TIPPED_ARROW_SLOWNESS:
						$potionType = PotionType::SLOWNESS();
						break;
					case ItemTypeIds::TIPPED_ARROW_LONG_SLOWNESS:
						$potionType = PotionType::LONG_SLOWNESS();
						break;
					case ItemTypeIds::TIPPED_ARROW_WATER_BREATHING:
						$potionType = PotionType::WATER_BREATHING();
						break;
					case ItemTypeIds::TIPPED_ARROW_HEALING:
						$potionType = PotionType::HEALING();
						break;
					case ItemTypeIds::TIPPED_ARROW_STRONG_HEALING:
						$potionType = PotionType::STRONG_HEALING();
						break;
					case ItemTypeIds::TIPPED_ARROW_HARMING:
						$potionType = PotionType::HARMING();
						break;
					case ItemTypeIds::TIPPED_ARROW_STRONG_HARMING:
						$potionType = PotionType::STRONG_HARMING();
						break;
					case ItemTypeIds::TIPPED_ARROW_POISON:
						$potionType = PotionType::POISON();
						break;
					case ItemTypeIds::TIPPED_ARROW_LONG_POISON:
						$potionType = PotionType::LONG_POISON();
						break;
					case ItemTypeIds::TIPPED_ARROW_REGENERATION:
						$potionType = PotionType::REGENERATION();
						break;
					case ItemTypeIds::TIPPED_ARROW_LONG_REGENERATION:
						$potionType = PotionType::LONG_REGENERATION();
						break;
					case ItemTypeIds::TIPPED_ARROW_STRONG_REGENERATION:
						$potionType = PotionType::STRONG_REGENERATION();
						break;
					case ItemTypeIds::TIPPED_ARROW_STRENGTH:
						$potionType = PotionType::STRENGTH();
						break;
					case ItemTypeIds::TIPPED_ARROW_LONG_STRENGTH:
						$potionType = PotionType::LONG_STRENGTH();
						break;
					case ItemTypeIds::TIPPED_ARROW_WEAKNESS:
						$potionType = PotionType::WEAKNESS();
						break;
					default:
						$potionType = null;
				}

				if($potionType !== null){
					$effects = $potionType->getEffects();
					foreach($effects as $effect){
						if($effect->getType() instanceof InstantEffect){
							$effect->getType()->applyEffect($entityHit, $effect, 1.0, $this);
						} else {
							$entityHit->getEffects()->add(clone $effect);
						}
					}
				}
			}
		}
	}

	public function getPickupMode() : int{
		return $this->pickupMode;
	}

	public function setPickupMode(int $pickupMode) : void{
		$this->pickupMode = $pickupMode;
	}

	public function onCollideWithPlayer(Player $player) : void{
		if($this->blockHit === null){
			return;
		}

		$item = $this->getProjectileItem() ?? VanillaItems::ARROW();
		$playerInventory = match(true){
			!$player->hasFiniteResources() => null, //arrows are not picked up in creative
			$player->getOffHandInventory()->getItem(0)->canStackWith($item) && $player->getOffHandInventory()->canAddItem($item) => $player->getOffHandInventory(),
			$player->getInventory()->canAddItem($item) => $player->getInventory(),
			default => null
		};

		$ev = new EntityItemPickupEvent($player, $this, $item, $playerInventory);
		if($player->hasFiniteResources() && $playerInventory === null){
			$ev->cancel();
		}
		if($this->pickupMode === self::PICKUP_NONE || ($this->pickupMode === self::PICKUP_CREATIVE && !$player->isCreative())){
			$ev->cancel();
		}

		$ev->call();
		if($ev->isCancelled()){
			return;
		}

		NetworkBroadcastUtils::broadcastEntityEvent(
			$this->getViewers(),
			fn(EntityEventBroadcaster $broadcaster, array $recipients) => $broadcaster->onPickUpItem($recipients, $player, $this)
		);

		$ev->getInventory()?->addItem($ev->getItem());
		$this->flagForDespawn();
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::CRITICAL, $this->critical);
	}
}
