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

use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;

/*
 * This is a Helper class to create a simple Bossbar
 * Note: This is not an entity
 */

class Bossbar extends Vector3{
	/** @var int */
	protected $entityId;
	/** @var string */
	protected $title;
	/** @var float */
	protected $healthPercent;
	/** @var Player[] */
	protected $viewers = [];

	public function __construct(string $title = "", float $hp = 1.0){
		parent::__construct(0, 0, 0);

		$this->entityId = Entity::$entityCount++;
		$this->title = $title;
		$this->setHealthPercent($hp, false);
	}

	public function setTitle(string $title, bool $update = true){
		$this->title = $title;

		if($update){
			$this->updateForAll();
		}
	}

	public function getTitle() : string{
		return $this->title;
	}

	/**
	 * @param float $hp This should be in 0.0-1.0 range
	 * @param bool  $update
	 */
	public function setHealthPercent(float $hp, bool $update = true){
		$this->healthPercent = max(0, min(1.0, $hp));

		if($update){
			$this->updateForAll();
		}
	}

	public function getHealthPercent() : float{
		return $this->healthPercent;
	}

	public function showTo(Player $player, bool $isViewer = true){
		$pk = new AddActorPacket();
		$pk->entityRuntimeId = $this->entityId;
		$pk->type = AddActorPacket::LEGACY_ID_MAP_BC[EntityIds::SLIME];
		$pk->metadata = [
			Entity::DATA_FLAGS => [
				Entity::DATA_TYPE_LONG,
				((1 << Entity::DATA_FLAG_INVISIBLE) | (1 << Entity::DATA_FLAG_IMMOBILE))
			],
			Entity::DATA_NAMETAG => [
				Entity::DATA_TYPE_STRING,
				$this->title
			]
		];
		$pk->position = $this;

		$player->sendDataPacket($pk);
		$this->sendBossEventPacket($player, BossEventPacket::TYPE_SHOW);

		if($isViewer){
			$this->viewers[spl_object_id($player)] = $player;
		}
	}

	public function hideFrom(Player $player){
		$this->sendBossEventPacket($player, BossEventPacket::TYPE_HIDE);

		$pk2 = new RemoveActorPacket();
		$pk2->entityUniqueId = $this->entityId;

		$player->sendDataPacket($pk2);

		if(isset($this->viewers[spl_object_id($player)])){
			unset($this->viewers[spl_object_id($player)]);
		}
	}

	public function updateFor(Player $player){
		$this->sendBossEventPacket($player, BossEventPacket::TYPE_HEALTH_PERCENT);
		$this->sendBossEventPacket($player, BossEventPacket::TYPE_TITLE);
	}

	public function updateForAll() : void{
		foreach($this->viewers as $player){
			$this->updateFor($player);
		}
	}

	protected function sendBossEventPacket(Player $player, int $eventType) : void{
		$pk = new BossEventPacket();
		$pk->bossEid = $this->entityId;
		$pk->eventType = $eventType;

		switch($eventType){
			case BossEventPacket::TYPE_SHOW:
				$pk->title = $this->title;
				$pk->healthPercent = $this->healthPercent;
				$pk->color = 0;
				$pk->overlay = 0;
				$pk->unknownShort = 0;
				break;
			case BossEventPacket::TYPE_REGISTER_PLAYER:
			case BossEventPacket::TYPE_UNREGISTER_PLAYER:
				$pk->playerEid = $player->getId();
				break;
			case BossEventPacket::TYPE_TITLE:
				$pk->title = $this->title;
				break;
			case BossEventPacket::TYPE_HEALTH_PERCENT:
				$pk->healthPercent = $this->healthPercent;
				break;
		}

		$player->sendDataPacket($pk);
	}

	public function getViewers() : array{
		return $this->viewers;
	}

	public function getEntityId() : int{
		return $this->entityId;
	}
}