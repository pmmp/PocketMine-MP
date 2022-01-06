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

namespace pocketmine\block\tile;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\item\WritableBookBase;
use pocketmine\math\Vector3;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

/**
 * @deprecated
 * @see \pocketmine\block\Lectern
 */
class Lectern extends Spawnable{
	public const TAG_HAS_BOOK = "hasBook";
	public const TAG_PAGE = "page";
	public const TAG_TOTAL_PAGES = "totalPages";
	public const TAG_BOOK = "book";

	private int $viewedPage = 0;
	private Item $book;

	public function __construct(World $world, Vector3 $pos){
		$this->book = VanillaItems::AIR();
		parent::__construct($world, $pos);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->viewedPage = $nbt->getInt(self::TAG_PAGE, 0);
		if(($itemTag = $nbt->getCompoundTag(self::TAG_BOOK)) !== null){
			$this->book = Item::nbtDeserialize($itemTag);
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_HAS_BOOK, !$this->book->isNull() ? 1 : 0);
		$nbt->setTag(self::TAG_BOOK, $this->book->nbtSerialize());
		$nbt->setInt(self::TAG_PAGE, $this->viewedPage);
		if($this->book instanceof WritableBookBase){
			$nbt->setInt(self::TAG_TOTAL_PAGES, count($this->book->getPages()));
		}
	}

	public function getViewedPage() : int{
		return $this->viewedPage;
	}

	public function setViewedPage(int $viewedPage) : void{
		$this->viewedPage = $viewedPage;
	}

	public function getBook() : ?WritableBookBase{
		return $this->book instanceof WritableBookBase && !$this->book->isNull() ? $this->book : null;
	}

	public function setBook(?WritableBookBase $book) : void{
		if(!$book instanceof WritableBookBase or $book->isNull()){
			$book = VanillaItems::AIR();
		}
		$this->book = $book;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_HAS_BOOK, !$this->book->isNull() ? 1 : 0);
		$nbt->setInt(self::TAG_PAGE, $this->viewedPage);
		$nbt->setTag(self::TAG_BOOK, $this->book->nbtSerialize());
		if($this->book instanceof WritableBookBase){
			$nbt->setInt(self::TAG_TOTAL_PAGES, count($this->book->getPages()));
		}
	}
}