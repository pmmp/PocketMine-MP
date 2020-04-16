<?php
declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

class PersonaSkinPiece{

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