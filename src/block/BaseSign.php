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

use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\SignText;
use pocketmine\block\utils\SupportType;
use pocketmine\block\utils\WoodType;
use pocketmine\block\utils\WoodTypeTrait;
use pocketmine\color\Color;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\DyeUseSound;
use pocketmine\world\sound\InkSacUseSound;
use function array_map;
use function assert;
use function strlen;

abstract class BaseSign extends Transparent{
	use WoodTypeTrait;

	protected SignText $text;
	private bool $waxed = false;

	protected ?int $editorEntityRuntimeId = null;

	/** @var \Closure() : Item */
	private \Closure $asItemCallback;

	/**
	 * @param \Closure() : Item $asItemCallback
	 */
	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, WoodType $woodType, \Closure $asItemCallback){
		$this->woodType = $woodType;
		parent::__construct($idInfo, $name, $typeInfo);
		$this->text = new SignText();
		$this->asItemCallback = $asItemCallback;
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileSign){
			$this->text = $tile->getText();
			$this->waxed = $tile->isWaxed();
			$this->editorEntityRuntimeId = $tile->getEditorEntityRuntimeId();
		}

		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileSign);
		$tile->setText($this->text);
		$tile->setWaxed($this->waxed);
		$tile->setEditorEntityRuntimeId($this->editorEntityRuntimeId);
	}

	public function isSolid() : bool{
		return false;
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	abstract protected function getSupportingFace() : int;

	public function onNearbyBlockChange() : void{
		if($this->getSide($this->getSupportingFace())->getTypeId() === BlockTypeIds::AIR){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->editorEntityRuntimeId = $player->getId();
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onPostPlace() : void{
		$player = $this->editorEntityRuntimeId !== null ?
			$this->position->getWorld()->getEntity($this->editorEntityRuntimeId) :
			null;
		if($player instanceof Player){
			$player->openSignEditor($this->position);
		}
	}

	private function doSignChange(SignText $newText, Player $player, Item $item) : bool{
		$ev = new SignChangeEvent($this, $player, $newText);
		$ev->call();
		if(!$ev->isCancelled()){
			$this->text = $ev->getNewText();
			$this->position->getWorld()->setBlock($this->position, $this);
			$item->pop();
			return true;
		}

		return false;
	}

	private function changeSignGlowingState(bool $glowing, Player $player, Item $item) : bool{
		if($this->text->isGlowing() !== $glowing && $this->doSignChange(new SignText($this->text->getLines(), $this->text->getBaseColor(), $glowing), $player, $item)){
			$this->position->getWorld()->addSound($this->position, new InkSacUseSound());
			return true;
		}
		return false;
	}

	private function wax(Player $player, Item $item) : bool{
		if($this->waxed){
			return false;
		}

		$this->waxed = true;
		$this->position->getWorld()->setBlock($this->position, $this);
		$item->pop();

		return true;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player === null){
			return false;
		}
		if($this->waxed){
			return true;
		}

		$dyeColor = $item instanceof Dye ? $item->getColor() : match($item->getTypeId()){
			ItemTypeIds::BONE_MEAL => DyeColor::WHITE,
			ItemTypeIds::LAPIS_LAZULI => DyeColor::BLUE,
			ItemTypeIds::COCOA_BEANS => DyeColor::BROWN,
			default => null
		};
		if($dyeColor !== null){
			$color = $dyeColor === DyeColor::BLACK ? new Color(0, 0, 0) : $dyeColor->getRgbValue();
			if(
				$color->toARGB() !== $this->text->getBaseColor()->toARGB() &&
				$this->doSignChange(new SignText($this->text->getLines(), $color, $this->text->isGlowing()), $player, $item)
			){
				$this->position->getWorld()->addSound($this->position, new DyeUseSound());
				return true;
			}
		}elseif(match($item->getTypeId()){
			ItemTypeIds::INK_SAC => $this->changeSignGlowingState(false, $player, $item),
			ItemTypeIds::GLOW_INK_SAC => $this->changeSignGlowingState(true, $player, $item),
			ItemTypeIds::HONEYCOMB => $this->wax($player, $item),
			default => false
		}){
			return true;
		}

		$player->openSignEditor($this->position);

		return true;
	}

	/**
	 * Returns an object containing information about the sign text.
	 */
	public function getText() : SignText{
		return $this->text;
	}

	/** @return $this */
	public function setText(SignText $text) : self{
		$this->text = $text;
		return $this;
	}

	/**
	 * Returns whether the sign has been waxed using a honeycomb. If true, the sign cannot be edited by a player.
	 */
	public function isWaxed() : bool{ return $this->waxed; }

	/** @return $this */
	public function setWaxed(bool $waxed) : self{
		$this->waxed = $waxed;
		return $this;
	}

	/**
	 * Sets the runtime entity ID of the player editing this sign. Only this player will be able to edit the sign.
	 * This is used to prevent multiple players from editing the same sign at the same time, and to prevent players
	 * from editing signs they didn't place.
	 */
	public function getEditorEntityRuntimeId() : ?int{ return $this->editorEntityRuntimeId; }

	/** @return $this */
	public function setEditorEntityRuntimeId(?int $editorEntityRuntimeId) : self{
		$this->editorEntityRuntimeId = $editorEntityRuntimeId;
		return $this;
	}

	/**
	 * Called by the player controller (network session) to update the sign text, firing events as appropriate.
	 *
	 * @return bool if the sign update was successful.
	 * @throws \UnexpectedValueException if the text payload is too large
	 */
	public function updateText(Player $author, SignText $text) : bool{
		$size = 0;
		foreach($text->getLines() as $line){
			$size += strlen($line);
		}
		if($size > 1000){
			throw new \UnexpectedValueException($author->getName() . " tried to write $size bytes of text onto a sign (bigger than max 1000)");
		}
		$ev = new SignChangeEvent($this, $author, new SignText(array_map(function(string $line) : string{
			return TextFormat::clean($line, false);
		}, $text->getLines()), $this->text->getBaseColor(), $this->text->isGlowing()));
		if($this->waxed || $this->editorEntityRuntimeId !== $author->getId()){
			$ev->cancel();
		}
		$ev->call();
		if(!$ev->isCancelled()){
			$this->setText($ev->getNewText());
			$this->setEditorEntityRuntimeId(null);
			$this->position->getWorld()->setBlock($this->position, $this);
			return true;
		}

		return false;
	}

	public function asItem() : Item{
		return ($this->asItemCallback)();
	}

	public function getFuelTime() : int{
		return $this->woodType->isFlammable() ? 200 : 0;
	}
}
