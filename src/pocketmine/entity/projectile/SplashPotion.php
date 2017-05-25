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

namespace pocketmine\entity\projectile;


use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\item\Potion;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\utils\Color;

class SplashPotion extends Projectile{

	const NETWORK_ID = 86;

	public $gravity = 0.05;
	public $drag = 0.01;

	public $length = 0.25;
	public $width = 0.25;
	public $height = 0.25;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, Item $sourceItem = null){
		if($sourceItem instanceof Item){
			$nbt->PotionId = new ShortTag("PotionId", $sourceItem->getDamage());
		}elseif(!isset($nbt->PotionId)){
			$nbt->PotionId = new ShortTag("PotionId", 0); //Default to water
		}
		parent::__construct($level, $nbt, $shootingEntity, $sourceItem);
	}

	protected function initEntity(){
		parent::initEntity();

		if(isset($this->namedtag->PotionId) and $this->namedtag->PotionId instanceof ShortTag){
			$this->setPotionId($this->namedtag->PotionId->getValue());
		}
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->PotionId = new ShortTag("PotionId", $this->getPotionId());
	}

	public function canCollideWith(Entity $entity){
		return false;
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if($this->isCollided){
			$effects = $this->getPotionEffects();
			$hasEffects = true;


			if(count($effects) === 0){
				$colors = [
					new Color(0x38, 0x5d, 0xc6) //Default colour for splash water bottle and similar with no effects.
				];
				$hasEffects = false;
			}else{
				$colors = [];
				foreach($effects as $effect){
					$level = $effect->getEffectLevel();
					for($j = 0; $j < $level; ++$j){
						$colors[] = $effect->getColor();
					}
				}
			}

			$resultColor = Color::mix(...$colors)->toARGB();

			$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_SPLASH, $resultColor);
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_GLASS);

			if($hasEffects){
				if(!$this->willLinger()){
					$bb = clone $this->getBoundingBox();
					$bb->expand(4.125, 2.125, 4.125);
					foreach($this->level->getNearbyEntities($bb, $this) as $entity){
						if($entity instanceof Living){
							$distanceSquared = $entity->distanceSquared($this);
							if($distanceSquared > 16){ //4 blocks
								continue;
							}

							$distanceMultiplier = 0.25 * (4 - floor(sqrt($distanceSquared)));

							foreach($this->getPotionEffects() as $effect){
								if(!$effect->isInstantEffect()){
									$newDuration = round($effect->getDuration() * 0.75 * $distanceMultiplier);
									if($newDuration < 20){
										continue;
									}
									$effect->setDuration($newDuration);
								}else{
									$effect->setPotency($distanceMultiplier);
								}

								$entity->addEffect($effect);
							}
						}
					}
				}else{
					//TODO: lingering potions
				}
			}
		}

		if($this->isCollided or $this->age > 1200){
			$this->close();
			$hasUpdate = false;
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}

	/**
	 * Returns the meta value of the potion item that this splash potion corresponds to. This decides what effects will be applied to the entity when it collides with its target.
	 * @return int
	 */
	public function getPotionId() : int{
		return $this->getDataProperty(self::DATA_POTION_AUX_VALUE) ?? 0;
	}

	/**
	 * @param int $id
	 */
	public function setPotionId(int $id){
		$this->setDataProperty(self::DATA_POTION_AUX_VALUE, self::DATA_TYPE_SHORT, $id);
	}

	/**
	 * Returns whether this splash potion will create an area-effect cloud when it lands.
	 * @return bool
	 */
	public function willLinger() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LINGER);
	}

	/**
	 * Sets whether this splash potion will create an area-effect-cloud when it lands.
	 * @param bool $value
	 */
	public function setLinger(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LINGER, $value);
	}

	/**
	 * @return Effect[]
	 */
	public function getPotionEffects() : array{
		return Potion::getEffectsByPotionId($this->getPotionId());
	}

	public static function getSaveId() : string{
		return "thrownpotion";
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = SplashPotion::NETWORK_ID;
		$pk->entityRuntimeId = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

}