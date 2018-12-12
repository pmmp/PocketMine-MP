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

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AvailableEntityIdentifiersPacket;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Handler used for the pre-spawn phase of the session.
 */
class PreSpawnSessionHandler extends SessionHandler{

	/** @var Server */
	private $server;
	/** @var Player */
	private $player;
	/** @var NetworkSession */
	private $session;

	public function __construct(Server $server, Player $player, NetworkSession $session){
		$this->player = $player;
		$this->server = $server;
		$this->session = $session;
	}

	public function setUp() : void{
		$spawnPosition = $this->player->getSpawn();

		$pk = new StartGamePacket();
		$pk->entityUniqueId = $this->player->getId();
		$pk->entityRuntimeId = $this->player->getId();
		$pk->playerGamemode = Player::getClientFriendlyGamemode($this->player->getGamemode());
		$pk->playerPosition = $this->player->getOffsetPosition($this->player);
		$pk->pitch = $this->player->pitch;
		$pk->yaw = $this->player->yaw;
		$pk->seed = -1;
		$pk->dimension = DimensionIds::OVERWORLD; //TODO: implement this properly
		$pk->worldGamemode = Player::getClientFriendlyGamemode($this->server->getGamemode());
		$pk->difficulty = $this->player->getLevel()->getDifficulty();
		$pk->spawnX = $spawnPosition->getFloorX();
		$pk->spawnY = $spawnPosition->getFloorY();
		$pk->spawnZ = $spawnPosition->getFloorZ();
		$pk->hasAchievementsDisabled = true;
		$pk->time = $this->player->getLevel()->getTime();
		$pk->eduMode = false;
		$pk->rainLevel = 0; //TODO: implement these properly
		$pk->lightningLevel = 0;
		$pk->commandsEnabled = true;
		$pk->levelId = "";
		$pk->worldName = $this->server->getMotd();
		$this->session->sendDataPacket($pk);

		$this->session->sendDataPacket(new AvailableEntityIdentifiersPacket());

		$this->player->setImmobile(); //HACK: fix client-side falling pre-spawn

		$this->player->getLevel()->sendTime($this->player);

		$this->player->sendAttributes(true);
		$this->player->sendCommandData();
		$this->player->sendSettings();
		$this->player->sendPotionEffects($this->player);
		$this->player->sendData($this->player);

		$this->player->sendAllInventories();
		$this->player->getInventory()->sendCreativeContents();
		$this->player->getInventory()->sendHeldItem($this->player);
		$this->session->queueCompressed($this->server->getCraftingManager()->getCraftingDataPacket());

		$this->server->sendFullPlayerListData($this->player);
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool{
		$this->player->setViewDistance($packet->radius);

		return true;
	}

	public function handleSetLocalPlayerAsInitialized(SetLocalPlayerAsInitializedPacket $packet) : bool{
		$this->player->setImmobile(false); //HACK: this is set to prevent client-side falling before spawn

		$this->player->doFirstSpawn();

		return true;
	}
}
