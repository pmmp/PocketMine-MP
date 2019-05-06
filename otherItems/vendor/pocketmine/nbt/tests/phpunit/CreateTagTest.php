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

namespace pocketmine\nbt;

use PHPUnit\Framework\TestCase;

class CreateTagTest extends TestCase{

	/**
	 * Tests that all tags with declared constants in NBT can be created (with the exception of TAG_End)
	 *
	 * @throws \Exception
	 * @throws \ReflectionException
	 */
	public function testCreateTags() : void{
		$consts = (new \ReflectionClass(NBT::class))->getConstants();

		/**
		 * @var string $name
		 */
		foreach($consts as $name => $value){
			if(strpos($name, "TAG_") === 0 and $name !== "TAG_End" and is_int($value)){
				/** @var int $value */

				try{
					$tag = NBT::createTag($value);
					self::assertEquals($value, $tag->getType());
				}catch(\InvalidArgumentException $e){
					self::assertTrue(false, "Could not create tag of type $name");
				}
			}
		}
	}
}
