<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\scoreboard;

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