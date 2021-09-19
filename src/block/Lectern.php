<?php

namespace pocketmine\block;

use pocketmine\block\tile\Lectern as TileLectern;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\NormalHorizontalFacingInMetadataTrait;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Facing;
use pocketmine\item\RottenFlesh;
use pocketmine\item\WritableBookBase;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Lectern extends Transparent {
	use FacesOppositePlacingPlayerTrait;
	use HorizontalFacingTrait;

	protected bool $hasBook = false;
	protected int $page = 0;
	protected int $totalPages = 0;
	protected ?Item $book = null;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readLegacyHorizontalFacing($stateMeta & 0x03);
	}

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::writeLegacyHorizontalFacing($this->facing);
	}

	public function getStateBitmask() : int{
		return 0b11;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);

		if($tile instanceof TileLectern) {

			$this->book = $tile->getBook();
			$this->hasBook = $tile->hasBook();
			$this->page = $tile->getPage();
			$this->totalPages = $tile->GetTotalPages();

		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileLectern){
			$tile->setBook($this->book);
			$tile->setPage($this->page);
			$tile->setTotalPages($this->totalPages);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		parent::onInteract($item, $face, $clickVector, $player);

		if($this->book->isNull() && !$item->isNull()) {
			$this->book = $item->pop();
			$this->position->getWorld()->setBlock($this->position, $this);

		}

		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		if($this->book !== ItemFactory::air() && $this->hasBook)
		{
			$tile = $this->position->getWorld()->getTile($this->position);
			$droppedBook = new ItemEntity(Location::fromObject($this->position, $this->position->getWorld(), 0, 0), $this->book);
			$droppedBook->spawnToAll();

			$tile->SetBook(ItemFactory::air());
			$tile->SetPage(0);
			$tile->setTotalPages(0);
			$this->readStateFromWorld();
			$this->position->getWorld()->setBlock($this->position, $this);
		}
		return parent::onAttack($item, $face, $player);
	}

	public function getMeta() : int{
		return parent::getMeta();
	}

}