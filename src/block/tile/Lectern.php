<?php

declare(strict_types=1);

namespace pocketmine\block\tile;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\WritableBookBase;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class Lectern extends Spawnable{
	public const TAG_HAS_BOOK = "hasBook";
	public const TAG_PAGE = "page";
	public const TAG_TOTAL_PAGES = "totalPages";
	public const TAG_BOOK = "book";

	private int $viewedPage = 0;

	private Item $book;

	public function __construct(World $world, Vector3 $pos){
		$this->book = ItemFactory::air();
		parent::__construct($world, $pos);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_HAS_BOOK, !$this->book->isNull() ? 1 : 0);
		$nbt->setInt(self::TAG_PAGE, $this->viewedPage);
		$nbt->setTag(self::TAG_BOOK, $this->book->nbtSerialize());

		if($this->book instanceof WritableBookBase){
			$nbt->setInt(self::TAG_TOTAL_PAGES, 0);
		}
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

	public function getBook() : Item{
		return clone $this->book;
	}

	public function setBook(?Item $item) : void{
		if($item !== null and !$item->isNull()){
			$this->book = clone $item;
		}else{
			$this->book = ItemFactory::air();
		}
	}

	public function getViewedPage() : int{
		return $this->viewedPage;
	}

	public function setViewedPage(int $viewedPage) : void{
		$this->viewedPage = $viewedPage;
	}

}
