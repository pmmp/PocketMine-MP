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

namespace pocketmine\scoreboard;

use function random_bytes;

class Objective{
	/** @var DisplaySlot */
	public $displaySlot;
	/** @var string */
	public $objectiveName;
	/** @var string */
	public $displayName;
	/** @var string */
	public $criteriaName;
	/** @var SortOrder */
	public $sortOrder;

	/**
	 * @internal
	 * @see Objective::create()
	 *
	 * @param DisplaySlot $displaySlot
	 * @param string      $objectiveName
	 * @param string      $displayName
	 * @param string      $criteriaName
	 * @param SortOrder   $sortOrder
	 */
	public function __construct(DisplaySlot $displaySlot, string $objectiveName, string $displayName, string $criteriaName, SortOrder $sortOrder){
		$this->displaySlot = $displaySlot;
		$this->objectiveName = $objectiveName;
		$this->displayName = $displayName;
		$this->criteriaName = $criteriaName;
		$this->sortOrder = $sortOrder;
	}

	public static function create(string $displayName, DisplaySlot $displaySlot, SortOrder $sortOrder) : self{
		//this avoid plugin conflicts and remove useless argument
		return new self($displaySlot, random_bytes(8), $displayName, "dummy", $sortOrder);
	}
}