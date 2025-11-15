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
use pocketmine\block\tile\Shelf as TileShelf;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\WoodMaterial;
use pocketmine\block\utils\WoodType;
use pocketmine\block\utils\WoodTypeTrait;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\math\Axis;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\BlockTransaction;

/**
 * Shelf block - stores and displays up to 3 items
 */
class Shelf extends Opaque implements WoodMaterial, HorizontalFacing{
	use WoodTypeTrait;
	use HorizontalFacingTrait;
	use PoweredByRedstoneTrait;

	// 0..3 value used for POWERED_SHELF_TYPE mapping
	protected int $poweredShelfType = 0;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, WoodType $woodType, private \Closure $asItemCallback){
		$this->woodType = $woodType;
		$this->facing = Facing::SOUTH;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->bool($this->powered);
		$w->boundedIntAuto(0, 3, $this->poweredShelfType);
	}

	public function getShelfType() : int{
		return $this->poweredShelfType;
	}

	/** @return $this */
	public function setShelfType(int $type) : self{
		if($type < 0 || $type > 3) throw new \InvalidArgumentException("Shelf type must be between 0 and 3");
		$this->poweredShelfType = $type;
		return $this;
	}

	public function asItem() : Item{
		return ($this->asItemCallback)();
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->setFacing(Facing::opposite($player->getHorizontalFacing()));
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player === null) return false;

		$tile = $this->position->getWorld()->getTile($this->position);
		if(!$tile instanceof TileShelf) return false;

		// Only accept clicks on the front face and middle vertical band
		if($face !== $this->facing || $clickVector->y <= 0.25 || $clickVector->y >= 0.75) return false;

		// Determine clicked slot in the same visual orientation the Tile uses.
		// TileShelf computes a "front" as the opposite of block facing and
		// reverses visual order for NORTH/WEST fronts. Mirror that logic here
		// so clicks map to the same left/center/right ordering the client sees.
		$x = Facing::axis($face) === Axis::X ? $clickVector->z : $clickVector->x;
		$front = Facing::opposite($this->facing);
		$reverse = $front === Facing::NORTH || $front === Facing::WEST;
		$proj = $reverse ? 1.0 - $x : $x;
		$slot = $proj < (1.0 / 3.0) ? 0 : ($proj < (2.0 / 3.0) ? 1 : 2);

		try{ Server::getInstance()->getLogger()->info("Shelf:onInteract pos=" . $this->position->getFloorX() . "/" . $this->position->getFloorY() . "/" . $this->position->getFloorZ() . " facing=" . $this->facing . " click=(" . round($clickVector->x,3) . "," . round($clickVector->y,3) . "," . round($clickVector->z,3) . ") slot=" . $slot); }catch(\Throwable){}

		$inventory = $tile->getInventory();

		// If not powered: single-shelf behaviour
		if(!$this->isGettingPower()){
			$handItem = $player->getInventory()->getItemInHand();
			$slotItem = $inventory->getItem($slot);

			if($player->isSneaking()){
				if($slotItem->isNull()){
					try{ Server::getInstance()->getLogger()->info("Shelf:remove attempted on empty slot={$slot}"); }catch(\Throwable){}
					return false;
				}
				if($player->getInventory()->canAddItem($slotItem)){
					try{ Server::getInstance()->getLogger()->info("Shelf:removing from slot={$slot} item=" . $slotItem->getName() . " count=" . $slotItem->getCount()); }catch(\Throwable){}
					$player->getInventory()->addItem($slotItem);
					$inventory->setItem($slot, VanillaItems::AIR());
					// $this->updateShelfType($inventory); // Removed
					$tile->clearSpawnCompoundCache();
					$tile->setDirty();
					return true;
				}
				return false;
			}

			if($handItem->isNull()) return false;

			if($slotItem->isNull()){
				$toPlace = $handItem->pop();
				$inventory->setItem($slot, $toPlace);
				$player->getInventory()->setItemInHand($handItem);
				// $this->updateShelfType($inventory); // Removed
				$tile->clearSpawnCompoundCache();
				$tile->setDirty();
				return true;
			}

			if($slotItem->canStackWith($handItem) && $slotItem->getCount() < $slotItem->getMaxStackSize()){
				$popped = $handItem->pop();
				$slotItem->setCount($slotItem->getCount() + $popped->getCount());
				$inventory->setItem($slot, $slotItem);
				$player->getInventory()->setItemInHand($handItem);
				// $this->updateShelfType($inventory); // Removed
				$tile->clearSpawnCompoundCache();
				$tile->setDirty();
				return true;
			}

			try{ Server::getInstance()->getLogger()->info("Shelf:place blocked - slot={$slot} occupied and not stackable"); }catch(\Throwable){}
			return false;
		}

		// If powered: swap contents with the player's inventory across connected shelves
		$shelves = $this->getConnectedBlocks();
		for($i = 0; $i < count($shelves); $i++){
			$s = $shelves[$i];
			$tileEntity = $s->position->getWorld()->getTile($s->position);
			if(!$tileEntity instanceof TileShelf) continue;
			$inv = $tileEntity->getInventory();
			for($j = 0; $j < $inv->getSize(); $j++){
				$shelfItem = $inv->getItem($j);
				$playerSlot = ($i * $inv->getSize()) + $j;
				$playerItem = $player->getInventory()->getItem($playerSlot);
				$inv->setItem($j, $playerItem);
				$player->getInventory()->setItem($playerSlot, $shelfItem);
			}
			$tileEntity->setDirty();
		}
		return true;
	}

	public function onNearbyBlockChange() : void{
		$this->updateConnection($this);
		$this->setPowered($this->isGettingPower());
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileShelf){
			$tile->clearSpawnCompoundCache();
			$tile->setDirty();
		}
	}

	private function isGettingPower() : bool{
		// Basic heuristic: any adjacent PoweredByRedstone block that is powered
		foreach(Facing::HORIZONTAL as $f){
			$b = $this->getSide($f);
			if($b !== null){
					if(method_exists($b, 'isPowered')){
						$fn = [$b, 'isPowered'];
						if(is_callable($fn) && call_user_func($fn)) return true;
					}
					if(method_exists($b, 'getOutputSignalStrength')){
						$fn2 = [$b, 'getOutputSignalStrength'];
						if(is_callable($fn2) && call_user_func($fn2) > 0) return true;
					}
			}
		}
		// also check direct input from above/below
		$up = $this->getSide(Facing::UP);
		$down = $this->getSide(Facing::DOWN);
		if($up !== null && method_exists($up, 'isPowered')){
			$fn = [$up, 'isPowered'];
			if(is_callable($fn) && call_user_func($fn)) return true;
		}
		if($down !== null && method_exists($down, 'isPowered')){
			$fn = [$down, 'isPowered'];
			if(is_callable($fn) && call_user_func($fn)) return true;
		}
		return false;
	}

	public function hasComparatorInputOverride() : bool{ return true; }

	public function getComparatorInputOverride() : int{
		$tile = $this->position->getWorld()->getTile($this->position);
		if(!$tile instanceof TileShelf) return 0;
		$items = $tile->getInventory()->getContents();
		$overwrite = 0;
		foreach($items as $idx => $it) if(!$it->isNull()) $overwrite |= (1 << $idx);
		return $overwrite;
	}

	public function canConnect(Shelf $shelf) : bool{
		if(!$this->isGettingPower()) return false;
		switch($this->getType()){
			case PoweredShelfType::LEFT:
				return $this->canConnectToSide($shelf, Facing::rotateY($this->facing, true), PoweredShelfType::RIGHT);
			case PoweredShelfType::RIGHT:
				return $this->canConnectToSide($shelf, Facing::rotateY($this->facing, false), PoweredShelfType::LEFT);
			default:
				return true;
		}
	}

	private function canConnectToSide(Shelf $shelf, int $sideFace, int $expectedType) : bool{
		$sideBlock = $this->getSide($sideFace);
		if($shelf->position->equals($sideBlock->position)) return true;
		if($sideBlock instanceof Shelf){
			return $sideBlock->getType() === $expectedType;
		}
		return false;
	}

	public function updateConnection(Block $origin) : void{
		$newType = PoweredShelfType::UNCONNECTED;

		$rightFace = Facing::rotateY($this->facing, false);
		$leftFace = Facing::rotateY($this->facing, true);
		$right = $this->getSide($rightFace);
		$left = $this->getSide($leftFace);

		if($this->isGettingPower()){
			$connectRight = $right instanceof Shelf && $right->canConnect($this);
			$connectLeft = $left instanceof Shelf && $left->canConnect($this);

			if($connectLeft && !$connectRight) $newType = PoweredShelfType::LEFT;
			elseif(!$connectLeft && $connectRight) $newType = PoweredShelfType::RIGHT;
			elseif($connectLeft) $newType = $this->determineCenterType($left, $right);
		}

		if($newType !== $this->getType()){
			$this->setShelfType($newType);
			$tile = $this->position->getWorld()->getTile($this->position);
			if($tile instanceof TileShelf){
				$tile->clearSpawnCompoundCache();
				$tile->setDirty();
			}

			// Do not call world->setBlock here to avoid recursive neighbor updates causing server freeze.
			// Neighbor connections will be updated via their own nearby block change events.
		}
	}

	private function determineCenterType(Block $left, Block $right) : int{
		if($right instanceof Shelf && $right->getType() === PoweredShelfType::UNCONNECTED && $right->canConnect($this)) return PoweredShelfType::LEFT;
		if($left instanceof Shelf && $left->getType() === PoweredShelfType::UNCONNECTED && $left->canConnect($this)) return PoweredShelfType::RIGHT;

		$rightIsRight = $right instanceof Shelf && $right->getType() === PoweredShelfType::RIGHT;
		$leftIsLeft = $left instanceof Shelf && $left->getType() === PoweredShelfType::LEFT;
		if($rightIsRight && $leftIsLeft) return PoweredShelfType::RIGHT;

		$rightIsCenter = $right instanceof Shelf && $right->getType() === PoweredShelfType::CENTER;
		$leftIsCenter = $left instanceof Shelf && $left->getType() === PoweredShelfType::CENTER;
		if($rightIsCenter) return PoweredShelfType::RIGHT;
		if($leftIsCenter) return PoweredShelfType::LEFT;

		return PoweredShelfType::CENTER;
	}

	protected function recalculateCollisionBoxes() : array{
		// Shelves are decorative and thin; return no collision boxes to avoid players
		// getting stuck or taking damage when multiple shelves are placed near each other.
		return [];
	}

	protected function getConnectedBlocks() : array{
		if($this->getType() === PoweredShelfType::UNCONNECTED || !$this->isGettingPower()){
			return [$this];
		}
		$shelves = [];
		$rightFace = Facing::rotateY($this->facing, false);
		$leftFace = Facing::rotateY($this->facing, true);
		$right = $this->getSide($rightFace);
		$left = $this->getSide($leftFace);
		switch($this->getType()){
			case PoweredShelfType::CENTER:
				if($right instanceof Shelf) $shelves[] = $right;
				$shelves[] = $this;
				if($left instanceof Shelf) $shelves[] = $left;
				break;
			case PoweredShelfType::RIGHT:
				$shelves[] = $this;
				if($right instanceof Shelf){
					$shelves[] = $right;
					if($right->getType() === PoweredShelfType::CENTER){
						$right1 = $this->getSide($rightFace, 2);
						if($right1 instanceof Shelf) $shelves[] = $right1;
					}
				}
				$shelves = array_reverse($shelves);
				break;
			case PoweredShelfType::LEFT:
				$shelves[] = $this;
				if($left instanceof Shelf){
					$shelves[] = $left;
					if($left->getType() === PoweredShelfType::CENTER){
						$left1 = $this->getSide($leftFace, 2);
						if($left1 instanceof Shelf) $shelves[] = $left1;
					}
				}
				break;
		}
		return $shelves;
	}

	public function getType() : int{ return $this->poweredShelfType; }

	}

	final class PoweredShelfType{
		public const UNCONNECTED = 0;
		public const RIGHT = 1;
		public const CENTER = 2;
		public const LEFT = 3;
	}