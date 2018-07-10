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

namespace pocketmine\utils;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase{

	public function parseDocCommentNewlineProvider() : array{
		return [
			["\t/**\r\n\t * @param PlayerJoinEvent \$event\r\n\t * @priority HIGHEST\r\n\t * @notHandler\r\n\t */"],
			["\t/**\n\t * @param PlayerJoinEvent \$event\n\t * @priority HIGHEST\n\t * @notHandler\n\t */"],
			["\t/**\r\t * @param PlayerJoinEvent \$event\r\t * @priority HIGHEST\r\t * @notHandler\r\t */"]
		];
	}

	/**
	 * @param string $docComment
	 * @dataProvider parseDocCommentNewlineProvider
	 */
	public function testParseDocCommentNewlines(string $docComment) : void{
		$tags = Utils::parseDocComment($docComment);

		self::assertArrayHasKey("notHandler", $tags);
		self::assertEquals("", $tags["notHandler"]);
		self::assertArrayHasKey("priority", $tags);
		self::assertEquals("HIGHEST", $tags["priority"]);
	}
}
