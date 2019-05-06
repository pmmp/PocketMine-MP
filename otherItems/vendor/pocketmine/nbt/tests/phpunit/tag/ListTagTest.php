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

namespace pocketmine\nbt\tag;

use PHPUnit\Framework\TestCase;
use pocketmine\nbt\NBT;

class ListTagTest extends TestCase{

	public function testConstructorValues() : void{
		$array = [];

		for($i = 0; $i < 5; ++$i){
			$array[] = new StringTag("", "test$i");
		}

		$list = new ListTag("jelly beans", $array);

		self::assertEquals("jelly beans", $list->getName());
		self::assertEquals(NBT::TAG_String, $list->getTagType());
		self::assertCount(5, $list);
	}

	/**
	 * Lists of TAG_End will have their type auto-detected when something is inserted
	 * @throws \Exception
	 */
	public function testTypeDetection() : void{
		$list = new ListTag("test", [], NBT::TAG_End);
		$list->push(new StringTag("", "works"));

		self::assertEquals(NBT::TAG_String, $list->getTagType(), "Adding a tag to an empty list of TAG_End type should change its type");
	}

	/**
	 * Lists with a pre-set type can't have other tag types added to them
	 */
	public function testAddWrongTypeEmptyList() : void{
		$this->expectException(\TypeError::class);

		$list = new ListTag("test2", [], NBT::TAG_Compound);
		$list->push(new StringTag("", "shouldn't work"));
	}

	/**
	 * Empty lists can have their tag changed manually, no matter what type they are
	 */
	public function testSetEmptyListType() : void{
		$list = new ListTag("test3", [], NBT::TAG_String);

		$list->setTagType(NBT::TAG_Compound);
		$list->push(new CompoundTag());
		self::assertCount(1, $list);

		$list->shift(); //empty the list

		//once it's empty, we can set its type again
		$list->setTagType(NBT::TAG_Byte);
		$list->push(new ByteTag("", 0));
		self::assertCount(1, $list);
	}

	/**
	 * Non-empty lists should not be able to have their types changed
	 */
	public function testSetNotEmptyListType() : void{
		$this->expectException(\LogicException::class);

		$list = new ListTag("test4");
		$list->push(new StringTag("", "string"));

		$list->setTagType(NBT::TAG_Compound);
	}


	/**
	 * Cloning a list should clone all of its children
	 *
	 * @throws \Exception
	 */
	public function testClone() : void{
		$tag = new ListTag();
		for($i = 0; $i < 5; ++$i){
			$tag->push(new StringTag("", "hi"));
		}

		$tag2 = clone $tag;
		self::assertEquals($tag->getCount(), $tag2->getCount());

		foreach($tag2 as $index => $child){
			self::assertNotSame($child, $tag->get($index));
		}
	}

	/**
	 * Cloning a tag with a cyclic dependency should throw an exception
	 */
	public function testRecursiveClone() : void{
		//create recursive dependency
		$tag = new ListTag();
		$child = new ListTag();
		$child->push($tag);
		$tag->push($child);

		$this->expectException(\RuntimeException::class);
		clone $tag; //recursive dependency, throw exception
	}

	/**
	 * Tags should be able to be added to lists using the $list[] = $tag syntax
	 *
	 * @throws \Exception
	 */
	public function testArrayPushTag() : void{
		$list = new ListTag();

		$array = [];
		for($i = 0; $i < 5; ++$i){
			$child = new StringTag("", "test");
			$list[] = $child;
			$array[] = $child;
		}

		foreach($array as $i => $tag){
			self::assertSame($tag, $list->get($i));
		}
	}

	/**
	 * Non-NamedTag values cannot be assigned by array-access any more
	 */
	public function testArrayPushPrimitiveValue() : void{
		$this->expectException(\TypeError::class);

		$list = new ListTag("", [], NBT::TAG_String);
		$list[] = "hello";
	}

	/**
	 * Verify that $list[$offset] = $tag works on offsets that do exist
	 * @throws \Exception
	 */
	public function testOffsetSetTag() : void{
		$list = new ListTag("", [
			new StringTag("", "hello"),
			new StringTag("", "world")
		], NBT::TAG_String);

		$list[0] = new StringTag("", "thinking");
		$list[1] = new StringTag("", "harder");

		self::assertEquals("thinking", $list->get(0)->getValue());
		self::assertEquals("harder", $list->get(1)->getValue());
	}

	/**
	 * Non-NamedTag values cannot be assigned by array-access any more
	 * @throws \Exception
	 */
	public function testOffsetSetPrimitive() : void{
		$this->expectException(\TypeError::class);
		$list = new ListTag("", [
			new StringTag("", "hello"),
			new StringTag("", "world")
		], NBT::TAG_String);

		$list[0] = "thinking";
	}

	/**
	 * Non-NamedTag values cannot be assigned by array-access any more
	 */
	public function testOffsetSetPrimitiveNoTag() : void{
		$this->expectException(\TypeError::class);

		$list = new ListTag("", [], NBT::TAG_String);
		$list[0] = "hello";
	}
}
