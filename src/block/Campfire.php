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

namespace pocketmine\block;

use pocketmine\block\tile\Campfire as TileCampfire;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\FurnaceType;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use pocketmine\item\PotionType;
use pocketmine\item\Shovel;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\FlintSteelSound;
use pocketmine\world\sound\ItemFrameAddItemSound;
use function count;

class Campfire extends Transparent{
	use FacesOppositePlacingPlayerTrait;
	use HorizontalFacingTrait;

	protected bool $extinguished = false;
	/** @var Item[] */
	protected array $items = [];
	/** @var int[] */
	protected array $itemTime = [];


	public function writeStateToMeta() : int{
		return BlockDataSerializer::writeLegacyHorizontalFacing($this->facing) | ($this->extinguished ? BlockLegacyMetadata::CAMPFIRE_FLAG_EXTINGUISHED : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readLegacyHorizontalFacing($stateMeta & 0x03);
		$this->extinguished = ($stateMeta & BlockLegacyMetadata::CAMPFIRE_FLAG_EXTINGUISHED) === BlockLegacyMetadata::CAMPFIRE_FLAG_EXTINGUISHED;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileCampfire){
			$this->items = $tile->getInventory()->getContents();
			$this->itemTime = $tile->getItemTimes();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileCampfire){
			$tile->getInventory()->setContents($this->items);
			$tile->setItemTimes($this->itemTime);
		}
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function getLightLevel() : int{
		return $this->extinguished ? 0 : 15;
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaItems::CHARCOAL()->setCount(2)
		];
	}

	public function isExtinguished() : bool{
		return $this->extinguished;
	}

	/** @return $this */
	public function setExtinguished(bool $extinguish = true) : self{
		$this->extinguished = $extinguish;
		return $this;
	}

	public function getFurnaceType() : FurnaceType{
		return FurnaceType::CAMPFIRE();
	}

	public function canCook(Item $item) : bool{
		return $this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager($this->getFurnaceType())->match($item) instanceof FurnaceRecipe;
	}

	public function canAddItem(Item $item) : bool{
		if(count($this->items) >= 4){
			return false;
		}
		return $this->canCook($item);
	}

	public function setItem(Item $item, int $slot) : void{
		if($slot < 0 or $slot > 3){
			throw new \InvalidArgumentException("Slot must be in range 0-3, got " . $slot);
		}
		if($item->isNull()){
			if(isset($this->items[$slot])){
				unset($this->items[$slot]);
			}
		}else{
			$this->items[$slot] = $item;
		}
	}

	public function setSlotTime(int $slot, int $time) : void{
		$this->itemTime[$slot] = $time;
	}

	public function getSlotTime(int $slot) : int{
		return $this->itemTime[$slot] ?? 0;
	}

	public function addItem(Item $item) : bool{
		$item->setCount(1);
		if(!$this->canAddItem($item)){
			return false;
		}
		$this->setItem($item, count($this->items));
		return true;
	}

	private function increaseSlotTime(int $slot) : void{
		$this->setSlotTime($slot, $this->getSlotTime($slot) + 1);
	}

	private function extinguish() : void{
		$this->position->getWorld()->addSound($this->position, new FireExtinguishSound());
		$this->position->getWorld()->setBlock($this->position, $this->setExtinguished());
	}

	private function fire() : void{
		$this->position->getWorld()->addSound($this->position, new FlintSteelSound());
		$this->position->getWorld()->setBlock($this->position, $this->setExtinguished(false));
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			return false;
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onBreak(Item $item, ?Player $player = null) : bool{
		$this->items = [];
		$this->itemTime = [];
		return parent::onBreak($item, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			if($item instanceof FlintSteel){
				if($this->extinguished){
					$item->applyDamage(1);
					$this->fire();
				}
				return true;
			}
			if($item instanceof Shovel && !$this->extinguished){
				$item->applyDamage(1);
				$this->extinguish();
				return true;
			}

			if($this->addItem(clone $item)){
				$item->pop();
				$this->position->getWorld()->addSound($this->position->add(0.5, 0.5, 0.5), new ItemFrameAddItemSound());
				$this->position->getWorld()->setBlock($this->position, $this);
				if(count($this->items) === 1){
					$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
				}
				return true;
			}
		}
		return false;
	}

	public function onNearbyBlockChange() : void{
		$block = $this->getSide(Facing::UP);
		if($block instanceof Water && !$this->extinguished){
			$this->extinguish();
		}
	}

	public function onEntityInside(Entity $entity) : bool{
		if($this->extinguished){
			if($entity->isOnFire()){
				$this->fire();
				return true;
			}
			return false;
		}
		if($entity instanceof SplashPotion && $entity->getPotionType()->getDisplayName() === PotionType::WATER()->getDisplayName()){
			$this->extinguish();
			return true;
		}elseif($entity instanceof Living){
			$entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 1));
			$entity->setOnFire(8);
		}
		return false;
	}

	public function onScheduledUpdate() : void{
		if(!$this->extinguished){
			foreach($this->items as $slot => $item){
				$this->increaseSlotTime($slot);
				if($this->getSlotTime($slot) >= $this->getFurnaceType()->getCookDurationTicks()){
					$this->setItem(VanillaItems::AIR(), $slot);
					$this->setSlotTime($slot, 0);
					$result = ($item = $this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager($this->getFurnaceType())->match($item)) instanceof FurnaceRecipe ? $item->getResult() : VanillaItems::AIR();
					$this->position->getWorld()->dropItem($this->position->add(0, 1, 0), $result);
				}
			}
			$this->position->getWorld()->setBlock($this->position, $this);
			if(!empty($this->items)){
				$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
			}
		}
	}
}