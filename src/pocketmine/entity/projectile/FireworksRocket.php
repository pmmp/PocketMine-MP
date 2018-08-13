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

use pocketmine\entity\Entity;
use pocketmine\item\Fireworks;
use pocketmine\level\Level;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\utils\Random;

class FireworksRocket extends Projectile{
	public const NETWORK_ID = self::FIREWORKS_ROCKET;

	/** @var float */
	public $width = 0.25;
	/** @var float */
	public $height = 0.25;
	/** @var float */
	public $gravity = 0.0;
	/** @var float */
	public $drag = 0.01;
	/** @var int */
	private $lifeTime = 0;
	/** @var Fireworks */
	private $item;
	/** @var null|Random */
	private $random;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, Fireworks $item = null, ?Random $random = null){
		$this->random = $random;
		$this->item = $item;
		parent::__construct($level, $nbt, $shootingEntity);
	}

	protected function initEntity() : void{
		parent::initEntity();
		$random = $this->random ?? new Random;

		$this->setGenericFlag(self::DATA_FLAG_HAS_COLLISION, true);
		$this->setGenericFlag(self::DATA_FLAG_AFFECTED_BY_GRAVITY, true);
		$this->getDataPropertyManager()->setItem(self::DATA_MINECART_DISPLAY_BLOCK, $this->item);

		$flyTime = 1;

		if($this->namedtag->hasTag("Fireworks")){
			$flyTime = $this->namedtag->getCompoundTag("Fireworks")->getByte("Flight", 1);
		}

		$this->lifeTime = 20 * $flyTime + $random->nextBoundedInt(5) + $random->nextBoundedInt(7);
	}

	public function spawnTo(Player $player) : void{
		$this->setMotion($this->getDirectionVector());
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LAUNCH);
		parent::spawnTo($player);
	}

	public function despawnFromAll() : void{
		$this->broadcastEntityEvent(EntityEventPacket::FIREWORK_PARTICLES);
		parent::despawnFromAll();
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BLAST);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->lifeTime-- < 0){
			$this->flagForDespawn();
			return true;
		}else{
			return parent::entityBaseTick($tickDiff);
		}
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
	}
}
