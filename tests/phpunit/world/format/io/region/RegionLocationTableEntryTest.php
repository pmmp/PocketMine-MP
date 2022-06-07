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

namespace pocketmine\world\format\io\region;

use PHPUnit\Framework\TestCase;
use function sprintf;

class RegionLocationTableEntryTest extends TestCase{

	/**
	 * @phpstan-return \Generator<int, array{RegionLocationTableEntry, RegionLocationTableEntry, bool}, void, void>
	 */
	public function overlapDataProvider() : \Generator{
		yield [new RegionLocationTableEntry(2, 1, 0), new RegionLocationTableEntry(2, 1, 0), true];
		yield [new RegionLocationTableEntry(2, 1, 0), new RegionLocationTableEntry(3, 1, 0), false];
		yield [new RegionLocationTableEntry(2, 2, 0), new RegionLocationTableEntry(3, 2, 0), true];
		yield [new RegionLocationTableEntry(2, 2, 0), new RegionLocationTableEntry(4, 2, 0), false];
		yield [new RegionLocationTableEntry(2, 2, 0), new RegionLocationTableEntry(2, 1, 0), true];
		yield [new RegionLocationTableEntry(2, 4, 0), new RegionLocationTableEntry(3, 1, 0), true];
	}

	/**
	 * @dataProvider overlapDataProvider
	 */
	public function testOverlap(RegionLocationTableEntry $entry1, RegionLocationTableEntry $entry2, bool $overlaps) : void{
		$stringify = function(RegionLocationTableEntry $entry) : string{
			return sprintf("entry first=%d last=%d size=%d", $entry->getFirstSector(), $entry->getLastSector(), $entry->getSectorCount());
		};
		self::assertSame($overlaps, $entry1->overlaps($entry2), $stringify($entry1) . " expected to " . ($overlaps ? "overlap" : "not overlap") . " with " . $stringify($entry2));
		self::assertSame($overlaps, $entry2->overlaps($entry1), $stringify($entry2) . " expected to " . ($overlaps ? "overlap" : "not overlap") . " with " . $stringify($entry1));
	}
}
