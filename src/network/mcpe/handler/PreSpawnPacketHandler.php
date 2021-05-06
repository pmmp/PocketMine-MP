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

use pocketmine\network\mcpe\cache\CraftingDataCache;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\network\mcpe\convert\ItemTypeDictionary;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\network\mcpe\protocol\types\PlayerMovementSettings;
use pocketmine\network\mcpe\protocol\types\PlayerMovementType;
use pocketmine\network\mcpe\protocol\types\SpawnSettings;
use pocketmine\player\Player;
use pocketmine\Server;

/**
 * Handler used for the pre-spawn phase of the session.
 */
class PreSpawnPacketHandler extends PacketHandler{

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
		$location = $this->player->getLocation();

		$pk = new StartGamePacket();
		$pk->entityUniqueId = $this->player->getId();
		$pk->entityRuntimeId = $this->player->getId();
		$pk->playerGamemode = TypeConverter::getInstance()->coreGameModeToProtocol($this->player->getGamemode());
		$pk->playerPosition = $this->player->getOffsetPosition($location);
		$pk->pitch = $location->pitch;
		$pk->yaw = $location->yaw;
		$pk->seed = -1;
		$pk->spawnSettings = new SpawnSettings(SpawnSettings::BIOME_TYPE_DEFAULT, "", DimensionIds::OVERWORLD); //TODO: implement this properly
		$pk->worldGamemode = TypeConverter::getInstance()->coreGameModeToProtocol($this->server->getGamemode());
		$pk->difficulty = $location->getWorld()->getDifficulty();
		$pk->spawnX = $spawnPosition->getFloorX();
		$pk->spawnY = $spawnPosition->getFloorY();
		$pk->spawnZ = $spawnPosition->getFloorZ();
		$pk->hasAchievementsDisabled = true;
		$pk->time = $location->getWorld()->getTime();
		$pk->eduEditionOffer = 0;
		$pk->rainLevel = 0; //TODO: implement these properly
		$pk->lightningLevel = 0;
		$pk->commandsEnabled = true;
		$pk->gameRules = [
			"naturalregeneration" => new BoolGameRule(false) //Hack for client side regeneration
		];
		$pk->experiments = new Experiments([], false);
		$pk->levelId = "";
		$pk->worldName = $this->server->getMotd();
		$pk->itemTable = ItemTypeDictionary::getInstance()->getEntries(); //TODO: check if this is actually needed
		$pk->playerMovementSettings = new PlayerMovementSettings(PlayerMovementType::LEGACY, 0, false);
		$this->session->sendDataPacket($pk);

		$this->session->sendDataPacket(StaticPacketCache::getInstance()->getAvailableActorIdentifiers());
		$this->session->sendDataPacket(StaticPacketCache::getInstance()->getBiomeDefs());
		$this->session->syncAttributes($this->player, $this->player->getAttributeMap()->getAll());
		$this->session->syncAvailableCommands();
		$this->session->syncAdventureSettings($this->player);
		foreach($this->player->getEffects()->all() as $effect){
			$this->session->onEntityEffectAdded($this->player, $effect, false);
		}
		$this->player->sendData([$this->player]);

		$this->session->getInvManager()->syncAll();
		$this->session->getInvManager()->syncCreative();
		$this->session->getInvManager()->syncSelectedHotbarSlot();
		$this->session->sendDataPacket(CraftingDataCache::getInstance()->getCache($this->server->getCraftingManager()));

		$this->session->syncPlayerList($this->server->getOnlinePlayers());
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool{
		$this->player->setViewDistance($packet->radius);

		return true;
	}
}
