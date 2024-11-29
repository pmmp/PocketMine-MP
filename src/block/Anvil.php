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

use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\utils\Fallable;
use pocketmine\block\utils\FallableTrait;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\object\FallingBlock;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\Sound;
use function round;

class Anvil extends Transparent implements Fallable{
	use FallableTrait;
	use HorizontalFacingTrait;

	public const UNDAMAGED = 0;
	public const SLIGHTLY_DAMAGED = 1;
	public const VERY_DAMAGED = 2;

	private int $damage = self::UNDAMAGED;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(self::UNDAMAGED, self::VERY_DAMAGED, $this->damage);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
	}

	public function getDamage() : int{ return $this->damage; }

	/** @return $this */
	public function setDamage(int $damage) : self{
		if($damage < self::UNDAMAGED || $damage > self::VERY_DAMAGED){
			throw new \InvalidArgumentException("Damage must be in range " . self::UNDAMAGED . " ... " . self::VERY_DAMAGED);
		}
		$this->damage = $damage;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->squash(Facing::axis(Facing::rotateY($this->facing, false)), 1 / 8)];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player instanceof Player){
			$player->setCurrentWindow(new AnvilInventory($this->position));
		}

		return true;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = Facing::rotateY($player->getHorizontalFacing(), false);
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onHitGround(FallingBlock $blockEntity) : bool{
		if(Utils::getRandomFloat() < 0.05 + (round($blockEntity->getFallDistance()) - 1) * 0.05){
			if($this->damage !== self::VERY_DAMAGED){
				$this->damage = $this->damage + 1;
			}else{
				return false;
			}
		}
		return true;
	}

	public function getFallDamagePerBlock() : float{
		return 2.0;
	}

	public function getMaxFallDamage() : float{
		return 40.0;
	}

	public function getLandSound() : ?Sound{
		return new AnvilFallSound();
	}
}
