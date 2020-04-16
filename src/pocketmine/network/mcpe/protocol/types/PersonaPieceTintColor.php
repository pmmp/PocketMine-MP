<?php
declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

final class PersonaPieceTintColor{

	public const PIECE_TYPE_PERSONA_EYES = "persona_eyes";
	public const PIECE_TYPE_PERSONA_HAIR = "persona_hair";
	public const PIECE_TYPE_PERSONA_MOUTH = "persona_mouth";

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