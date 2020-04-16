<?php
declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

class PersonaPieceTintColor{

	/** @var string */
	private $pieceType;
	/** @var string[] */
	private $colors;

	/**
	 * @param string[] $colors
	 */
	public function __construct(string $pieceType, array $colors){
		$this->pieceType = $pieceType;
		$this->colors = $colors;
	}

	public function getPieceType() : string{
		return $this->pieceType;
	}

	/**
	 * @return string[]
	 */
	public function getColors(): array{
		return $this->colors;
	}
}