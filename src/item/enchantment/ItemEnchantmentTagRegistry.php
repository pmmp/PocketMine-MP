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
use function array_shift;
use function array_unique;
use function count;

/**
 * Manages known item enchantment tags and the relations between them.
 * Used to determine which tags belong to which other tags, and to check if lists of tags intersect.
 */
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
		$this->register(Tags::BLOCK_TOOLS, [Tags::AXE, Tags::PICKAXE, Tags::SHOVEL, Tags::HOE]);
		$this->register(Tags::FISHING_ROD);
		$this->register(Tags::CARROT_ON_STICK);
		$this->register(Tags::COMPASS);
		$this->register(Tags::MASK);
		$this->register(Tags::ELYTRA);
		$this->register(Tags::BRUSH);
		$this->register(Tags::WEAPONS, [
			Tags::SWORD,
			Tags::TRIDENT,
			Tags::BOW,
			Tags::CROSSBOW,
			Tags::BLOCK_TOOLS,
		]);
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
		if(!isset($this->tagMap[$tag])){
			return;
		}
		$this->assertNotInternalTag($tag);

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
	 * @param string[] $firstTags
	 * @param string[] $secondTags
	 */
	public function isTagArrayIntersection(array $firstTags, array $secondTags) : bool{
		if(count($firstTags) === 0 || count($secondTags) === 0){
			return false;
		}

		$firstLeafTags = $this->getLeafTagsForArray($firstTags);
		$secondLeafTags = $this->getLeafTagsForArray($secondTags);

		return count(array_intersect($firstLeafTags, $secondLeafTags)) !== 0;
	}

	/**
	 * Returns all tags that are recursively nested within each tag in the array and do not have any nested tags.
	 *
	 * @param string[] $tags
	 *
	 * @return string[]
	 */
	private function getLeafTagsForArray(array $tags) : array{
		$leafTagArrays = [];
		foreach($tags as $tag){
			$leafTagArrays[] = $this->getLeafTags($tag);
		}
		return array_unique(array_merge(...$leafTagArrays));
	}

	/**
	 * Returns all tags that are recursively nested within the given tag and do not have any nested tags.
	 *
	 * @return string[]
	 */
	private function getLeafTags(string $tag) : array{
		$result = [];
		$tagsToHandle = [$tag];

		while(count($tagsToHandle) !== 0){
			$currentTag = array_shift($tagsToHandle);
			$nestedTags = $this->getNested($currentTag);

			if(count($nestedTags) === 0){
				$result[] = $currentTag;
			}else{
				$tagsToHandle = array_merge($tagsToHandle, $nestedTags);
			}
		}

		return $result;
	}

	private function assertNotInternalTag(string $tag) : void{
		if($tag === Tags::ALL){
			throw new \InvalidArgumentException(
				"Cannot perform any operations on the internal item enchantment tag '$tag'"
			);
		}
	}
}
