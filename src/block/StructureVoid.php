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

use pocketmine\block\utils\StructureVoidType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\AxisAlignedBB;

class StructureVoid extends Transparent{
	private StructureVoidType $type;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo){
		$this->type = StructureVoidType::VOID;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function isSolid() : bool{
		return false;
	}

	public function describeBlockItemState(RuntimeDataDescriber $w) : void{
		$w->enum($this->type);
	}

	public function getType() : StructureVoidType{
		return $this->type;
	}

	/** @return $this */
	public function setType(StructureVoidType $type) : self{
		$this->type = $type;
		return $this;
	}
}
