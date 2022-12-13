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
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\ItemBreakParticle;
use pocketmine\world\sound\BlockBreakSound;
use const PHP_INT_MAX;

class IceBomb extends Throwable{
	public static function getNetworkTypeId() : string{ return EntityIds::ICE_BOMB; }

	public function getResultDamage(): int{
		return -1;
	}

	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult{
		if($block instanceof Water){
			$pos = $block->getPosition();

			$nextHit = AxisAlignedBB::one()->offset($pos->getX(), $pos->getY(), $pos->getZ())->calculateIntercept($start, $end);
			if($nextHit === null){
				return null;
			}

			return $nextHit->hitVector->distanceSquared($start) < PHP_INT_MAX ? $nextHit : null;
		}

		return parent::calculateInterceptWithBlock($block, $start, $end);
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$world = $this->getWorld();
		$pos = $this->location;

		$world->addSound($pos, new BlockBreakSound(VanillaBlocks::GLASS()));
		for($i = 0; $i < 6; ++$i){
			$world->addParticle($pos, new ItemBreakParticle(VanillaItems::ICE_BOMB()));
		}
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);

		$pos = $blockHit->getPosition();
		$world = $pos->getWorld();
		$floorX = $pos->getFloorX();
		$floorY = $pos->getFloorY();
		$floorZ = $pos->getFloorZ();

		for($x = $floorX - 1; $x <= $floorX + 1; $x++){
			for($y = $floorY - 1; $y <= $floorY + 1; $y++){
				for($z = $floorZ - 1; $z <= $floorZ + 1; $z++){
					if($world->getBlockAt($x, $y, $z) instanceof Water){
						$world->setBlockAt($x, $y, $z, VanillaBlocks::ICE());
					}
				}
			}
		}
	}
}
