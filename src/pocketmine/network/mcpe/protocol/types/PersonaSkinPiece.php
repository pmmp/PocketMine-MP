<?php
declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

final class PersonaSkinPiece{

	public const PIECE_TYPE_PERSONA_BODY = "persona_body";
	public const PIECE_TYPE_PERSONA_BOTTOM = "persona_bottom";
	public const PIECE_TYPE_PERSONA_EYES = "persona_eyes";
	public const PIECE_TYPE_PERSONA_FACIAL_HAIR = "persona_facial_hair";
	public const PIECE_TYPE_PERSONA_FEET = "persona_feet";
	public const PIECE_TYPE_PERSONA_HAIR = "persona_hair";
	public const PIECE_TYPE_PERSONA_MOUTH = "persona_mouth";
	public const PIECE_TYPE_PERSONA_SKELETON = "persona_skeleton";
	public const PIECE_TYPE_PERSONA_SKIN = "persona_skin";
	public const PIECE_TYPE_PERSONA_TOP = "persona_top";

	/** @var string */
	private $pieceId;
	/** @var string */
	private $pieceType;
	/** @var string */
	private $packId;
	/** @var bool */
	private $isDefaultPiece;
	/** @var string */
	private $productId;

	public function __construct(string $pieceId, string $pieceType, string $packId, bool $isDefaultPiece, string $productId){
		$this->pieceId = $pieceId;
		$this->pieceType = $pieceType;
		$this->packId = $packId;
		$this->isDefaultPiece = $isDefaultPiece;
		$this->productId = $productId;
	}

	public function getPieceId() : string{
		return $this->pieceId;
	}

	public function getPieceType() : string{
		return $this->pieceType;
	}

	public function getPackId() : string{
		return $this->packId;
	}

	public function isDefaultPiece() : bool{
		return $this->isDefaultPiece;
	}

	public function getProductId() : string{
		return $this->productId;
	}
}