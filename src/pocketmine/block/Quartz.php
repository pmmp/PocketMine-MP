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

use pocketmine\block\utils\PillarRotationTrait;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Quartz extends Solid{
	use PillarRotationTrait;

	public const NORMAL = 0;
	public const CHISELED = 1;
	public const PILLAR = 2;

	protected $id = self::QUARTZ_BLOCK;

	/** @var int */
	protected $variant = self::NORMAL;

	public function __construct(int $meta = 0){
		$this->setDamage($meta);
	}

	public function getDamage() : int{
		return $this->variant | ($this->variant !== self::NORMAL ? $this->writeAxisToMeta() : 0);
	}

	public function setDamage(int $meta) : void{
		$this->variant = $meta & 0x03;
		if($this->variant !== self::NORMAL){
			$this->readAxisFromMeta($meta);
		}
	}

	public function getVariant() : int{
		return $this->variant;
	}

	public function getHardness() : float{
		return 0.8;
	}

	public function getName() : string{
		static $names = [
			self::NORMAL => "Quartz Block",
			self::CHISELED => "Chiseled Quartz Block",
			self::PILLAR => "Quartz Pillar"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($this->variant !== self::NORMAL){
			$this->axis = Facing::axis($face);
		}
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}
}
