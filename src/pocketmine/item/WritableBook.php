<?php

declare(strict_types = 1);

namespace pocketmine\item;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class WritableBook extends Item{

	const GENERATION_ORIGINAL = 0;
	const GENERATION_COPY = 1;
	const GENERATION_COPY_OF_COPY = 2;
	const GENERATION_TATTERED = 3;

	public function __construct(int $meta = 0){
		parent::__construct(self::WRITABLE_BOOK, $meta, "Book & Quill");
	}

	public function getMaxStackSize() : int {
		return 1;
	}

	public function correctNBT() : void{
		$nbt = $this->getNamedTag() ?? new CompoundTag();
		if(!isset($nbt->pages) or !($nbt->pages instanceof ListTag)){
			$nbt->pages = new ListTag("pages");
		}
		if(!isset($nbt->generation) or !($nbt->generation instanceof IntTag)){
			$nbt->generation = new IntTag("generation", 0);
		}
		if(!isset($nbt->author) or !($nbt->author instanceof StringTag)){
			$nbt->author = new StringTag("author", "");
		}
		if(!isset($nbt->title) or !($nbt->title instanceof StringTag)){
			$nbt->title = new StringTag("title", "");
		}
		$this->setNamedTag($nbt);
	}

	/**
	 * Returns whether the given page exists in this book.
	 *
	 * @param int $pageId
	 *
	 * @return bool
	 */
	public function pageExists(int $pageId) : bool{
		$this->correctNBT();
		return isset($this->getNamedTag()->pages->{$pageId});
	}

	/**
	 * Returns a string containing the content of a page, or an empty string if the page doesn't exist.
	 *
	 * @param int $pageId
	 *
	 * @return string|null
	 */
	public function getPageText(int $pageId) : ?string{
		if(!$this->pageExists($pageId)){
			return null;
		}
		return $this->getNamedTag()->pages->{$pageId}->text->getValue();
	}

	/**
	 * Sets the text of a page in the book. Adds the page if the page does not yet exist.
	 *
	 * @param int    $pageId
	 * @param string $pageText
	 *
	 * @return bool indicating whether the page was created or not.
	 */
	public function setPageText(int $pageId, string $pageText) : bool{
		$created = false;
		if(!$this->pageExists($pageId)){
			$this->addPage($pageId);
			$created = true;
		}
		$namedTag = $this->getNamedTag();
		$namedTag->pages->{$pageId}->text->setValue($pageText);
		$this->setNamedTag($namedTag);
		return $created;
	}

	/**
	 * Adds a new page with the given text. (if given)
	 *
	 * @param int    $pageId
	 *
	 * @return int page number
	 */
	public function addPage(int $pageId) : int{
		if($pageId < -1) {
			throw new \InvalidArgumentException("Page number \"$pageId\" is out of range");
		}
		$this->correctNBT();
		$namedTag = $this->getNamedTag();
		$namedTag->pages->{$pageId} = new CompoundTag("");
		$namedTag->pages->{$pageId}->text = new StringTag("text", "");
		$this->setNamedTag($namedTag);
		return $pageId;
	}

	/**
	 * Deletes an existing page.
	 *
	 * @param int $pageId
	 *
	 * @return bool indicating success
	 */
	public function deletePage(int $pageId) : bool{
		if(!$this->pageExists($pageId)){
			return false;
		}
		$namedTag = $this->getNamedTag();
		unset($namedTag->pages->{$pageId});
		$this->setNamedTag($namedTag);
		return true;
	}

	/**
	 * Switches the text of two pages with each other.
	 *
	 * @param int $pageId1
	 * @param int $pageId2
	 *
	 * @return bool indicating success
	 */
	public function swapPage(int $pageId1, int $pageId2) : bool{
		if(!$this->pageExists($pageId1) or !$this->pageExists($pageId2)){
			return false;
		}
		$pageContents1 = $this->getPageText($pageId1);
		$pageContents2 = $this->getPageText($pageId2);
		$this->setPageText($pageId1, $pageContents2);
		$this->setPageText($pageId2, $pageContents1);
		return true;
	}

	/**
	 * Returns the generation of the book.
	 * Generations higher than 1 can not be copied.
	 *
	 * @return int
	 */
	public function getGeneration() : int{
		$this->correctNBT();
		return $this->getNamedTag()->generation->getValue();
	}

	/**
	 * Sets the generation of a book.
	 *
	 * @param int $generation
	 */
	public function setGeneration(int $generation) : void{
		if($generation < 0 or $generation > 3) {
			throw new \InvalidArgumentException("Generation \"$generation\" is out of range");
		}
		$this->correctNBT();
		$namedTag = $this->getNamedTag();
		$namedTag->generation->setValue($generation);
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the author of this book.
	 * This is not a reliable way to get the real author of the book. It can be changed in-game.
	 *
	 * @return string
	 */
	public function getAuthor() : string{
		$this->correctNBT();
		return $this->getNamedTag()->author->getValue();
	}

	/**
	 * Sets the author of this book.
	 *
	 * @param string $authorName
	 */
	public function setAuthor(string $authorName) : void{
		$this->correctNBT();
		$namedTag = $this->getNamedTag();
		$namedTag->author->setValue($authorName);
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the title of this book.
	 *
	 * @return string
	 */
	public function getTitle() : string{
		$this->correctNBT();
		return $this->getNamedTag()->title->getValue();
	}

	/**
	 * Sets the author of this book.
	 *
	 * @param string $title
	 */
	public function setTitle(string $title) : void{
		$this->correctNBT();
		$namedTag = $this->getNamedTag();
		$namedTag->title->setValue($title);
		$this->setNamedTag($namedTag);
	}
}