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

namespace pocketmine\item;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use function array_push;
use function array_slice;
use function array_values;
use function count;
use function mb_scrub;

abstract class WritableBookBase extends Item{
	public const TAG_PAGES = "pages"; //TAG_List<TAG_Compound>
	public const TAG_PAGE_TEXT = "text"; //TAG_String
	public const TAG_PAGE_PHOTONAME = "photoname"; //TAG_String - TODO

	/**
	 * @var WritableBookPage[]
	 * @phpstan-var list<WritableBookPage>
	 */
	private array $pages = [];

	public function __construct(ItemIdentifier $identifier, string $name){
		parent::__construct($identifier, $name);
	}

	/**
	 * Returns whether the given page exists in this book.
	 */
	public function pageExists(int $pageId) : bool{
		return isset($this->pages[$pageId]);
	}

	/**
	 * Returns a string containing the content of a page (which could be empty), or null if the page doesn't exist.
	 *
	 * @throws \OutOfRangeException if requesting a nonexisting page
	 */
	public function getPageText(int $pageId) : string{
		return $this->pages[$pageId]->getText();
	}

	/**
	 * Sets the text of a page in the book. Adds the page if the page does not yet exist.
	 *
	 * @return $this
	 */
	public function setPageText(int $pageId, string $pageText) : self{
		if(!$this->pageExists($pageId)){
			$this->addPage($pageId);
		}

		$this->pages[$pageId] = new WritableBookPage($pageText);
		return $this;
	}

	/**
	 * Adds a new page with the given page ID.
	 * Creates a new page for every page between the given ID and existing pages that doesn't yet exist.
	 *
	 * @return $this
	 */
	public function addPage(int $pageId) : self{
		if($pageId < 0){
			throw new \InvalidArgumentException("Page number \"$pageId\" is out of range");
		}

		for($current = count($this->pages); $current <= $pageId; $current++){
			$this->pages[] = new WritableBookPage("");
		}
		return $this;
	}

	/**
	 * Deletes an existing page with the given page ID.
	 *
	 * @return $this
	 */
	public function deletePage(int $pageId) : self{
		unset($this->pages[$pageId]);
		$this->pages = array_values($this->pages);
		return $this;
	}

	/**
	 * Inserts a new page with the given text and moves other pages upwards.
	 *
	 * @return $this
	 */
	public function insertPage(int $pageId, string $pageText = "") : self{
		if($pageId < 0 || $pageId > count($this->pages)){
			throw new \InvalidArgumentException("Page ID must not be negative");
		}
		$newPages = array_slice($this->pages, 0, $pageId);
		$newPages[] = new WritableBookPage($pageText);
		array_push($newPages, ...array_slice($this->pages, $pageId));
		$this->pages = $newPages;
		return $this;
	}

	/**
	 * Switches the text of two pages with each other.
	 *
	 * @return bool indicating success
	 * @throws \OutOfRangeException if either of the pages does not exist
	 */
	public function swapPages(int $pageId1, int $pageId2) : bool{
		$pageContents1 = $this->getPageText($pageId1);
		$pageContents2 = $this->getPageText($pageId2);
		$this->setPageText($pageId1, $pageContents2);
		$this->setPageText($pageId2, $pageContents1);

		return true;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	/**
	 * Returns an array containing all pages of this book.
	 *
	 * @return WritableBookPage[]
	 */
	public function getPages() : array{
		return $this->pages;
	}

	/**
	 * @param WritableBookPage[] $pages
	 *
	 * @return $this
	 */
	public function setPages(array $pages) : self{
		$this->pages = array_values($pages);
		return $this;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);
		$this->pages = [];

		$pages = $tag->getListTag(self::TAG_PAGES);
		if($pages !== null){
			if($pages->getTagType() === NBT::TAG_Compound){ //PE format
				/** @var CompoundTag $page */
				foreach($pages as $page){
					$this->pages[] = new WritableBookPage(mb_scrub($page->getString(self::TAG_PAGE_TEXT), 'UTF-8'), $page->getString(self::TAG_PAGE_PHOTONAME, ""));
				}
			}elseif($pages->getTagType() === NBT::TAG_String){ //PC format
				/** @var StringTag $page */
				foreach($pages as $page){
					$this->pages[] = new WritableBookPage(mb_scrub($page->getValue(), 'UTF-8'));
				}
			}
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);
		if(count($this->pages) > 0){
			$pages = new ListTag();
			foreach($this->pages as $page){
				$pages->push(CompoundTag::create()
					->setString(self::TAG_PAGE_TEXT, $page->getText())
					->setString(self::TAG_PAGE_PHOTONAME, $page->getPhotoName())
				);
			}
			$tag->setTag(self::TAG_PAGES, $pages);
		}else{
			$tag->removeTag(self::TAG_PAGES);
		}
	}
}
