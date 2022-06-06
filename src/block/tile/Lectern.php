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
use pocketmine\item\WritableBookBase;
use pocketmine\nbt\tag\CompoundTag;
use function count;

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
	private ?WritableBookBase $book = null;

	public function readSaveData(CompoundTag $nbt) : void{
		$this->viewedPage = $nbt->getInt(self::TAG_PAGE, 0);
		if(($itemTag = $nbt->getCompoundTag(self::TAG_BOOK)) !== null){
			$book = Item::nbtDeserialize($itemTag);
			if($book instanceof WritableBookBase && !$book->isNull()){
				$this->book = $book;
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_HAS_BOOK, $this->book !== null ? 1 : 0);
		$nbt->setInt(self::TAG_PAGE, $this->viewedPage);
		if($this->book !== null){
			$nbt->setTag(self::TAG_BOOK, $this->book->nbtSerialize());
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
		return $this->book !== null ? clone $this->book : null;
	}

	public function setBook(?WritableBookBase $book) : void{
		$this->book = $book !== null && !$book->isNull() ? clone $book : null;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_HAS_BOOK, $this->book !== null ? 1 : 0);
		$nbt->setInt(self::TAG_PAGE, $this->viewedPage);
		if($this->book !== null){
			$nbt->setTag(self::TAG_BOOK, $this->book->nbtSerialize());
			$nbt->setInt(self::TAG_TOTAL_PAGES, count($this->book->getPages()));
		}
	}
}
