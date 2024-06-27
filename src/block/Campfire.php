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

use pocketmine\block\inventory\CampfireInventory;
use pocketmine\block\tile\Campfire as TileCampfire;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\LightableTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\FurnaceType;
use pocketmine\data\runtime\RuntimeDataDescriber;
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
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Limits;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\CampfireSound;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\FlintSteelSound;
use pocketmine\world\sound\ItemFrameAddItemSound;
use function count;
use function mt_rand;

class Campfire extends Transparent{
	use HorizontalFacingTrait{
		HorizontalFacingTrait::describeBlockOnlyState as encodeFacingState;
	}
	use LightableTrait{
		LightableTrait::describeBlockOnlyState as encodeLitState;
	}

	protected CampfireInventory $inventory;

	/** @var int[] slot => ticks */
	protected array $cookingTimes = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$this->encodeFacingState($w);
		$this->encodeLitState($w);
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileCampfire){
			$this->inventory = $tile->getInventory();
			$this->cookingTimes = $tile->getCookingTimes();
		}else{
			$this->inventory = new CampfireInventory($this->position);
		}

		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileCampfire){
			$tile->setCookingTimes($this->cookingTimes);
		}
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function getLightLevel() : int{
		return $this->lit ? 15 : 0;
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaItems::CHARCOAL()->setCount(2)
		];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function getInventory() : CampfireInventory{
		return $this->inventory;
	}

	/** Sets the number of ticks left to cook the item to the given slot */
	public function setCookingTime(int $slot, int $time) : void{
		if($slot < 0 || $slot > 3){
			throw new \InvalidArgumentException("Slot must be in range 0-3");
		}
		if($time < 0 || $time > Limits::INT32_MAX){
			throw new \InvalidArgumentException("CookingTime must be in range 0-" . Limits::INT32_MAX);
		}

		$this->cookingTimes[$slot] = $time;
	}

	/** Returns the number of ticks left to cook the item in the given slot */
	public function getCookingTime(int $slot) : int{
		return $this->cookingTimes[$slot] ?? 0;
	}

	private function extinguish() : void{
		$this->position->getWorld()->addSound($this->position, new FireExtinguishSound());
		$this->position->getWorld()->setBlock($this->position, $this->setLit(false));
	}

	private function fire() : void{
		$this->position->getWorld()->addSound($this->position, new FlintSteelSound());
		$this->position->getWorld()->setBlock($this->position, $this->setLit(true));
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->getSide(Facing::DOWN)->getSupportType(Facing::UP)->hasCenterSupport()){
			return false;
		}
		if($player !== null){
			$this->facing = $player->getHorizontalFacing();
		}
		$this->lit = true;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player !== null){
			if($item instanceof FlintSteel){
				if(!$this->lit){
					$item->applyDamage(1);
					$this->fire();
				}
				return true;
			}
			if($item instanceof Shovel && $this->lit){
				$item->applyDamage(1);
				$this->extinguish();
				return true;
			}

			if($this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::CAMPFIRE)->match($item) !== null){
				$ingredient = clone $item;
				$ingredient->setCount(1);
				if(count($this->inventory->addItem($ingredient)) === 0){
					$item->pop();
					$this->position->getWorld()->addSound($this->position, new ItemFrameAddItemSound());
					return true;
				}
			}
		}
		return false;
	}

	public function onNearbyBlockChange() : void{
		$block = $this->getSide(Facing::UP);
		if($block instanceof Water && $this->lit){
			$this->extinguish();
		}
	}

	public function onEntityInside(Entity $entity) : bool{
		if(!$this->lit){
			if($entity->isOnFire()){
				$this->fire();
				return true;
			}
			return false;
		}
		if($entity instanceof SplashPotion && $entity->getPotionType()->equals(PotionType::WATER)){
			$this->extinguish();
			return true;
		}elseif($entity instanceof Living){
			$entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 1));
			$entity->setOnFire(8);
			return false;
		}
		return false;
	}

	public function onScheduledUpdate() : void{
		if($this->lit){
			$items = $this->inventory->getContents();
			foreach($items as $slot => $item){
				$this->setCookingTime($slot, $this->getCookingTime($slot) + 20);
				if($this->getCookingTime($slot) >= FurnaceType::CAMPFIRE->getCookDurationTicks()){
					$this->inventory->setItem($slot, VanillaItems::AIR());
					$this->setCookingTime($slot, 0);
					$result = ($item = $this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::CAMPFIRE)->match($item)) instanceof FurnaceRecipe ? $item->getResult() : VanillaItems::AIR();
					$this->position->getWorld()->dropItem($this->position->add(0, 1, 0), $result);
				}
			}
			if(count($items) > 0){
				$this->position->getWorld()->setBlock($this->position, $this);
			}
			if(mt_rand(1, 10) === 1){
				$this->position->getWorld()->addSound($this->position, new CampfireSound());
			}

			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20);
		}
	}
}
