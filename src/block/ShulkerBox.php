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
use pocketmine\block\tile\ShulkerBox as TileShulkerBox;
use pocketmine\block\utils\AnimatedContainerLike;
use pocketmine\block\utils\AnimatedContainerLikeTrait;
use pocketmine\block\utils\AnyFacing;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\Container;
use pocketmine\block\utils\ContainerTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\player\InventoryWindow;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\Position;
use pocketmine\world\sound\ShulkerBoxCloseSound;
use pocketmine\world\sound\ShulkerBoxOpenSound;
use pocketmine\world\sound\Sound;

class ShulkerBox extends Opaque implements AnimatedContainerLike, AnyFacing, Container{
	use AnimatedContainerLikeTrait;
	use AnyFacingTrait;
	use ContainerTrait;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		//NOOP - we don't read or write facing here, because the tile persists it
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$shulker = $this->position->getWorld()->getTile($this->position);
		if($shulker instanceof TileShulkerBox){
			$shulker->setFacing($this->facing);
		}
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$shulker = $this->position->getWorld()->getTile($this->position);
		if($shulker instanceof TileShulkerBox){
			$this->facing = $shulker->getFacing();
		}

		return $this;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, Facing $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->facing = $face;

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	private function addDataFromTile(TileShulkerBox $tile, Item $item) : void{
		$shulkerNBT = $tile->getCleanedNBT();
		if($shulkerNBT !== null){
			$item->setNamedTag($shulkerNBT);
		}
		if($tile->hasName()){
			$item->setCustomName($tile->getName());
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drop = $this->asItem();
		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof TileShulkerBox){
			$this->addDataFromTile($tile, $drop);
		}
		return [$drop];
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		$result = parent::getPickedItem($addUserData);
		if($addUserData && ($tile = $this->position->getWorld()->getTile($this->position)) instanceof TileShulkerBox){
			$this->addDataFromTile($tile, $result);
		}
		return $result;
	}

	public function isOpeningObstructed() : bool{
		return $this->getSide($this->facing)->isSolid();
	}

	protected function newMenu(Player $player, Inventory $inventory, Position $position) : InventoryWindow{
		return new BlockInventoryWindow($player, $inventory, $position);
	}

	public function getSupportType(Facing $facing) : SupportType{
		return SupportType::NONE;
	}

	protected function getOpenSound() : Sound{
		return new ShulkerBoxOpenSound();
	}

	protected function getCloseSound() : Sound{
		return new ShulkerBoxCloseSound();
	}

	protected function playAnimationVisual(Position $position, bool $isOpen) : void{
		//event ID is always 1 for a chest
		//TODO: we probably shouldn't be sending a packet directly here, but it doesn't fit anywhere into existing systems
		$position->getWorld()->broadcastPacketToViewers($position, BlockEventPacket::create(BlockPosition::fromVector3($position), 1, $isOpen ? 1 : 0));
	}
}
