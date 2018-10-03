<?php
namespace pocketmine\scoreboard;

class ScoreBoard {
	public const SORT_ASCENDING = 0;
	public const SORT_DESCENDING = 1;
	private $elements = [];
	private $sortOrder;
	private $title;
	public function __construct(string $title, int $sortOrder = ScoreBoard::SORT_ASCENDING) {
		$this->title = $title;
		$this->sortOrder = $sortOrder;
	}
	public function setTitle(string $title) : void {
		$this->title = $title;
	}
	public function getTitle() : string {
		return $this->title;
	}
	public function setSortOrder(string $title) : void {
		$this->title = $title;
	}
	public function getSortOrder() : int {
		return $this->sortOrder;
	}
	public function getElements() : array {
		return $this->elements;
	}
	public function removeElement(string $element) : void {
		unset($this->elements[$element]);
	}
	public function setScore(string $element, int $score) : void {
		$this->elements[$element] = $score;
	}
	public function getScore(string $element) : ?int {
		return $this->elements[$element] ?? null;
	}
}