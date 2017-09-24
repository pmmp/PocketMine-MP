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

	/**
	 * Returns whether the given page exists in this book.
	 *
	 * @param int $pageId
	 *
	 * @return bool
	 */
	public function pageExists(int $pageId) : bool{
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
		$namedTag = $this->getCorrectedNamedTag();

		if(!isset($namedTag->pages) or !($namedTag->pages instanceof ListTag)){
			$namedTag->pages = new ListTag("pages", []);
		}
		for($id = 0; $id <= $pageId; $id++) {
			if(!$this->pageExists($id)) {
				$namedTag->pages->{$id} = new CompoundTag("", [
					new StringTag("text", ""),
					new StringTag("photoname", "")
				]);
			}
		}

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
		$this->movePagesAboveDownwards($pageId, $namedTag);
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
		if(!isset($this->getNamedTag()->generation)) {
			return -1;
		}
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
		$namedTag = $this->getCorrectedNamedTag();

		if(isset($namedTag->generation)){
			$namedTag->generation->setValue($generation);
		} else {
			$namedTag->generation = new IntTag("generation", $generation);
		}
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the author of this book.
	 * This is not a reliable way to get the real author of the book. It can be changed in-game.
	 *
	 * @return string
	 */
	public function getAuthor() : string{
		if(!isset($this->getNamedTag()->author)){
			return "";
		}
		return $this->getNamedTag()->author->getValue();
	}

	/**
	 * Sets the author of this book.
	 *
	 * @param string $authorName
	 */
	public function setAuthor(string $authorName) : void{
		$namedTag = $this->getCorrectedNamedTag();
		if(isset($namedTag->author)){
			$namedTag->author->setValue($authorName);
		} else {
			$namedTag->author = new StringTag("author", $authorName);
		}
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the title of this book.
	 *
	 * @return string
	 */
	public function getTitle() : string{
		if(!isset($this->getNamedTag()->title)){
			return "";
		}
		return $this->getNamedTag()->title->getValue();
	}

	/**
	 * Sets the author of this book.
	 *
	 * @param string $title
	 */
	public function setTitle(string $title) : void{
		$namedTag = $this->getCorrectedNamedTag();
		if(isset($namedTag->title)){
			$namedTag->title->setValue($title);
		} else {
			$namedTag->title = new StringTag("title", $title);
		}
		$this->setNamedTag($namedTag);
	}

	/**
	 * @return CompoundTag
	 */
	public function getCorrectedNamedTag() : CompoundTag{
		return $this->getNamedTag() ?? new CompoundTag();
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	/**
	 * @param int         $id
	 * @param CompoundTag $namedTag
	 *
	 * @return bool
	 */
	private function movePagesAboveDownwards(int $id, CompoundTag $namedTag) : bool{
		if(!isset($namedTag->pages)){
			return false;
		}
		foreach($namedTag->pages as $key => $page){
			if(!is_numeric($key) or $key <= $id){
				continue;
			}
			$namedTag->pages->{$key - 1} = $page;
		}
		return true;
	}
}