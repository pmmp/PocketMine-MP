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

abstract class WritableBookBase extends Item{

	public const TAG_PAGES = "pages"; //TAG_List<TAG_Compound>
	public const TAG_PAGE_TEXT = "text"; //TAG_String
	public const TAG_PAGE_PHOTONAME = "photoname"; //TAG_String - TODO

	/**
	 * Returns whether the given page exists in this book.
	 *
	 * @param int $pageId
	 *
	 * @return bool
	 */
	public function pageExists(int $pageId) : bool{
		return $this->getPagesTag()->isset($pageId);
	}

	/**
	 * Returns a string containing the content of a page (which could be empty), or null if the page doesn't exist.
	 *
	 * @param int $pageId
	 *
	 * @return string|null
	 */
	public function getPageText(int $pageId) : ?string{
		$pages = $this->getNamedTag()->getListTag(self::TAG_PAGES);
		if($pages === null){
			return null;
		}

		$page = $pages->get($pageId);
		if($page instanceof CompoundTag){
			return $page->getString(self::TAG_PAGE_TEXT, "");
		}

		return null;
	}

	/**
	 * Sets the text of a page in the book. Adds the page if the page does not yet exist.
	 *
	 * @param int    $pageId
	 * @param string $pageText
	 *
	 * @return $this
	 */
	public function setPageText(int $pageId, string $pageText) : self{
		if(!$this->pageExists($pageId)){
			$this->addPage($pageId);
		}

		/** @var CompoundTag[]|ListTag $pagesTag */
		$pagesTag = $this->getPagesTag();
		/** @var CompoundTag $page */
		$page = $pagesTag->get($pageId);
		$page->setString(self::TAG_PAGE_TEXT, $pageText);

		$this->getNamedTag()->setTag(self::TAG_PAGES, $pagesTag);

		return $this;
	}

	/**
	 * Adds a new page with the given page ID.
	 * Creates a new page for every page between the given ID and existing pages that doesn't yet exist.
	 *
	 * @param int $pageId
	 *
	 * @return $this
	 */
	public function addPage(int $pageId) : self{
		if($pageId < 0){
			throw new \InvalidArgumentException("Page number \"$pageId\" is out of range");
		}

		$pagesTag = $this->getPagesTag();

		for($current = $pagesTag->count(); $current <= $pageId; $current++){
			$pagesTag->push(CompoundTag::create()
				->setString(self::TAG_PAGE_TEXT, "")
				->setString(self::TAG_PAGE_PHOTONAME, "")
			);
		}

		$this->getNamedTag()->setTag(self::TAG_PAGES, $pagesTag);
		return $this;
	}

	/**
	 * Deletes an existing page with the given page ID.
	 *
	 * @param int $pageId
	 *
	 * @return $this
	 */
	public function deletePage(int $pageId) : self{
		$pagesTag = $this->getPagesTag();
		$pagesTag->remove($pageId);

		return $this;
	}

	/**
	 * Inserts a new page with the given text and moves other pages upwards.
	 *
	 * @param int    $pageId
	 * @param string $pageText
	 *
	 * @return $this
	 */
	public function insertPage(int $pageId, string $pageText = "") : self{
		$pagesTag = $this->getPagesTag();

		$pagesTag->insert($pageId, CompoundTag::create()
			->setString(self::TAG_PAGE_TEXT, $pageText)
			->setString(self::TAG_PAGE_PHOTONAME, "")
		);

		$this->getNamedTag()->setTag(self::TAG_PAGES, $pagesTag);

		return $this;
	}

	/**
	 * Switches the text of two pages with each other.
	 *
	 * @param int $pageId1
	 * @param int $pageId2
	 *
	 * @return bool indicating success
	 */
	public function swapPages(int $pageId1, int $pageId2) : bool{
		if(!$this->pageExists($pageId1) or !$this->pageExists($pageId2)){
			return false;
		}

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
	 * @return CompoundTag[]
	 */
	public function getPages() : array{
		$pages = $this->getNamedTag()->getListTag(self::TAG_PAGES);
		if($pages === null){
			return [];
		}

		return $pages->getValue();
	}

	protected function getPagesTag() : ListTag{
		return $this->getNamedTag()->getListTag(self::TAG_PAGES) ?? new ListTag([], NBT::TAG_Compound);
	}

	/**
	 * @param CompoundTag[] $pages
	 *
	 * @return $this
	 */
	public function setPages(array $pages) : self{
		$nbt = $this->getNamedTag();
		$nbt->setTag(self::TAG_PAGES, new ListTag($pages, NBT::TAG_Compound));
		$this->setNamedTag($nbt);
		return $this;
	}
}
