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

use pocketmine\block\utils\StructureBlockType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;

class StructureBlock extends Opaque{
	private StructureBlockType $type;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo){
		$this->type = StructureBlockType::SAVE;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if ($player instanceof Player) {
			$pk = ContainerOpenPacket::blockInv(0, WindowTypes::STRUCTURE_EDITOR, BlockPosition::fromVector3($this->getPosition()));
			$player->getNetworkSession()->sendDataPacket($pk);
			return true;
		}
		return false;
	}

	public function describeBlockItemState(RuntimeDataDescriber $w) : void{
		$w->enum($this->type);
	}

	public function getType() : StructureBlockType{
		return $this->type;
	}

	/** @return $this */
	public function setType(StructureBlockType $type) : self{
		$this->type = $type;
		if ($this->position->isValid()){
			$this->position->getWorld()->setBlock($this->position, $this);
		}
		return $this;
	}

	//TODO: The Structure Block has redstone effects, they are not implemented.
}
