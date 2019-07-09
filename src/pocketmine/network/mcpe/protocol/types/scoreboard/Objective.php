<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\scoreboard;

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

	public static function create(string $objectiveName, string $displayName, DisplaySlot $displaySlot, SortOrder $sortOrder, string $criteriaName = "dummy") : self{
		return new self($displaySlot, $objectiveName, $displayName, $criteriaName, $sortOrder);
	}
}