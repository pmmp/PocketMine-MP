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

use pocketmine\block\inventory\window\BlockInventoryWindow;
use pocketmine\block\inventory\window\DoubleChestInventoryWindow;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\utils\AnimatedContainerLike;
use pocketmine\block\utils\AnimatedContainerLikeTrait;
use pocketmine\block\utils\ChestPairHalf;
use pocketmine\block\utils\Container;
use pocketmine\block\utils\ContainerTrait;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\HorizontalFacingOption;
use pocketmine\block\utils\SupportType;
use pocketmine\event\block\ChestPairEvent;
use pocketmine\inventory\CombinedInventoryProxy;
use pocketmine\inventory\Inventory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\player\InventoryWindow;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use pocketmine\world\sound\Sound;
use function assert;

class Chest extends Transparent implements AnimatedContainerLike, Container, HorizontalFacing{
	use AnimatedContainerLikeTrait;
	use ContainerTrait;
	use FacesOppositePlacingPlayerTrait;

	protected ?ChestPairHalf $pairHalf = null;

	public function getPairHalf() : ?ChestPairHalf{ return $this->pairHalf; }

	public function setPairHalf(?ChestPairHalf $pairHalf) : self{
		$this->pairHalf = $pairHalf;
		return $this;
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);

		$this->pairHalf = null;
		if($tile instanceof TileChest && ($pairXZ = $tile->getPairXZ()) !== null){
			[$pairX, $pairZ] = $pairXZ;
			foreach(ChestPairHalf::cases() as $pairSide){
				$pairDirection = $pairSide->getOtherHalfSide($this->facing);
				$pairPosition = $this->position->getSide($pairDirection);
				if($pairPosition->getFloorX() === $pairX && $pairPosition->getFloorZ() === $pairZ){
					$this->pairHalf = $pairSide;
					break;
				}
			}
		}

		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileChest);

		//TODO: this should probably use relative coordinates instead of absolute, for portability
		if($this->pairHalf !== null){
			$pairDirection = $this->pairHalf->getOtherHalfSide($this->facing);
			$pairPosition = $this->position->getSide($pairDirection);
			$pairXZ = [$pairPosition->getFloorX(), $pairPosition->getFloorZ()];
		}else{
			$pairXZ = null;
		}
		$tile->setPairXZ($pairXZ);
	}

	protected function recalculateCollisionBoxes() : array{
		//these are slightly bigger than in PC
		$facing = $this->facing->toFacing();
		$box = AxisAlignedBB::one()
			->squashedCopy(Facing::axis($facing), 0.025)
			->trimmedCopy(Facing::UP, 0.05);
		$pairSide = $this->pairHalf?->getOtherHalfSide($this->facing);
		return [$pairSide !== null ?
			$box->trimmedCopy(Facing::opposite($pairSide), 0.025) :
			$box->squashedCopy(Facing::axis(Facing::rotateY($facing, true)), 0.025)
		];
	}

	public function getSupportType(Facing $facing) : SupportType{
		return SupportType::NONE;
	}

	private function getPossiblePair(ChestPairHalf $pairSide) : ?Chest{
		$pair = $this->getSide($pairSide->getOtherHalfSide($this->facing));
		return $pair->hasSameTypeId($this) && $pair instanceof Chest && $pair->getFacing() === $this->facing ? $pair : null;
	}

	public function getOtherHalf() : ?Chest{
		return $this->pairHalf !== null && ($pair = $this->getPossiblePair($this->pairHalf)) !== null && $pair->pairHalf === $this->pairHalf->opposite() ? $pair : null;
	}

	public function onPostPlace() : void{
		//Not sure if this vanilla behaviour is intended, but a chest facing north or west will try to pair on the left
		//side first, while a chest facing south or east will try the right side first.
		$order = match($this->facing){
			HorizontalFacingOption::NORTH, HorizontalFacingOption::WEST => [ChestPairHalf::LEFT, ChestPairHalf::RIGHT],
			HorizontalFacingOption::SOUTH, HorizontalFacingOption::EAST => [ChestPairHalf::RIGHT, ChestPairHalf::LEFT]
		};
		$world = $this->position->getWorld();
		foreach($order as $pairSide){
			$possiblePair = $this->getPossiblePair($pairSide);
			if($possiblePair !== null && $possiblePair->pairHalf === null){
				[$left, $right] = $pairSide === ChestPairHalf::LEFT ? [$this, $possiblePair] : [$possiblePair, $this];
				$ev = new ChestPairEvent($left, $right);
				if(!$ev->isCancelled() && $world->getBlock($this->position)->isSameState($this) && $world->getBlock($possiblePair->position)->isSameState($possiblePair)){
					$world->setBlock($this->position, $this->setPairHalf($pairSide));
					$world->setBlock($possiblePair->position, $possiblePair->setPairHalf($pairSide->opposite()));
					break;
				}
			}
		}
	}

	public function onNearbyBlockChange() : void{
		//TODO: If the pair chunk isn't loaded, a block update of an adjacent block in loaded terrain could cause the
		//chest to become unpaired. However, this is not unique to chests (think wall connections). Probably we
		//should defer updates in chunks whose neighbours are not loaded?
		if($this->pairHalf !== null && $this->getOtherHalf() === null){
			$this->position->getWorld()->setBlock($this->position, $this->setPairHalf(null));
		}
	}

	public function isOpeningObstructed() : bool{
		foreach([$this, $this->getOtherHalf()] as $chest){
			if($chest !== null && !$chest->getSide(Facing::UP)->isTransparent()){
				return true;
			}
		}
		return false;
	}

	protected function getTile() : ?TileChest{
		$tile = $this->position->getWorld()->getTile($this->position);
		return $tile instanceof TileChest ? $tile : null;
	}

	public function getInventory() : ?Inventory{
		$thisInventory = $this->getTile()?->getRealInventory();
		if($thisInventory === null){
			return null;
		}
		$pairInventory = $this->getOtherHalf()?->getTile()?->getRealInventory();
		if($pairInventory === null){
			return $thisInventory;
		}

		[$left, $right] = $this->pairHalf === ChestPairHalf::LEFT ? [$thisInventory, $pairInventory] : [$pairInventory, $thisInventory];
		return new CombinedInventoryProxy([$left, $right]);
	}

	protected function newMenu(Player $player, Inventory $inventory, Position $position) : InventoryWindow{
		$pair = $this->getOtherHalf();
		if($pair === null){
			return new BlockInventoryWindow($player, $inventory, $position);
		}
		[$left, $right] = $this->pairHalf === ChestPairHalf::LEFT ? [$this, $pair] : [$pair, $this];
		return new DoubleChestInventoryWindow($player, $inventory, $left->position, $right->position);
	}

	public function getFuelTime() : int{
		return 300;
	}

	protected function getOpenSound() : Sound{
		return new ChestOpenSound();
	}

	protected function getCloseSound() : Sound{
		return new ChestCloseSound();
	}

	protected function playAnimationVisual(Position $position, bool $isOpen) : void{
		//event ID is always 1 for a chest
		//TODO: we probably shouldn't be sending a packet directly here, but it doesn't fit anywhere into existing systems
		$position->getWorld()->broadcastPacketToViewers($position, BlockEventPacket::create(BlockPosition::fromVector3($position), 1, $isOpen ? 1 : 0));
	}

	protected function doAnimationEffects(bool $isOpen) : void{
		$this->playAnimationVisual($this->position, $isOpen);
		$this->playAnimationSound($this->position, $isOpen);

		$pair = $this->getOtherHalf();
		if($pair !== null){
			$this->playAnimationVisual($pair->position, $isOpen);
			$this->playAnimationSound($pair->position, $isOpen);
		}
	}
}
