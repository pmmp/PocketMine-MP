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

use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace as TileFurnace;
use pocketmine\block\tile\Hopper as TileHopper;
use pocketmine\block\tile\Jukebox as TileJukebox;
use pocketmine\block\tile\ShulkerBox as TileShulkerBox;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Bucket;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Record;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function count;

class Hopper extends Transparent{
	use PoweredByRedstoneTrait;

	private int $facing = Facing::DOWN;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$facing = BlockDataSerializer::readFacing($stateMeta & 0x07);
		if($facing === Facing::UP){
			throw new InvalidBlockStateException("Hopper may not face upward");
		}
		$this->facing = $facing;
		$this->powered = ($stateMeta & BlockLegacyMetadata::HOPPER_FLAG_POWERED) !== 0;
	}

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::writeFacing($this->facing) | ($this->powered ? BlockLegacyMetadata::HOPPER_FLAG_POWERED : 0);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getFacing() : int{ return $this->facing; }

	/** @return $this */
	public function setFacing(int $facing) : self{
		if($facing === Facing::UP){
			throw new \InvalidArgumentException("Hopper may not face upward");
		}
		$this->facing = $facing;
		return $this;
	}

	protected function recalculateCollisionBoxes() : array{
		$result = [
			AxisAlignedBB::one()->trim(Facing::UP, 6 / 16) //the empty area around the bottom is currently considered solid
		];

		foreach(Facing::HORIZONTAL as $f){ //add the frame parts around the bowl
			$result[] = AxisAlignedBB::one()->trim($f, 14 / 16);
		}
		return $result;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->facing = $face === Facing::DOWN ? Facing::DOWN : Facing::opposite($face);

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$tile = $this->position->getWorld()->getTile($this->position);
			if($tile instanceof TileHopper){ //TODO: find a way to have inventories open on click without this boilerplate in every block
				$player->setCurrentWindow($tile->getInventory());
			}
			return true;
		}
		return false;
	}

	public function onScheduledUpdate() : void{
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);

		$tile = $this->position->getWorld()->getTile($this->position);
		if(!$tile instanceof TileHopper){
			return;
		}

		$transferCooldown = $tile->getTransferCooldown();
		if($transferCooldown > 0){
			$transferCooldown--;
			$tile->setTransferCooldown($transferCooldown);
		}

		if($this->isPowered()){
			// If a hopper is powered it is deactivated and won't push, pull or pick up items.
			return;
		}

		if($transferCooldown > 0){
			// Skipping this tick because the hopper is still on cooldown of 8 ticks.
			return;
		}

		$success = $this->push($tile);
		// Hoppers that have a container above them, won't try to pick up items.
		$origin = $this->position->getWorld()->getTile($this->position->getSide(Facing::UP));
		if($origin instanceof Container){
			$success |= $this->pull($tile, $origin);
		}else{
			$success |= $this->pickup($tile);
		}
		// The cooldown is only set back to 8 ticks if the hopper has done anything.
		if((bool) $success){
			$tile->setTransferCooldown(8);
		}
	}

	/**
	 * This function handles pushing items from the hopper to a tile in the direction the hopper is facing.
	 * Returns true if an item was successfully pushed or false on failure.
	 */
	private function push(TileHopper $tile) : bool{
		if(count($tile->getInventory()->getContents()) === 0){
			return false;
		}
		$destination = $this->position->getWorld()->getTile($this->position->getSide($this->facing));
		if($destination === null){
			return false;
		}

		for($slot = 0; $slot < $tile->getInventory()->getSize(); $slot++){
			$item = $tile->getInventory()->getItem($slot);
			if($item->isNull()){
				continue;
			}

			// Hoppers interact differently when pushing into different kinds of tiles.
			// TODO Composter
			// TODO Brewing Stand
			// TODO Jukebox (improve)
			if($destination instanceof TileFurnace){
				// If the hopper is facing down, it will push every item to the furnace's smelting slot, even items that aren't smeltable.
				// If the hopper is facing in any other direction, it will only push items that can be used as fuel to the furnace's fuel slot.
				if($this->facing === Facing::DOWN){
					// ID of the smelting slot
					$slot = 0;
					$itemInFurnace = $destination->getInventory()->getSmelting();
				}else{
					if($item->getFuelTime() === 0){
						continue;
					}
					// ID of the fuel slot
					$slot = 1;
					$itemInFurnace = $destination->getInventory()->getFuel();
				}
				if(!$itemInFurnace->isNull()){
					if($itemInFurnace->getCount() >= $itemInFurnace->getMaxStackSize()){
						return false;
					}
					if(!$itemInFurnace->canStackWith($item)){
						continue;
					}
					$item->pop();
					$itemInFurnace->setCount($itemInFurnace->getCount() + 1);
				}else{
					$itemInFurnace = $item->pop();
				}

				// TODO event on item inventory switch

				$destination->getInventory()->setItem($slot, $itemInFurnace);
				$tile->getInventory()->setItem($slot, $item->isNull() ? ItemFactory::air() : $item);
				return true;

			}elseif($destination instanceof TileHopper){
				$itemToPush = $item->pop();
				if(!$destination->getInventory()->canAddItem($itemToPush)){
					continue;
				}
				// Hoppers pushing into empty hoppers set the empty hoppers transfer cooldown back to 8 ticks.
				if(count($destination->getInventory()->getContents()) === 0){
					$destination->setTransferCooldown(8);
				}

			}elseif($destination instanceof TileJukebox){
				if(!$item instanceof Record){
					continue;
				}
				// TODO
				// Jukeboxes actually emit a redstone signal when playing a record so nearby hoppers are blocked and
				// prevented from inserting another disk. Because neither does redstone work properly nor can we check if
				// a jukebox is still playing a record or has already finished it, we can just check if it has already a
				// record inserted.
				if($destination->getRecord() !== null){
					return false;
				}

				// The Jukebox block is handling the playing of records, so we need to get it here and can't use TileJukebox::setRecord().
				$jukeboxBlock = $destination->getBlock();
				if($jukeboxBlock instanceof Jukebox){
					$jukeboxBlock->insertRecord($item->pop());
					$jukeboxBlock->getPosition()->getWorld()->setBlock($jukeboxBlock->getPosition(), $jukeboxBlock);
					$tile->getInventory()->setItem($slot, $item->isNull() ? ItemFactory::air() : $item);
				}
				return true;

			}elseif($destination instanceof TileShulkerBox){
				// Hoppers can't push a shulkerbox into another shulkerbox.
				if($item->getBlock() instanceof ShulkerBox){
					continue;
				}
				$itemToPush = $item->pop();
				if(!$destination->getInventory()->canAddItem($itemToPush)){
					continue;
				}

			}elseif($destination instanceof Container){
				$itemToPush = $item->pop();
				if(!$destination->getInventory()->canAddItem($itemToPush)){
					continue;
				}

			}else{
				return false;
			}

			// TODO event on item inventory switch

			$tile->getInventory()->setItem($slot, $item->isNull() ? ItemFactory::air() : $item);
			$destination->getInventory()->addItem($itemToPush);
			return true;
		}
		return false;
	}

	/**
	 * This function handles pulling items by the hopper from a container above.
	 * Returns true if an item was successfully pulled or false on failure.
	 */
	private function pull(TileHopper $tile, Container $origin) : bool{
		// Hoppers interact differently when pulling from different kinds of tiles.
		// TODO Composter
		// TODO Brewing Stand
		// TODO Jukebox
		if($origin instanceof TileFurnace){
			// Hoppers either pull empty buckets from the furnace's fuel slot or pull from its result slot.
			// They prioritise pulling from the fuel slot over the result slot.
			$item = $origin->getInventory()->getFuel();
			if($item instanceof Bucket){
				// ID of the fuel slot
				$slot = 1;
			}else{
				// ID of the result slot
				$slot = 2;
				$item = $origin->getInventory()->getResult();
				if($item->isNull()){
					return false;
				}
			}
			$itemToPull = $item->pop();

			// TODO event on item inventory switch

			$origin->getInventory()->setItem($slot, $item->isNull() ? ItemFactory::air() : $item);
			$tile->getInventory()->addItem($itemToPull);
			return true;

		}else{
			for($slot = 0; $slot < $origin->getInventory()->getSize(); $slot++){
				$item = $origin->getInventory()->getItem($slot);
				if($item->isNull()){
					continue;
				}
				$itemToPull = $item->pop();
				if(!$tile->getInventory()->canAddItem($itemToPull)){
					continue;
				}

				// TODO event on item inventory switch

				$origin->getInventory()->setItem($slot, $item->isNull() ? ItemFactory::air() : $item);
				$tile->getInventory()->addItem($itemToPull);
				return true;
			}
		}
		return false;
	}

	/**
	 * This function handles picking up items by the hopper.
	 * Returns true if an item was successfully picked up or false on failure.
	 */
	private function pickup(TileHopper $tile) : bool{
		// In Bedrock Edition hoppers collect from the lower 3/4 of the block space above them.
		$pickupCollisionBox = new AxisAlignedBB(
			$this->position->getX(),
			$this->position->getY() + 1,
			$this->position->getZ(),
			$this->position->getX() + 1,
			$this->position->getY() + 1.75,
			$this->position->getZ() + 1
		);

		foreach($this->position->getWorld()->getNearbyEntities($pickupCollisionBox) as $entity){
			if($entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity instanceof ItemEntity){
				continue;
			}
			// Unlike Java Edition, Bedrock Edition's hoppers don't save in which order item entities landed on top of them to collect them in that order.
			// In Bedrock Edition hoppers collect item entities in the order in which they entered the chunk.
			// Because of how entities are saved by PocketMine-MP the first entities of this loop are also the first ones who were saved.
			// That's why we don't need to implement any sorting mechanism.
			$item = $entity->getItem();
			if(!$tile->getInventory()->canAddItem($item)){
				continue;
			}

			// TODO event on block picking up an item

			$tile->getInventory()->addItem($item);
			$entity->flagForDespawn();
			return true;
		}
		return false;
	}
}
