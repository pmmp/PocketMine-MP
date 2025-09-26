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

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\BlockPreExplodeEvent;
use pocketmine\event\player\PlayerRespawnAnchorUseEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Explosion;
use pocketmine\world\Position;
use pocketmine\world\sound\RespawnAnchorChargeSound;
use pocketmine\world\sound\RespawnAnchorSetSpawnSound;

final class RespawnAnchor extends Opaque{
	private const MIN_CHARGES = 0;
	private const MAX_CHARGES = 4;

	private int $charges = self::MIN_CHARGES;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(self::MIN_CHARGES, self::MAX_CHARGES, $this->charges);
	}

	public function getCharges() : int{
		return $this->charges;
	}

	/** @return $this */
	public function setCharges(int $charges) : self{
		if($charges < self::MIN_CHARGES || $charges > self::MAX_CHARGES){
			throw new \InvalidArgumentException("Charges must be between " . self::MIN_CHARGES . " and " . self::MAX_CHARGES . ", given: $charges");
		}
		$this->charges = $charges;
		return $this;
	}

	public function getLightLevel() : int{
		return $this->charges > 0 ? ($this->charges * 4) - 1 : 0;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item->getTypeId() === ItemTypeIds::fromBlockTypeId(BlockTypeIds::GLOWSTONE) && $this->charges < self::MAX_CHARGES){
			$this->position->getWorld()->setBlock($this->position, $this->setCharges($this->charges + 1));
			$this->position->getWorld()->addSound($this->position, new RespawnAnchorChargeSound());
			return true;
		}

		if($this->charges > self::MIN_CHARGES){
			if($player === null){
				return false;
			}

			$ev = new PlayerRespawnAnchorUseEvent($player, $this, PlayerRespawnAnchorUseEvent::ACTION_EXPLODE);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}

			switch($ev->getAction()){
				case PlayerRespawnAnchorUseEvent::ACTION_EXPLODE:
					$this->explode($player);
					return true;

				case PlayerRespawnAnchorUseEvent::ACTION_SET_SPAWN:
					if($player->getSpawn() !== null && $player->getSpawn()->equals($this->position)){
						return true;
					}

					$player->setSpawn($this->position);
					$this->position->getWorld()->addSound($this->position, new RespawnAnchorSetSpawnSound());
					$player->sendMessage(KnownTranslationFactory::tile_respawn_anchor_respawnSet()->prefix(TextFormat::GRAY));
					return true;
			}
		}
		return false;
	}

	private function explode(?Player $player) : void{
		$ev = new BlockPreExplodeEvent($this, 5, $player);
		$ev->setIncendiary(true);

		$ev->call();
		if($ev->isCancelled()){
			return;
		}

		$this->position->getWorld()->setBlock($this->position, VanillaBlocks::AIR());

		$explosion = new Explosion(Position::fromObject($this->position->add(0.5, 0.5, 0.5), $this->position->getWorld()), $ev->getRadius(), $this);
		$explosion->setFireChance($ev->getFireChance());

		if($ev->isBlockBreaking()){
			$explosion->explodeA();
		}
		$explosion->explodeB();
	}
}
