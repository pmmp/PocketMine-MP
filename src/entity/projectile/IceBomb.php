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

namespace pocketmine\entity\projectile;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\ItemBreakParticle;
use pocketmine\world\sound\IceBombHitSound;

class IceBomb extends Throwable{
	public static function getNetworkTypeId() : string{ return EntityIds::ICE_BOMB; }

	public function getResultDamage() : int{
		return -1;
	}

	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult{
		if($block->getTypeId() === BlockTypeIds::WATER){
			$pos = $block->getPosition();

			return AxisAlignedBB::one()->offset($pos->x, $pos->y, $pos->z)->calculateIntercept($start, $end);
		}

		return parent::calculateInterceptWithBlock($block, $start, $end);
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$world = $this->getWorld();
		$pos = $this->location;

		$world->addSound($pos, new IceBombHitSound());
		$itemBreakParticle = new ItemBreakParticle(VanillaItems::ICE_BOMB());
		for($i = 0; $i < 6; ++$i){
			$world->addParticle($pos, $itemBreakParticle);
		}
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);

		$pos = $blockHit->getPosition();
		$world = $pos->getWorld();
		$posX = $pos->getFloorX();
		$posY = $pos->getFloorY();
		$posZ = $pos->getFloorZ();

		$ice = VanillaBlocks::ICE();
		for($x = $posX - 1; $x <= $posX + 1; $x++){
			for($y = $posY - 1; $y <= $posY + 1; $y++){
				for($z = $posZ - 1; $z <= $posZ + 1; $z++){
					if($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::WATER){
						$world->setBlockAt($x, $y, $z, $ice);
					}
				}
			}
		}
	}
}
