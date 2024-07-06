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
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\block\CampfireCookEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\PotionType;
use pocketmine\item\Shovel;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\BlazeShootSound;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\FlintSteelSound;
use pocketmine\world\sound\ItemFrameAddItemSound;
use function count;
use function min;
use function mt_rand;

class Campfire extends Transparent{
	use HorizontalFacingTrait{
		HorizontalFacingTrait::describeBlockOnlyState as encodeFacingState;
	}
	use LightableTrait{
		LightableTrait::describeBlockOnlyState as encodeLitState;
	}

	private const UPDATE_INTERVAL_TICKS = 10;

	protected CampfireInventory $inventory;

	/**
	 * @var int[] slot => ticks
	 * @phpstan-var array<int, int>
	 */
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

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 9 / 16)];
	}

	public function getInventory() : CampfireInventory{
		return $this->inventory;
	}

	protected function getFurnaceType() : FurnaceType{
		return FurnaceType::CAMPFIRE;
	}

	protected function getEntityCollisionDamage() : int{
		return 1;
	}

	/**
	 * Sets the number of ticks during the item in the given slot has been cooked.
	 */
	public function setCookingTime(int $slot, int $time) : void{
		if($slot < 0 || $slot > 3){
			throw new \InvalidArgumentException("Slot must be in range 0-3");
		}
		if($time < 0 || $time > $this->getFurnaceType()->getCookDurationTicks()){
			throw new \InvalidArgumentException("CookingTime must be in range 0-" . $this->getFurnaceType()->getCookDurationTicks());
		}
		$this->cookingTimes[$slot] = $time;
	}

	/**
	 * Returns the number of ticks during the item in the given slot has been cooked.
	 */
	public function getCookingTime(int $slot) : int{
		return $this->cookingTimes[$slot] ?? 0;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->getSide(Facing::DOWN) instanceof Campfire){
			return false;
		}
		if($player !== null){
			$this->facing = $player->getHorizontalFacing();
		}
		$this->lit = true;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if(!$this->lit){
			if($item->getTypeId() === ItemTypeIds::FIRE_CHARGE){
				$item->pop();
				$this->ignite();
				$this->position->getWorld()->addSound($this->position, new BlazeShootSound());
				return true;
			}elseif($item->getTypeId() === ItemTypeIds::FLINT_AND_STEEL || $item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())){
				if($item instanceof Durable){
					$item->applyDamage(1);
				}
				$this->ignite();
				return true;
			}
		}elseif($item instanceof Shovel){
			$item->applyDamage(1);
			$this->extinguish();
			return true;
		}

		if($this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager($this->getFurnaceType())->match($item) !== null){
			$ingredient = clone $item;
			$ingredient->setCount(1);
			if(count($this->inventory->addItem($ingredient)) === 0){
				$item->pop();
				$this->position->getWorld()->addSound($this->position, new ItemFrameAddItemSound());
				return true;
			}
		}
		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->lit && $this->getSide(Facing::UP)->getTypeId() === BlockTypeIds::WATER){
			$this->extinguish();
			//TODO: Waterlogging
		}
	}

	public function onEntityInside(Entity $entity) : bool{
		if(!$this->lit){
			if($entity->isOnFire()){
				$this->ignite();
				return false;
			}
		}elseif($entity instanceof Living){
			$entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, $this->getEntityCollisionDamage()));
		}
		return true;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		if($this->lit && $projectile instanceof SplashPotion && $projectile->getPotionType() === PotionType::WATER){
			$this->extinguish();
		}
	}

	public function onScheduledUpdate() : void{
		if($this->lit){
			$items = $this->inventory->getContents();
			$furnaceType = $this->getFurnaceType();
			$maxCookDuration = $furnaceType->getCookDurationTicks();
			foreach($items as $slot => $item){
				$this->setCookingTime($slot, min($maxCookDuration, $this->getCookingTime($slot) + self::UPDATE_INTERVAL_TICKS));
				if($this->getCookingTime($slot) >= $maxCookDuration){
					$result =
						($recipe = $this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager($furnaceType)->match($item)) instanceof FurnaceRecipe ?
							$recipe->getResult() :
							VanillaItems::AIR();

					$ev = new CampfireCookEvent($this, $slot, $item, $result);
					$ev->call();

					if ($ev->isCancelled()){
						continue;
					}

					$this->inventory->setItem($slot, VanillaItems::AIR());
					$this->setCookingTime($slot, 0);
					$this->position->getWorld()->dropItem($this->position->add(0.5, 1, 0.5), $ev->getResult());
				}
			}
			if(count($items) > 0){
				$this->position->getWorld()->setBlock($this->position, $this);
			}
			if(mt_rand(1, 6) === 1){
				$this->position->getWorld()->addSound($this->position, $furnaceType->getCookSound());
			}
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, self::UPDATE_INTERVAL_TICKS);
		}
	}

	private function extinguish() : void{
		$this->position->getWorld()->addSound($this->position, new FireExtinguishSound());
		$this->position->getWorld()->setBlock($this->position, $this->setLit(false));
	}

	private function ignite() : void{
		$this->position->getWorld()->addSound($this->position, new FlintSteelSound());
		$this->position->getWorld()->setBlock($this->position, $this->setLit(true));
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, self::UPDATE_INTERVAL_TICKS);
	}
}
