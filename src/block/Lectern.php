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

namespace pocketmine\block;

use pocketmine\block\tile\Lectern as TileLectern;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\WritableBookBase;
use pocketmine\item\WritableBook;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\PredictedResult;
use pocketmine\network\mcpe\protocol\types\inventory\TriggerType;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\player\Player;
use pocketmine\world\sound\LecternPlaceBookSound;
use function count;

class Lectern extends Transparent implements HorizontalFacing{
	use FacesOppositePlacingPlayerTrait;

	protected int $viewedPage = 0;
	protected ?WritableBookBase $book = null;

	protected bool $producingSignal = false;

	private function getTileLectern() : ?TileLectern{
		$tile = $this->position->getWorld()->getTile($this->position);
		return $tile instanceof TileLectern ? $tile : null;
	}

	private function sendStateUpdate(?Player $target = null) : void{
		$world = $this->position->getWorld();
		$packets = $world->createBlockUpdatePackets([$this->position]);
		if($target !== null){
			$session = $target->getNetworkSession();
			foreach($packets as $packet){
				$session->sendDataPacket($packet);
			}
			return;
		}

		foreach($packets as $packet){
			$world->broadcastPacketToViewers($this->position, $packet);
		}
	}

	private function previewBookToPlayer(Player $player, WritableBookBase $book) : void{
		$networkSession = $player->getNetworkSession();
		$inventoryManager = $networkSession->getInvManager();
		if($inventoryManager === null){
			return;
		}

		$inventory = $player->getInventory();
		$slot = $inventory->getHeldItemIndex();
		$original = clone $inventory->getItem($slot);
		$preview = (clone $book)->setCount(1);

		// Do not modify server inventory. Instead, send a temporary slot sync to the client
		// so the client believes it's holding the book. This avoids prediction/restore races.
		$inventoryManager->syncSlot($inventory, $slot, $networkSession->getTypeConverter()->coreItemStackToNet($preview));

		$networkSession->sendDataPacket(InventoryTransactionPacket::create(
			0,
			[],
			UseItemTransactionData::new(
				[],
				UseItemTransactionData::ACTION_CLICK_AIR,
				TriggerType::PLAYER_INPUT,
				new BlockPosition(0, 0, 0),
				0,
				$slot,
				ItemStackWrapper::legacy(ItemStack::null()),
				Vector3::zero(),
				Vector3::zero(),
				0,
				PredictedResult::FAILURE
			)
		), true);

		// Restore the original slot view for the client
		$inventoryManager->syncSlot($inventory, $slot, $networkSession->getTypeConverter()->coreItemStackToNet($original));
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->bool($this->producingSignal);
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileLectern){
			$this->viewedPage = $tile->getViewedPage();
			$this->book = $tile->getBook();
		}

		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileLectern){
			$tile->setViewedPage($this->viewedPage);
			$tile->setBook($this->book);
		}
	}

	public function getFlammability() : int{
		return 30;
	}

	public function getDrops(Item $item) : array{
		$drops = parent::getDrops($item);
		if($this->book !== null){
			$drops[] = clone $this->book;
		}

		return $drops;
	}

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 0.1)];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function isProducingSignal() : bool{ return $this->producingSignal; }

	/** @return $this */
	public function setProducingSignal(bool $producingSignal) : self{
		$this->producingSignal = $producingSignal;
		return $this;
	}

	public function getViewedPage() : int{
		return $this->viewedPage;
	}

	/** @return $this */
	public function setViewedPage(int $viewedPage) : self{
		$this->viewedPage = $viewedPage;
		return $this;
	}

	public function getBook() : ?WritableBookBase{
		return $this->book !== null ? clone $this->book : null;
	}

	/** @return $this */
	public function setBook(?WritableBookBase $book) : self{
		$this->book = $book !== null && !$book->isNull() ? (clone $book)->setCount(1) : null;
		$this->viewedPage = 0;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		$this->readStateFromWorld();
		$tile = $this->getTileLectern();
		if($tile === null){
			return false;
		}

		$world = $this->position->getWorld();
		$currentBook = $tile->getBook();

		if($currentBook === null && $item instanceof WritableBookBase && !$item->isNull()){
			$newBook = (clone $item)->setCount(1);
			$tile->setBook($newBook);
			$tile->setViewedPage(0);
			$tile->setDirty();
			$this->book = $tile->getBook();
			$this->viewedPage = 0;

			$world->addSound($this->position, new LecternPlaceBookSound());
			if($player === null || $player->hasFiniteResources()){
				$item->pop();
			}

			$this->sendStateUpdate();
			if($player !== null){
				$this->sendStateUpdate($player);
			}
			return true;
		}

		if($currentBook !== null && $player !== null){
			// If the lectern holds a writable (unsigned) book, present it as a read-only written book
			// so players can read pages but not edit the lectern's copy.
			if ($currentBook instanceof WritableBook && !($currentBook instanceof \pocketmine\item\WrittenBook)) {
				$readOnly = \pocketmine\item\VanillaItems::WRITTEN_BOOK();
				$readOnly->setPages($currentBook->getPages());
				// leave title/author empty for unsigned books
				$this->previewBookToPlayer($player, $readOnly);
			} else {
				$this->previewBookToPlayer($player, $currentBook);
			}
			return true;
		}

		return false;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		$this->readStateFromWorld();
		$tile = $this->getTileLectern();
		if($tile === null){
			return false;
		}
		$book = $tile->getBook();
		if($book === null){
			return false;
		}

		$world = $this->position->getWorld();
		$world->dropItem($this->position->up(), $book);
		$tile->setBook(null);
		$tile->setDirty();
		$this->book = null;
		$this->viewedPage = 0;
		$this->sendStateUpdate();
		return true;
	}

	public function onPageTurn(int $newPage) : bool{
		$this->readStateFromWorld();
		$tile = $this->getTileLectern();
		if($tile === null){
			return false;
		}
		$book = $tile->getBook();
		if($newPage === $this->viewedPage){
			return true;
		}
		if($book === null || $newPage >= count($book->getPages()) || $newPage < 0){
			return false;
		}

		$this->viewedPage = $newPage;
		$tile->setViewedPage($newPage);
		$tile->setDirty();
		$world = $this->position->getWorld();
		if(!$this->producingSignal){
			$this->producingSignal = true;
			$world->scheduleDelayedBlockUpdate($this->position, 1);
		}

		$world->setBlock($this->position, $this);

		return true;
	}

	public function onScheduledUpdate() : void{
		if($this->producingSignal){
			$this->producingSignal = false;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}
}
