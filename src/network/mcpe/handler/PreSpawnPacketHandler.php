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
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\network\mcpe\protocol\types\LevelSettings;
use pocketmine\network\mcpe\protocol\types\PlayerMovementSettings;
use pocketmine\network\mcpe\protocol\types\PlayerMovementType;
use pocketmine\network\mcpe\protocol\types\SpawnSettings;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\VersionInfo;
use function sprintf;

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

	private InventoryManager $inventoryManager;

	public function __construct(Server $server, Player $player, NetworkSession $session, InventoryManager $inventoryManager){
		$this->player = $player;
		$this->server = $server;
		$this->session = $session;
		$this->inventoryManager = $inventoryManager;
	}

	public function setUp() : void{
		$location = $this->player->getLocation();

		$levelSettings = new LevelSettings();
		$levelSettings->seed = -1;
		$levelSettings->spawnSettings = new SpawnSettings(SpawnSettings::BIOME_TYPE_DEFAULT, "", DimensionIds::OVERWORLD); //TODO: implement this properly
		$levelSettings->worldGamemode = TypeConverter::getInstance()->coreGameModeToProtocol($this->server->getGamemode());
		$levelSettings->difficulty = $location->getWorld()->getDifficulty();
		$levelSettings->spawnPosition = BlockPosition::fromVector3($location->getWorld()->getSpawnLocation());
		$levelSettings->hasAchievementsDisabled = true;
		$levelSettings->time = $location->getWorld()->getTime();
		$levelSettings->eduEditionOffer = 0;
		$levelSettings->rainLevel = 0; //TODO: implement these properly
		$levelSettings->lightningLevel = 0;
		$levelSettings->commandsEnabled = true;
		$levelSettings->gameRules = [
			"naturalregeneration" => new BoolGameRule(false, false) //Hack for client side regeneration
		];
		$levelSettings->experiments = new Experiments([], false);

		$this->session->sendDataPacket(StartGamePacket::create(
			$this->player->getId(),
			$this->player->getId(),
			TypeConverter::getInstance()->coreGameModeToProtocol($this->player->getGamemode()),
			$this->player->getOffsetPosition($location),
			$location->pitch,
			$location->yaw,
			$levelSettings,
			"",
			$this->server->getMotd(),
			"",
			false,
			new PlayerMovementSettings(PlayerMovementType::LEGACY, 0, false),
			0,
			0,
			"",
			false,
			sprintf("%s %s", VersionInfo::NAME, VersionInfo::VERSION()->getFullVersion(true)),
			[],
			GlobalItemTypeDictionary::getInstance()->getDictionary()->getEntries()
		));

		$this->session->sendDataPacket(StaticPacketCache::getInstance()->getAvailableActorIdentifiers());
		$this->session->sendDataPacket(StaticPacketCache::getInstance()->getBiomeDefs());
		$this->session->syncAttributes($this->player, $this->player->getAttributeMap()->getAll());
		$this->session->syncAvailableCommands();
		$this->session->syncAdventureSettings($this->player);
		foreach($this->player->getEffects()->all() as $effect){
			$this->session->onEntityEffectAdded($this->player, $effect, false);
		}
		$this->player->sendData([$this->player]);

		$this->inventoryManager->syncAll();
		$this->inventoryManager->syncCreative();
		$this->inventoryManager->syncSelectedHotbarSlot();
		$this->session->sendDataPacket(CraftingDataCache::getInstance()->getCache($this->server->getCraftingManager()));

		$this->session->syncPlayerList($this->server->getOnlinePlayers());
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool{
		$this->player->setViewDistance($packet->radius);

		return true;
	}
}
