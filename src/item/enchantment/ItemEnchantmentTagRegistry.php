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

namespace pocketmine\item\enchantment;

use pocketmine\item\enchantment\ItemEnchantmentTags as Tags;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use function array_diff;
use function array_intersect;
use function array_merge;
use function array_search;
use function array_unique;
use function count;

final class ItemEnchantmentTagRegistry{
	use SingletonTrait;

	/**
	 * @phpstan-var array<string, list<string>>
	 * @var string[][]
	 */
	private array $tagMap = [];

	private function __construct(){
		$this->register(Tags::ARMOR, [Tags::HELMET, Tags::CHESTPLATE, Tags::LEGGINGS, Tags::BOOTS]);
		$this->register(Tags::SHIELD);
		$this->register(Tags::SWORD);
		$this->register(Tags::TRIDENT);
		$this->register(Tags::BOW);
		$this->register(Tags::CROSSBOW);
		$this->register(Tags::SHEARS);
		$this->register(Tags::FLINT_AND_STEEL);
		$this->register(Tags::DIG_TOOLS, [Tags::AXE, Tags::PICKAXE, Tags::SHOVEL, Tags::HOE]);
		$this->register(Tags::FISHING_ROD);
		$this->register(Tags::CARROT_ON_STICK);
		$this->register(Tags::COMPASS);
		$this->register(Tags::MASK);
		$this->register(Tags::ELYTRA);
		$this->register(Tags::BRUSH);
	}

	/**
	 * Register tag and its nested tags.
	 *
	 * @param string[] $nestedTags
	 */
	public function register(string $tag, array $nestedTags = []) : void{
		$this->assertNotInternalTag($tag);

		foreach($nestedTags as $nestedTag){
			if(!isset($this->tagMap[$nestedTag])){
				$this->register($nestedTag);
			}
			$this->tagMap[$tag][] = $nestedTag;
		}

		if(!isset($this->tagMap[$tag])){
			$this->tagMap[$tag] = [];
			$this->tagMap[Tags::ALL][] = $tag;
		}
	}

	public function unregister(string $tag) : void{
		$this->assertNotInternalTag($tag);

		if(!isset($this->tagMap[$tag])){
			return;
		}

		unset($this->tagMap[$tag]);

		foreach(Utils::stringifyKeys($this->tagMap) as $key => $nestedTags){
			if(($nestedKey = array_search($tag, $nestedTags, true)) !== false){
				unset($this->tagMap[$key][$nestedKey]);
			}
		}
	}

	/**
	 * Remove specified nested tags.
	 *
	 * @param string[] $nestedTags
	 */
	public function removeNested(string $tag, array $nestedTags) : void{
		$this->assertNotInternalTag($tag);
		$this->tagMap[$tag] = array_diff($this->tagMap[$tag], $nestedTags);
	}

	/**
	 * Returns nested tags of a particular tag.
	 *
	 * @return string[]
	 */
	public function getNested(string $tag) : array{
		return $this->tagMap[$tag] ?? [];
	}

	/**
	 * Returns all tags that are recursively nested within the given tag and do not have any further
	 * nested tags beneath it.
	 *
	 * @return string[]
	 */
	public function getLeafTags(string $tag) : array{
		$result = [];
		$tagsToHandle = [$tag];

		while (!empty($tagsToHandle)) {
			$currentTag = array_shift($tagsToHandle);
			$nestedTags = $this->getNested($currentTag);

			if (count($nestedTags) === 0) {
				$result[] = $currentTag;
			} else {
				$tagsToHandle = array_merge($tagsToHandle, $nestedTags);
			}
		}

		return $result;
	}

	/**
	 * Returns whether one tag array is a subset of another tag array.
	 *
	 * @param string[] $firstTags
	 * @param string[] $secondTags
	 */
	public function isTagArraySubset(array $firstTags, array $secondTags) : bool{
		if(count($firstTags) === 0 || count($secondTags) === 0){
			return false;
		}

		$firstLeafTags = [];
		$secondLeafTags = [];

		foreach($firstTags as $tag){
			$firstLeafTags = array_unique(array_merge($firstLeafTags, $this->getLeafTags($tag)));
		}
		foreach($secondTags as $tag){
			$secondLeafTags = array_unique(array_merge($secondLeafTags, $this->getLeafTags($tag)));
		}

		$intersection = array_intersect($firstLeafTags, $secondLeafTags);

		return count(array_diff($firstLeafTags, $intersection)) === 0 ||
			count(array_diff($secondLeafTags, $intersection)) === 0;
	}

	private function assertNotInternalTag(string $tag) : void{
		if($tag === Tags::ALL){
			throw new \InvalidArgumentException(
				"Cannot perform register and unregister operations on the internal item enchantment tag '$tag'"
			);
		}
	}
}
