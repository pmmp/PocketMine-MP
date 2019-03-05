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

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\block\utils\SignText;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Sign as TileSign;
use pocketmine\utils\TextFormat;
use function array_map;
use function assert;
use function floor;

class Sign extends Transparent{
	/** @var BlockIdentifierFlattened */
	protected $idInfo;

	//TODO: conditionally useless properties, find a way to fix

	/** @var int */
	protected $rotation = 0;

	/** @var int */
	protected $facing = Facing::UP;

	/** @var SignText */
	protected $text;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name){
		parent::__construct($idInfo, $name);
		$this->text = new SignText();
	}

	public function __clone(){
		$this->text = clone $this->text;
	}

	public function getId() : int{
		return $this->facing === Facing::UP ? parent::getId() : $this->idInfo->getSecondId();
	}

	protected function writeStateToMeta() : int{
		if($this->facing === Facing::UP){
			return $this->rotation;
		}
		return $this->facing;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		if($id === $this->idInfo->getSecondId()){
			$this->facing = BlockDataValidator::readHorizontalFacing($stateMeta);
		}else{
			$this->facing = Facing::UP;
			$this->rotation = $stateMeta;
		}
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->level->getTile($this);
		if($tile instanceof TileSign){
			$this->text = $tile->getText();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->level->getTile($this);
		assert($tile instanceof TileSign);
		$tile->setText($this->text);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getHardness() : float{
		return 1;
	}

	public function isSolid() : bool{
		return false;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return null;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($face !== Facing::DOWN){
			$this->facing = $face;
			if($face === Facing::UP){
				$this->rotation = $player !== null ? ((int) floor((($player->yaw + 180) * 16 / 360) + 0.5)) & 0x0f : 0;
			}

			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::opposite($this->facing))->getId() === self::AIR){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	/**
	 * Returns an object containing information about the sign text.
	 *
	 * @return SignText
	 */
	public function getText() : SignText{
		return $this->text;
	}

	/**
	 * Called by the player controller (network session) to update the sign text, firing events as appropriate.
	 *
	 * @param Player   $author
	 * @param SignText $text
	 *
	 * @return bool if the sign update was successful.
	 */
	public function updateText(Player $author, SignText $text) : bool{
		$removeFormat = $author->getRemoveFormat();
		$ev = new SignChangeEvent($this, $author, new SignText(array_map(function(string $line) use ($removeFormat){
			return TextFormat::clean($line, $removeFormat);
		}, $text->getLines())));
		$ev->call();
		if(!$ev->isCancelled()){
			$this->text = clone $ev->getNewText();
			$this->level->setBlock($this, $this);
			return true;
		}

		return false;
	}
}
