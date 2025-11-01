<?php


declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\world\sound\ThunderSound;

final class LightningBolt extends Entity{

	public static function getNetworkTypeId() : string{
		return EntityIds::LIGHTNING_BOLT;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.8, 0.6);
	}

	protected function getInitialGravity() : float{
		return 0.0;
	}

	protected function getInitialDragMultiplier() : float{
		return 0.02;
	}

	public function getName() : string{
		return "Lightning Bolt";
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->setNameTagVisible(false);
		$this->setSilent();
	}

	public function onFirstUpdate(int $currentTick) : void{
		parent::onFirstUpdate($currentTick);
		$this->getWorld()->addSound($this->location, new ThunderSound());
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		foreach($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(3, 3, 3), $this) as $entity){
			if($entity instanceof Living){
				$entity->attack(new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_LIGHTNING, 5.0));
			}
		}

		if($this->ticksLived > 20){
			$this->flagForDespawn();
		}

		return $hasUpdate;
	}

	protected function sendSpawnPacket(Player $player) : void{
		$player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
			$this->getId(),
			$this->getId(),
			self::getNetworkTypeId(),
			$this->location,
			null,
			0.0,
			0.0,
			0.0,
			0.0,
			[],
			[],
			new PropertySyncData([], []),
			[]
		));
	}
}