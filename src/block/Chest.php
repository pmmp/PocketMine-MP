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
use pocketmine\block\utils\AnimatedContainer;
use pocketmine\block\utils\AnimatedContainerTrait;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\SupportType;
use pocketmine\event\block\ChestPairEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use pocketmine\world\sound\Sound;

class Chest extends Transparent implements AnimatedContainer, HorizontalFacing{
	use AnimatedContainerTrait;
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

	public function onInteract(Item $item, Facing $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player instanceof Player){
			$world = $this->position->getWorld();
			$chest = $world->getTile($this->position);
			if($chest instanceof TileChest){
				[$pairOnLeft, $pair] = $this->locatePair($this->position) ?? [false, null];
				if(
					!$this->getSide(Facing::UP)->isTransparent() ||
					($pair !== null && !$pair->getBlock()->getSide(Facing::UP)->isTransparent()) ||
					!$chest->canOpenWith($item->getCustomName())
				){
					return true;
				}

				if($pair !== null){
					[$left, $right] = $pairOnLeft ? [$pair->getBlock(), $this] : [$this, $pair->getBlock()];

					//TODO: we should probably construct DoubleChestInventory here directly too using the same logic
					//right now it uses some weird logic in TileChest which produces incorrect results
					//however I'm not sure if this is currently possible
					$window = new DoubleChestInventoryWindow($player, $chest->getInventory(), $left->position, $right->position);
				}

				$player->setCurrentWindow($window ?? new BlockInventoryWindow($player, $chest->getInventory(), $this->position));
			}
		}

		return true;
	}

	public function getFuelTime() : int{
		return 300;
	}

	protected function getContainerOpenSound() : Sound{
		return new ChestOpenSound();
	}

	protected function getContainerCloseSound() : Sound{
		return new ChestCloseSound();
	}

	protected function doContainerAnimation(Position $position, bool $isOpen) : void{
		//event ID is always 1 for a chest
		//TODO: we probably shouldn't be sending a packet directly here, but it doesn't fit anywhere into existing systems
		$position->getWorld()->broadcastPacketToViewers($position, BlockEventPacket::create(BlockPosition::fromVector3($position), 1, $isOpen ? 1 : 0));
	}

	protected function doContainerEffects(bool $isOpen) : void{
		$this->doContainerAnimation($this->position, $isOpen);
		$this->playContainerSound($this->position, $isOpen);

		$pairInfo = $this->locatePair($this->position);
		if($pairInfo !== null){
			[, $pair] = $pairInfo;
			$this->doContainerAnimation($pair->getPosition(), $isOpen);
			$this->playContainerSound($pair->getPosition(), $isOpen);
		}
	}
}
