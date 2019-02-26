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
use pocketmine\entity\Entity;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\Player;
use function mt_rand;
use function sqrt;

class Arrow extends Projectile{
	public const NETWORK_ID = self::ARROW;

	public const PICKUP_NONE = 0;
	public const PICKUP_ANY = 1;
	public const PICKUP_CREATIVE = 2;

	private const TAG_PICKUP = "pickup"; //TAG_Byte

	public $width = 0.25;
	public $height = 0.25;

	protected $gravity = 0.05;
	protected $drag = 0.01;

	/** @var float */
	protected $damage = 2.0;

	/** @var int */
	protected $pickupMode = self::PICKUP_ANY;

	/** @var float */
	protected $punchKnockback = 0.0;

	/** @var int */
	protected $collideTicks = 0;

	public function __construct(Level $level, CompoundTag $nbt, ?Entity $shootingEntity = null, bool $critical = false){
		parent::__construct($level, $nbt, $shootingEntity);
		$this->setCritical($critical);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->pickupMode = $nbt->getByte(self::TAG_PICKUP, self::PICKUP_ANY, true);
		$this->collideTicks = $nbt->getShort("life", $this->collideTicks);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setByte(self::TAG_PICKUP, $this->pickupMode);
		$nbt->setShort("life", $this->collideTicks);
		return $nbt;
	}

	public function isCritical() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_CRITICAL);
	}

	public function setCritical(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_CRITICAL, $value);
	}

	public function getResultDamage() : int{
		$base = parent::getResultDamage();
		if($this->isCritical()){
			return ($base + mt_rand(0, (int) ($base / 2) + 1));
		}else{
			return $base;
		}
	}

	/**
	 * @return float
	 */
	public function getPunchKnockback() : float{
		return $this->punchKnockback;
	}

	/**
	 * @param float $punchKnockback
	 */
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
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BOW_HIT);
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->broadcastEntityEvent(EntityEventPacket::ARROW_SHAKE, 7); //7 ticks
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		parent::onHitEntity($entityHit, $hitResult);
		if($this->punchKnockback > 0){
			$horizontalSpeed = sqrt($this->motion->x ** 2 + $this->motion->z ** 2);
			if($horizontalSpeed > 0){
				$multiplier = $this->punchKnockback * 0.6 / $horizontalSpeed;
				$entityHit->setMotion($entityHit->getMotion()->add($this->motion->x * $multiplier, 0.1, $this->motion->z * $multiplier));
			}
		}
	}

	/**
	 * @return int
	 */
	public function getPickupMode() : int{
		return $this->pickupMode;
	}

	/**
	 * @param int $pickupMode
	 */
	public function setPickupMode(int $pickupMode) : void{
		$this->pickupMode = $pickupMode;
	}

	public function onCollideWithPlayer(Player $player) : void{
		if($this->blockHit === null){
			return;
		}

		$item = ItemFactory::get(Item::ARROW, 0, 1);

		$playerInventory = $player->getInventory();
		if($player->isSurvival() and !$playerInventory->canAddItem($item)){
			return;
		}

		$ev = new InventoryPickupArrowEvent($playerInventory, $this);
		if($this->pickupMode === self::PICKUP_NONE or ($this->pickupMode === self::PICKUP_CREATIVE and !$player->isCreative())){
			$ev->setCancelled();
		}

		$ev->call();
		if($ev->isCancelled()){
			return;
		}

		$pk = new TakeItemEntityPacket();
		$pk->eid = $player->getId();
		$pk->target = $this->getId();
		$this->server->broadcastPacket($this->getViewers(), $pk);

		$playerInventory->addItem(clone $item);
		$this->flagForDespawn();
	}
}
