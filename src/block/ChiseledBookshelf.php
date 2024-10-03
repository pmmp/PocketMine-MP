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

use pocketmine\block\tile\ChiseledBookshelf as TileChiseledBookshelf;
use pocketmine\block\utils\ChiseledBookshelfSlot;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Book;
use pocketmine\item\EnchantedBook;
use pocketmine\item\Item;
use pocketmine\item\WritableBookBase;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function spl_object_id;

class ChiseledBookshelf extends Opaque{
	use HorizontalFacingTrait;
	use FacesOppositePlacingPlayerTrait;

	/**
	 * @var ChiseledBookshelfSlot[]
	 * @phpstan-var array<int, ChiseledBookshelfSlot>
	 */
	private array $slots = [];

	private ?ChiseledBookshelfSlot $lastInteractedSlot = null;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->enumSet($this->slots, ChiseledBookshelfSlot::cases());
	}

	public function readStateFromWorld() : Block{
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileChiseledBookshelf){
			$this->lastInteractedSlot = $tile->getLastInteractedSlot();
		}else{
			$this->lastInteractedSlot = null;
		}
		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();

		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileChiseledBookshelf){
			$tile->setLastInteractedSlot($this->lastInteractedSlot);
		}
	}

	/**
	 * Returns whether the given slot is displayed as occupied.
	 * This doesn't guarantee that there is or isn't a book in the bookshelf's inventory.
	 */
	public function hasSlot(ChiseledBookshelfSlot $slot) : bool{
		return isset($this->slots[spl_object_id($slot)]);
	}

	/**
	 * Sets whether the given slot is displayed as occupied.
	 *
	 * This doesn't modify the bookshelf's inventory, so you can use this to make invisible
	 * books or display books that aren't actually in the bookshelf.
	 *
	 * To modify the contents of the bookshelf inventory, access the tile inventory.
	 *
	 * @return $this
	 */
	public function setSlot(ChiseledBookshelfSlot $slot, bool $occupied) : self{
		if($occupied){
			$this->slots[spl_object_id($slot)] = $slot;
		}else{
			unset($this->slots[spl_object_id($slot)]);
		}
		return $this;
	}

	/**
	 * Returns which slots of the bookshelf are displayed as occupied.
	 * As above, these values do not necessarily reflect the contents of the bookshelf inventory,
	 * although they usually will unless modified by plugins.
	 *
	 * @return ChiseledBookshelfSlot[]
	 * @phpstan-return array<int, ChiseledBookshelfSlot>
	 */
	public function getSlots() : array{
		return $this->slots;
	}

	/**
	 * Returns the last slot interacted by a player or null if no slot has been interacted with yet.
	 */
	public function getLastInteractedSlot() : ?ChiseledBookshelfSlot{
		return $this->lastInteractedSlot;
	}

	/**
	 * Sets the last slot interacted by a player.
	 *
	 * @return $this
	 */
	public function setLastInteractedSlot(?ChiseledBookshelfSlot $lastInteractedSlot) : self{
		$this->lastInteractedSlot = $lastInteractedSlot;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($face !== $this->facing){
			return false;
		}

		$x = Facing::axis($face) === Axis::X ? $clickVector->z : $clickVector->x;
		$slot = ChiseledBookshelfSlot::fromBlockFaceCoordinates(
			Facing::isPositive(Facing::rotateY($face, true)) ? 1 - $x : $x,
			$clickVector->y
		);
		$tile = $this->position->getWorld()->getTile($this->position);
		if(!$tile instanceof TileChiseledBookshelf){
			return false;
		}

		$inventory = $tile->getInventory();
		if(!$inventory->isSlotEmpty($slot->value)){
			$returnedItems[] = $inventory->getItem($slot->value);
			$inventory->clear($slot->value);
			$this->setSlot($slot, false);
			$this->lastInteractedSlot = $slot;
		}elseif($item instanceof WritableBookBase || $item instanceof Book || $item instanceof EnchantedBook){
			//TODO: type tags like blocks would be better for this
			$inventory->setItem($slot->value, $item->pop());
			$this->setSlot($slot, true);
			$this->lastInteractedSlot = $slot;
		}else{
			return true;
		}

		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}
}
