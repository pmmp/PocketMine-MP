<?php

namespace pocketmine\block\tile;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class Lectern extends Spawnable {
	public const TAG_HAS_BOOK = "hasBook";
	public const TAG_PAGE = "page";
	public const TAG_TOTAL_PAGES = "totalPages";
	public const TAG_BOOK = "book";

	/** @var Boolean */
	private $hasBook = false;
	private $page = 0;
	private $totalPages = 0;
	private $book;

	public function __construct(World $world, Vector3 $pos){
		$this->book = ItemFactory::air();
		parent::__construct($world, $pos);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_HAS_BOOK, $this->hasBook ? 1 : 0);
		$nbt->setint(self::TAG_PAGE, $this->page);
		$nbt->setint(self::TAG_TOTAL_PAGES, $this->totalPages);
		$nbt->setTag(self::TAG_BOOK, $this->book->nbtSerialize());

	}

	public function readSaveData(CompoundTag $nbt) : void{
		if(($itemTag = $nbt->getCompoundTag(self::TAG_BOOK)) !== null){

			$this->book = Item::nbtDeserialize($itemTag);
		}
		$this->hasBook = $nbt->getByte(self::TAG_HAS_BOOK, 0) !== 0;
		$this->page = $nbt->getInt(self::TAG_PAGE, 0);
		$this->totalPages = $nbt->getint(self::TAG_TOTAL_PAGES, 0);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_HAS_BOOK, $this->hasBook ? 1 : 0);
		$nbt->setInt(self::TAG_PAGE, $this->page);
		$nbt->setInt(self::TAG_TOTAL_PAGES, $this->totalPages);
		$nbt->setTag(self::TAG_BOOK, $this->book->nbtSerialize());
	}

	public function hasBook() : bool {
		//$this->position->getWorld()->getLogger()->info(strval($this->book != ItemFactory::air()));
		return $this->hasBook;
	}


	public function getBook() : Item{
		return clone $this->book;
	}

	public function setBook(?Item $item) : void{
		if($item !== null and !$item->isNull()){
			$this->hasBook = true;
			$this->book = clone $item;
		}else{
			$this->hasBook = false;
			$this->book = ItemFactory::air();
		}
	}

	public function getPage() : int {
		return $this->page;
	}

	public function setPage(int $page): void {
		$this->page = $page;
	}

	public function getTotalPages(): int {
		return $this->totalPages;
	}

	public function setTotalPages(int $totalPages): void {
		$this->totalPages = $totalPages;
	}

}