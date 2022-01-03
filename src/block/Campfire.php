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
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
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
	use HorizontalFacingTrait;

	protected bool $extinguished = false;

	public function writeStateToMeta() : int{
		return $this->facing | ($this->extinguished ? BlockLegacyMetadata::CAMPFIRE_FLAG_EXTINGUISHED : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = $stateMeta & 0x03;
		$this->extinguished = ($stateMeta & BlockLegacyMetadata::CAMPFIRE_FLAG_EXTINGUISHED) === BlockLegacyMetadata::CAMPFIRE_FLAG_EXTINGUISHED;
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
		if($player !== null){
			$faces = [
				4 => 3,
				3 => 2,
				2 => 0,
				5 => 1
			];
			$this->facing = $faces[$player->getHorizontalFacing()];
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
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

			$tile = $this->position->getWorld()->getTile($this->position);
			if($tile instanceof TileCampfire && $tile->addItem(clone $item)){
				$item->pop();
				$this->position->getWorld()->addSound($this->position->add(0.5, 0.5, 0.5), new ItemFrameAddItemSound());
				$this->position->getWorld()->setBlock($this->position, $this);
				if(count($tile->getContents()) == 1) $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
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
		if($entity instanceof SplashPotion && $entity->getPotionType()->getDisplayName() == PotionType::WATER()->getDisplayName()){
			$this->extinguish();
			return true;
		}elseif($entity instanceof Living){
			$entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 1));
			$entity->setOnFire(8);
		}
		return false;
	}

	public function onScheduledUpdate() : void{
		$tile = $this->position->getWorld()->getTile($this->position);

		if($tile instanceof TileCampfire && !$tile->closed){
			if(!$this->extinguished){
				foreach($tile->getContents() as $slot => $item){
					$tile->increaseSlotTime($slot);
					if($tile->getItemTime($slot) >= $tile->getFurnaceType()->getCookDurationTicks()){
						$tile->setItem(ItemFactory::air(), $slot);
						$tile->setSlotTime($slot, 0);
						$this->position->world->setBlock($this->position, $this);
						$result = ($item = $this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager($tile->getFurnaceType())->match($item)) instanceof FurnaceRecipe ? $item->getResult() : ItemFactory::air();
						$this->position->getWorld()->dropItem($this->position->add(0, 1, 0), $result);
					}
				}
				if(!empty($tile->getContents())) $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
			}
		}
	}
}