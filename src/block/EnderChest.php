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
use pocketmine\block\tile\EnderChest as TileEnderChest;
use pocketmine\block\utils\AnimatedContainerLike;
use pocketmine\block\utils\AnimatedContainerLikeTrait;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\MenuAccessorTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\EnderChestCloseSound;
use pocketmine\world\sound\EnderChestOpenSound;
use pocketmine\world\sound\Sound;

class EnderChest extends Transparent implements AnimatedContainerLike, HorizontalFacing{
	use AnimatedContainerLikeTrait {
		onViewerAdded as private traitOnViewerAdded;
		onViewerRemoved as private traitOnViewerRemoved;
	}
	use MenuAccessorTrait;
	use FacesOppositePlacingPlayerTrait;

	public function getLightLevel() : int{
		return 7;
	}

	protected function recalculateCollisionBoxes() : array{
		//these are slightly bigger than in PC
		return [AxisAlignedBB::one()->contractedCopy(0.025, 0, 0.025)->trimmedCopy(Facing::UP, 0.05)];
	}

	public function getSupportType(Facing $facing) : SupportType{
		return SupportType::NONE;
	}

	public function isOpeningObstructed() : bool{
		return !$this->getSide(Facing::UP)->isTransparent();
	}

	protected function newMenu(Player $player, Position $position) : BlockInventoryWindow{
		return new BlockInventoryWindow($player, $player->getEnderInventory(), $position);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaBlocks::OBSIDIAN()->asItem()->setCount(8)
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	protected function getViewerCount() : int{
		$enderChest = $this->position->getWorld()->getTile($this->position);
		if(!$enderChest instanceof TileEnderChest){
			return 0;
		}
		return $enderChest->getViewerCount();
	}

	private function updateViewerCount(int $amount) : void{
		$enderChest = $this->position->getWorld()->getTile($this->position);
		if($enderChest instanceof TileEnderChest){
			$enderChest->setViewerCount($enderChest->getViewerCount() + $amount);
		}
	}

	protected function getOpenSound() : Sound{
		return new EnderChestOpenSound();
	}

	protected function getCloseSound() : Sound{
		return new EnderChestCloseSound();
	}

	protected function playAnimationVisual(Position $position, bool $isOpen) : void{
		//event ID is always 1 for a chest
		//TODO: we probably shouldn't be sending a packet directly here, but it doesn't fit anywhere into existing systems
		$position->getWorld()->broadcastPacketToViewers($position, BlockEventPacket::create(BlockPosition::fromVector3($position), 1, $isOpen ? 1 : 0));
	}

	public function onViewerAdded() : void{
		$this->updateViewerCount(1);
		$this->traitOnViewerAdded();
	}

	public function onViewerRemoved() : void{
		$this->traitOnViewerRemoved();
		$this->updateViewerCount(-1);
	}
}
