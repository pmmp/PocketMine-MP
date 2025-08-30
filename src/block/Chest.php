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
use pocketmine\block\utils\Container;
use pocketmine\block\utils\ContainerTrait;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\SupportType;
use pocketmine\event\block\ChestPairEvent;
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

class Chest extends Transparent implements AnimatedContainerLike, Container, HorizontalFacing{
	use AnimatedContainerLikeTrait;
	use ContainerTrait;
	use FacesOppositePlacingPlayerTrait;

	protected function recalculateCollisionBoxes() : array{
		//these are slightly bigger than in PC
		return [AxisAlignedBB::one()->contractedCopy(0.025, 0, 0.025)->trimmedCopy(Facing::UP, 0.05)];
	}

	public function getSupportType(Facing $facing) : SupportType{
		return SupportType::NONE;
	}

	/**
	 * @phpstan-return array{bool, TileChest}|null
	 */
	private function locatePair(Position $position) : ?array{
		$world = $position->getWorld();
		$tile = $world->getTile($position);
		if($tile instanceof TileChest){
			foreach([false, true] as $clockwise){
				$side = Facing::rotateY($this->facing->toFacing(), $clockwise);
				$c = $position->getSide($side);
				$pair = $world->getTile($c);
				if($pair instanceof TileChest && $pair->isPaired() && $pair->getPair() === $tile){
					return [$clockwise, $pair];
				}
			}
		}
		return null;
	}

	public function onPostPlace() : void{
		$world = $this->position->getWorld();
		$tile = $world->getTile($this->position);
		if($tile instanceof TileChest){
			foreach([false, true] as $clockwise){
				$side = Facing::rotateY($this->facing->toFacing(), $clockwise);
				$c = $this->getSide($side);
				if($c instanceof Chest && $c->hasSameTypeId($this) && $c->facing === $this->facing){
					$pair = $world->getTile($c->position);
					if($pair instanceof TileChest && !$pair->isPaired()){
						[$left, $right] = $clockwise ? [$c, $this] : [$this, $c];
						$ev = new ChestPairEvent($left, $right);
						$ev->call();
						if(!$ev->isCancelled() && $world->getBlock($this->position)->hasSameTypeId($this) && $world->getBlock($c->position)->hasSameTypeId($c)){
							$pair->pairWith($tile);
							$tile->pairWith($pair);
							break;
						}
					}
				}
			}
		}
	}

	public function isOpeningObstructed() : bool{
		if(!$this->getSide(Facing::UP)->isTransparent()){
			return true;
		}
		[, $pair] = $this->locatePair($this->position) ?? [false, null];
		return $pair !== null && !$pair->getBlock()->getSide(Facing::UP)->isTransparent();
	}

	protected function newMenu(Player $player, Inventory $inventory, Position $position) : InventoryWindow{
		[$pairOnLeft, $pair] = $this->locatePair($position) ?? [false, null];
		if($pair === null){
			return new BlockInventoryWindow($player, $inventory, $position);
		}
		[$left, $right] = $pairOnLeft ? [$pair->getPosition(), $position] : [$position, $pair->getPosition()];

		//TODO: we should probably construct DoubleChestInventory here directly too using the same logic
		//right now it uses some weird logic in TileChest which produces incorrect results
		//however I'm not sure if this is currently possible
		return new DoubleChestInventoryWindow($player, $inventory, $left, $right);
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

		$pairInfo = $this->locatePair($this->position);
		if($pairInfo !== null){
			[, $pair] = $pairInfo;
			$this->playAnimationVisual($pair->getPosition(), $isOpen);
			$this->playAnimationSound($pair->getPosition(), $isOpen);
		}
	}
}
