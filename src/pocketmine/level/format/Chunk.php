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

namespace pocketmine\level\format;

interface Chunk extends FullChunk{
	const SECTION_COUNT = 8;

	/**
	 * Tests whether a section (mini-chunk) is empty
	 *
	 * @param $fY 0-7, (Y / 16)
	 *
	 * @return bool
	 */
	public function isSectionEmpty($fY);

	/**
	 * @param int $fY 0-7
	 *
	 * @return ChunkSection
	 */
	public function getSection($fY);

	/**
	 * @param int          $fY 0-7
	 * @param ChunkSection $section
	 *
	 * @return boolean
	 */
	public function setSection($fY, ChunkSection $section);

	/**
	 * @return ChunkSection[]
	 */
	public function getSections();

}