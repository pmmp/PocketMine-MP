<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\utils;

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{AddActorPacket,
	AddEntityPacket,
	BossEventPacket,
	RemoveActorPacket,
	RemoveEntityPacket,
	SetActorDataPacket,
	UpdateAttributesPacket};
use pocketmine\Player;

/*
 * This is a Helper class to create a simple Bossbar
 * Note: This is not an entity
 */

class Bossbar extends Vector3{
	/** @var float */
	protected $healthPercent = 0, $maxHealthPercent = 1;
	/** @var int */
	protected $entityId;
	/** @var array */
	protected $metadata = [];
	/** @var Player[] */
	protected $viewers = [];

	public function __construct(string $title = "", float $hp = 1, float $maxHp = 1){
		parent::__construct(0, 0, 0);

		$flags = ((1 << Entity::DATA_FLAG_INVISIBLE) | (1 << Entity::DATA_FLAG_IMMOBILE));
		$this->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]
		];

		$this->entityId = Entity::$entityCount++;

		$this->setHealthPercent($hp, $maxHp);
	}

	public function setTitle(string $t, bool $update = true){
		$this->setMetadata(Entity::DATA_NAMETAG, Entity::DATA_TYPE_STRING, $t);
		if($update){
			$this->updateForAll();
		}
	}

	public function getTitle() : string{
		return $this->getMetadata(Entity::DATA_NAMETAG);
	}

	public function setHealthPercent(?float $hp = null, ?float $maxHp = null, bool $update = true){
		if($maxHp !== null){
			$this->maxHealthPercent = $maxHp;
		}

		if($hp !== null){
			if($hp > $this->maxHealthPercent){
				$this->maxHealthPercent = $hp;
			}

			$this->healthPercent = $hp;
		}

		if($update){
			$this->updateForAll();
		}
	}

	public function getHealthPercent() : float{
		return $this->healthPercent;
	}

	public function getMaxHealthPercent() : float{
		return $this->maxHealthPercent;
	}

	public function showTo(Player $player, bool $isViewer = true){
		$pk = new AddActorPacket();
		$pk->entityRuntimeId = $this->entityId;
		$pk->type = EntityIds::SHULKER;
		$pk->metadata = $this->metadata;
		$pk->position = $this;

		$player->sendDataPacket($pk);
		$player->sendDataPacket($this->getHealthPacket());

		$pk2 = new BossEventPacket();
		$pk2->bossEid = $this->entityId;
		$pk2->eventType = BossEventPacket::TYPE_SHOW;
		$pk2->title = $this->getTitle();
		$pk2->healthPercent = $this->healthPercent;
		$pk2->color = 0;
		$pk2->overlay = 0;
		$pk2->unknownShort = 0;

		$player->sendDataPacket($pk2);

		if($isViewer){
			$this->viewers[spl_object_id($player)] = $player;
		}
	}

	public function hideFrom(Player $player){
		$pk = new BossEventPacket();
		$pk->bossEid = $this->entityId;
		$pk->eventType = BossEventPacket::TYPE_HIDE;

		$player->sendDataPacket($pk);

		$pk2 = new RemoveActorPacket();
		$pk2->entityUniqueId = $this->entityId;

		$player->sendDataPacket($pk2);

		if(isset($this->viewers[spl_object_id($player)])){
			unset($this->viewers[spl_object_id($player)]);
		}
	}

	public function updateFor(Player $player){
		$pk = new BossEventPacket();
		$pk->bossEid = $this->entityId;
		$pk->eventType = BossEventPacket::TYPE_TITLE;
		$pk->healthPercent = $this->getHealthPercent();
		$pk->title = $this->getTitle();

		$pk2 = clone $pk;
		$pk2->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;

		$player->sendDataPacket($pk);
		$player->sendDataPacket($pk2);
		$player->sendDataPacket($this->getHealthPacket());

		$mpk = new SetActorDataPacket();
		$mpk->entityRuntimeId = $this->entityId;
		$mpk->metadata = $this->metadata;

		$player->sendDataPacket($mpk);
	}

	public function updateForAll() : void{
		foreach($this->viewers as $player){
			$this->updateFor($player);
		}
	}

	protected function getHealthPacket() : UpdateAttributesPacket{
		$attr = Attribute::getAttribute(Attribute::HEALTH);
		$attr->setMaxValue($this->maxHealthPercent);
		$attr->setValue($this->healthPercent);

		$pk = new UpdateAttributesPacket();
		$pk->entityRuntimeId = $this->entityId;
		$pk->entries = [$attr];

		return $pk;
	}

	public function setMetadata(int $key, int $dtype, $value){
		$this->metadata[$key] = [$dtype, $value];
	}

	/**
	 * @param int $key
	 *
	 * @return mixed
	 */
	public function getMetadata(int $key){
		return isset($this->metadata[$key]) ? $this->metadata[$key][1] : null;
	}

	public function getViewers() : array{
		return $this->viewers;
	}

	public function getEntityId() : int{
		return $this->entityId;
	}
}