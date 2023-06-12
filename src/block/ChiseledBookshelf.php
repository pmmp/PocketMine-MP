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

use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\tile\ChiseledBookshelf as TileChiseledBookshelf;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Book;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\item\WritableBookBase;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function count;

class ChiseledBookshelf extends Opaque {
	use HorizontalFacingTrait;
	use FacesOppositePlacingPlayerTrait;

	private int $booksBit = 0;

	/** @var (WritableBookBase|Book)[] $items */
	private array $items = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->int(6, $this->booksBit);
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileChiseledBookshelf){
			$this->items = $tile->getItems();
		}
		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileChiseledBookshelf){
			$tile->setItems($this->items);
		}
	}

	public function getItem(int $index) : WritableBookBase|Book|null{
		return $this->items[$index] ?? null;
	}

	public function setItem(int $index, WritableBookBase|Book|null $item) : void{
		if($item === null){
			if(isset($this->items[$index])){
				unset($this->items[$index]);
			}
			return;
		}
		$this->items[$index] = $item;
	}

	public function recalculateBooksBit() : int{
		$bit = 0;
		foreach($this->items as $index => $item){
			$bit |= 1 << $index;
		}
		return $this->booksBit = $bit;
	}

	public function setCachedBooksBit(int $booksBit) : self{
		$this->booksBit = $booksBit;
		return $this;
	}

	public function getCachedBooksBit() : int{
		return $this->booksBit ?? $this->recalculateBooksBit();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($face !== $this->getFacing()){
			return false;
		}
		$x = (Facing::axis($face) === Axis::X ? $clickVector->getZ() : $clickVector->getX());
		$x = min(match($face){
			Facing::NORTH, Facing::EAST => 1 - $x,
			default => $x
		}, 0.9);
		$index = ($clickVector->y < 0.5 ? 3 : 0) + (int) floor($x * 3);
		if(!$item instanceof WritableBookBase && !$item instanceof Book){
			if($player instanceof Player){
				$item = $this->getItem($index) ?? VanillaItems::AIR();

				$leftover = $player->getInventory()->addItem($item);
				if(count($leftover) > 0){
					$player->getWorld()->dropItem($player->getEyePos(), $leftover[0], $player->getDirectionVector()->multiply(0.2));
				}
				$this->setItem($index, null);
			}
		}else{
			 if($this->getItem($index) instanceof Item){
				 return false;
			 }
			 /** @var WritableBookBase|Book $item */
			 $this->setItem($index, $item->pop());
		}
		$this->recalculateBooksBit();
		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function getDrops(Item $item) : array{
		return ($item->hasEnchantment(VanillaEnchantments::SILK_TOUCH()) ? [$this->asItem(), ...$this->items] : $this->items);
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}
}
