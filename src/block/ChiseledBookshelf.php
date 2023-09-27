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
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Book;
use pocketmine\item\Item;
use pocketmine\item\WritableBookBase;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use function count;
use function is_int;
use function ksort;
use const SORT_NUMERIC;

class ChiseledBookshelf extends Opaque{
	use HorizontalFacingTrait;
	use FacesOppositePlacingPlayerTrait;

	public const SLOTS = self::SLOTS_PER_SHELF * 2;
	private const SLOTS_PER_SHELF = 3;

	/**
	 * @var (WritableBookBase|Book)[] $books
	 * @phpstan-var array<int, Book|WritableBookBase>
	 */
	private array $books = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->chiseledBookshelfSlots($this->books);
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileChiseledBookshelf){
			$this->books = $tile->getBooks();
		}
		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileChiseledBookshelf){
			$tile->setBooks($this->books);
		}
	}

	public function getBook(int $slot) : WritableBookBase|Book|null{
		return $this->books[$slot] ?? null;
	}

	public function setBook(int $slot, WritableBookBase|Book|null $item) : self{
		if($slot < 0 || $slot >= self::SLOTS){
			throw new \InvalidArgumentException("Cannot set a book in nonexisting bookshelf slot $slot");
		}
		if($item === null){
			unset($this->books[$slot]);
		}else{
			$this->books[$slot] = $item;
		}
		return $this;
	}

	/**
	 * @param (WritableBookBase|Book)[] $books
	 * @phpstan-param array<int, Book|WritableBookBase> $books
	 */
	public function setBooks(array $books) : self{
		if(count($books) > self::SLOTS){
			throw new \InvalidArgumentException("Expected at most " . self::SLOTS . " books, but have " . count($books));
		}
		foreach($books as $slot => $book){
			if(!is_int($slot) || $slot < 0 || $slot >= self::SLOTS){
				throw new \InvalidArgumentException("Cannot set a book in nonexisting bookshelf slot $slot");
			}
		}
		ksort($books, SORT_NUMERIC);
		$this->books = Utils::cloneObjectArray($books);
		return $this;
	}

	/**
	 * @return (WritableBookBase|Book)[]
	 * @phpstan-return array<int, Book|WritableBookBase>
	 */
	public function getBooks() : array{
		return Utils::cloneObjectArray($this->books);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($face !== $this->getFacing()){
			return false;
		}

		$horizontalOffset = (Facing::axis($face) === Axis::X ? $clickVector->getZ() : $clickVector->getX());
		$slot = ($clickVector->y < 0.5 ? self::SLOTS_PER_SHELF : 0) + match (true) {
				//we can't use simple maths here as the action is aligned to the 16x16 pixel grid :(
				$horizontalOffset < 6 / 16 => 0,
				$horizontalOffset < 11 / 16 => 1,
				default => 2
			};

		if(($existing = $this->getBook($slot)) !== null){
			$returnedItems[] = $existing;
			$this->setBook($slot, null);
		}elseif($item instanceof WritableBookBase || $item instanceof Book){
			$this->setBook($slot, $item->pop());
		}else{
			return true;
		}

		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return $this->books;
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function getSilkTouchDrops(Item $item) : array{
		return [$this->asItem(), ...$this->books];
	}
}
