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

/**
 * PocketMine-MP is the Minecraft: PE multiplayer server software
 * Homepage: http://www.pocketmine.net/
 */
namespace pocketmine;

use pocketmine\block\Block;
use pocketmine\command\CommandReader;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\entity\Arrow;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\FallingSand;
use pocketmine\entity\Human;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\PrimedTNT;
use pocketmine\entity\Snowball;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\event\HandlerList;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;
use pocketmine\event\TranslationContainer;
use pocketmine\inventory\CraftingManager;
use pocketmine\inventory\InventoryType;
use pocketmine\inventory\Recipe;
use pocketmine\item\Item;
use pocketmine\lang\BaseLang;
use pocketmine\level\format\anvil\Anvil;
use pocketmine\level\format\leveldb\LevelDB;
use pocketmine\level\format\LevelProviderManager;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\normal\Normal;
use pocketmine\level\Level;
use pocketmine\metadata\EntityMetadataStore;
use pocketmine\metadata\LevelMetadataStore;
use pocketmine\metadata\PlayerMetadataStore;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\network\CompressBatchedTask;
use pocketmine\network\Network;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\RakLibInterface;
use pocketmine\network\rcon\RCON;
use pocketmine\network\SourceInterface;
use pocketmine\network\upnp\UPnP;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\plugin\PluginManager;
use pocketmine\scheduler\SendUsageTask;
use pocketmine\scheduler\ServerScheduler;
use pocketmine\tile\Chest;
use pocketmine\tile\Furnace;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\updater\AutoUpdater;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
use pocketmine\utils\LevelException;
use pocketmine\utils\MainLogger;
use pocketmine\utils\ServerException;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextWrapper;
use pocketmine\utils\Utils;
use pocketmine\utils\VersionString;

/**
 * The class that manages everything
 */
class Server{
	const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	/** @var Server */
	private static $instance = null;

	/** @var BanList */
	private $banByName = null;

	/** @var BanList */
	private $banByIP = null;

	/** @var Config */
	private $operators = null;

	/** @var Config */
	private $whitelist = null;

	/** @var bool */
	private $isRunning = true;

	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager = null;

	/** @var AutoUpdater */
	private $updater = null;

	/** @var ServerScheduler */
	private $scheduler = null;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */
	private $tickCounter;
	private $nextTick = 0;
	private $tickAverage = [20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20];
	private $useAverage = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	private $maxTick = 20;
	private $maxUse = 0;

	private $sendUsageTicker = 0;

	private $dispatchSignals = false;

	/** @var \AttachableThreadedLogger */
	private $logger;

	/** @var MemoryManager */
	private $memoryManager;

	/** @var CommandReader */
	private $console = null;
	private $consoleThreaded;

	/** @var SimpleCommandMap */
	private $commandMap = null;

	/** @var CraftingManager */
	private $craftingManager;

	/** @var ConsoleCommandSender */
	private $consoleSender;

	/** @var int */
	private $maxPlayers;

	/** @var bool */
	private $autoSave;

	/** @var RCON */
	private $rcon;

	/** @var EntityMetadataStore */
	private $entityMetadata;

	/** @var PlayerMetadataStore */
	private $playerMetadata;

	/** @var LevelMetadataStore */
	private $levelMetadata;

	/** @var Network */
	private $network;

	private $networkCompressionAsync = true;
	private $networkCompressionLevel = 7;

	private $autoTickRate = true;
	private $autoTickRateLimit = 20;
	private $alwaysTickPlayers = false;
	private $baseTickRate = 1;

	private $autoSaveTicker = 0;
	private $autoSaveTicks = 0;

	/** @var BaseLang */
	private $baseLang;

	private $forceLanguage = false;

	private $serverID;

	private $autoloader;
	private $filePath;
	private $dataPath;
	private $pluginPath;

	private $lastSendUsage = null;

	/** @var QueryHandler */
	private $queryHandler;

	/** @var Config */
	private $properties;

	/** @var Config */
	private $config;

	/** @var Player[] */
	private $players = [];

	private $identifiers = [];

	/** @var Level[] */
	private $levels = [];

	/** @var Level */
	private $levelDefault = null;

	/**
	 * @return string
	 */
	public function getName(){
		return "PocketMine-MP";
	}

	/**
	 * @return bool
	 */
	public function isRunning(){
		return $this->isRunning === true;
	}

	/**
	 * @return string
	 */
	public function getPocketMineVersion(){
		return \pocketmine\VERSION;
	}

	/**
	 * @return string
	 */
	public function getCodename(){
		return \pocketmine\CODENAME;
	}

	/**
	 * @return string
	 */
	public function getVersion(){
		return \pocketmine\MINECRAFT_VERSION;
	}

	/**
	 * @return string
	 */
	public function getApiVersion(){
		return \pocketmine\API_VERSION;
	}

	/**
	 * @return string
	 */
	public function getFilePath(){
		return $this->filePath;
	}

	/**
	 * @return string
	 */
	public function getDataPath(){
		return $this->dataPath;
	}

	/**
	 * @return string
	 */
	public function getPluginPath(){
		return $this->pluginPath;
	}

	/**
	 * @return int
	 */
	public function getMaxPlayers(){
		return $this->maxPlayers;
	}

	/**
	 * @return int
	 */
	public function getPort(){
		return $this->getConfigInt("server-port", 19132);
	}

	/**
	 * @return int
	 */
	public function getViewDistance(){
		return max(56, $this->getProperty("chunk-sending.max-chunks", 256));
	}

	/**
	 * @return string
	 */
	public function getIp(){
		return $this->getConfigString("server-ip", "0.0.0.0");
	}

	/**
	 * @deprecated
	 */
	public function getServerName(){
		return $this->getConfigString("motd", "Minecraft: PE Server");
	}

	/**
	 * @return bool
	 */
	public function getAutoSave(){
		return $this->autoSave;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave($value){
		$this->autoSave = (bool) $value;
		foreach($this->getLevels() as $level){
			$level->setAutoSave($this->autoSave);
		}
	}

	/**
	 * @return string
	 */
	public function getLevelType(){
		return $this->getConfigString("level-type", "DEFAULT");
	}

	/**
	 * @return bool
	 */
	public function getGenerateStructures(){
		return $this->getConfigBoolean("generate-structures", true);
	}

	/**
	 * @return int
	 */
	public function getGamemode(){
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return bool
	 */
	public function getForceGamemode(){
		return $this->getConfigBoolean("force-gamemode", false);
	}

	/**
	 * Returns the gamemode text name
	 *
	 * @param int $mode
	 *
	 * @return string
	 */
	public static function getGamemodeString($mode){
		switch((int) $mode){
			case Player::SURVIVAL:
				return "%gameMode.survival";
			case Player::CREATIVE:
				return "%gameMode.creative";
			case Player::ADVENTURE:
				return "%gameMode.adventure";
			case Player::SPECTATOR:
				return "%gameMode.spectator";
		}

		return "UNKNOWN";
	}

	/**
	 * Parses a string and returns a gamemode integer, -1 if not found
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function getGamemodeFromString($str){
		switch(strtolower(trim($str))){
			case (string) Player::SURVIVAL:
			case "survival":
			case "s":
				return Player::SURVIVAL;

			case (string) Player::CREATIVE:
			case "creative":
			case "c":
				return Player::CREATIVE;

			case (string) Player::ADVENTURE:
			case "adventure":
			case "a":
				return Player::ADVENTURE;

			case (string) Player::SPECTATOR:
			case "spectator":
			case "view":
			case "v":
				return Player::SPECTATOR;
		}
		return -1;
	}

	/**
	 * @param string $str
	 *
	 * @return int
	 */
	public static function getDifficultyFromString($str){
		switch(strtolower(trim($str))){
			case "0":
			case "peaceful":
			case "p":
				return 0;

			case "1":
			case "easy":
			case "e":
				return 1;

			case "2":
			case "normal":
			case "n":
				return 2;

			case "3":
			case "hard":
			case "h":
				return 3;
		}
		return -1;
	}

	/**
	 * @return int
	 */
	public function getDifficulty(){
		return $this->getConfigInt("difficulty", 1);
	}

	/**
	 * @return bool
	 */
	public function hasWhitelist(){
		return $this->getConfigBoolean("white-list", false);
	}

	/**
	 * @return int
	 */
	public function getSpawnRadius(){
		return $this->getConfigInt("spawn-protection", 16);
	}

	/**
	 * @return bool
	 */
	public function getAllowFlight(){
		return $this->getConfigBoolean("allow-flight", false);
	}

	/**
	 * @return bool
	 */
	public function isHardcore(){
		return $this->getConfigBoolean("hardcore", false);
	}

	/**
	 * @return int
	 */
	public function getDefaultGamemode(){
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return string
	 */
	public function getMotd(){
		return $this->getConfigString("motd", "Minecraft: PE Server");
	}

	/**
	 * @return \ClassLoader
	 */
	public function getLoader(){
		return $this->autoloader;
	}

	/**
	 * @return \AttachableThreadedLogger
	 */
	public function getLogger(){
		return $this->logger;
	}

	/**
	 * @return EntityMetadataStore
	 */
	public function getEntityMetadata(){
		return $this->entityMetadata;
	}

	/**
	 * @return PlayerMetadataStore
	 */
	public function getPlayerMetadata(){
		return $this->playerMetadata;
	}

	/**
	 * @return LevelMetadataStore
	 */
	public function getLevelMetadata(){
		return $this->levelMetadata;
	}

	/**
	 * @return AutoUpdater
	 */
	public function getUpdater(){
		return $this->updater;
	}

	/**
	 * @return PluginManager
	 */
	public function getPluginManager(){
		return $this->pluginManager;
	}

	/**
	 * @return CraftingManager
	 */
	public function getCraftingManager(){
		return $this->craftingManager;
	}

	/**
	 * @return ServerScheduler
	 */
	public function getScheduler(){
		return $this->scheduler;
	}

	/**
	 * @return int
	 */
	public function getTick(){
		return $this->tickCounter;
	}

	/**
	 * Returns the last server TPS measure
	 *
	 * @return float
	 */
	public function getTicksPerSecond(){
		return round($this->maxTick, 2);
	}

	/**
	 * Returns the last server TPS average measure
	 *
	 * @return float
	 */
	public function getTicksPerSecondAverage(){
		return round(array_sum($this->tickAverage) / count($this->tickAverage), 2);
	}

	/**
	 * Returns the TPS usage/load in %
	 *
	 * @return float
	 */
	public function getTickUsage(){
		return round($this->maxUse * 100, 2);
	}

	/**
	 * Returns the TPS usage/load average in %
	 *
	 * @return float
	 */
	public function getTickUsageAverage(){
		return round((array_sum($this->useAverage) / count($this->useAverage)) * 100, 2);
	}


	/**
	 * @deprecated
	 *
	 * @param     $address
	 * @param int $timeout
	 */
	public function blockAddress($address, $timeout = 300){
		$this->network->blockAddress($address, $timeout);
	}

	/**
	 * @deprecated
	 *
	 * @param $address
	 * @param $port
	 * @param $payload
	 */
	public function sendPacket($address, $port, $payload){
		$this->network->sendPacket($address, $port, $payload);
	}

	/**
	 * @deprecated
	 *
	 * @return SourceInterface[]
	 */
	public function getInterfaces(){
		return $this->network->getInterfaces();
	}

	/**
	 * @deprecated
	 *
	 * @param SourceInterface $interface
	 */
	public function addInterface(SourceInterface $interface){
		$this->network->registerInterface($interface);
	}

	/**
	 * @deprecated
	 *
	 * @param SourceInterface $interface
	 */
	public function removeInterface(SourceInterface $interface){
		$interface->shutdown();
		$this->network->unregisterInterface($interface);
	}

	/**
	 * @return SimpleCommandMap
	 */
	public function getCommandMap(){
		return $this->commandMap;
	}

	/**
	 * @return Player[]
	 */
	public function getOnlinePlayers(){
		return $this->players;
	}

	public function addRecipe(Recipe $recipe){
		$this->craftingManager->registerRecipe($recipe);
	}

	/**
	 * @param string $name
	 *
	 * @return OfflinePlayer|Player
	 */
	public function getOfflinePlayer($name){
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);

		if($result === null){
			$result = new OfflinePlayer($this, $name);
		}

		return $result;
	}

	/**
	 * @param string $name
	 *
	 * @return Compound
	 */
	public function getOfflinePlayerData($name){
		$name = strtolower($name);
		$path = $this->getDataPath() . "players/";
		if(file_exists($path . "$name.dat")){
			try{
				$nbt = new NBT(NBT::BIG_ENDIAN);
				$nbt->readCompressed(file_get_contents($path . "$name.dat"));

				return $nbt->getData();
			}catch(\Exception $e){ //zlib decode error / corrupt data
				rename($path . "$name.dat", $path . "$name.dat.bak");
				$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerCorrupted", [$name]));
			}
		}else{
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerNotFound", [$name]));
		}
		$spawn = $this->getDefaultLevel()->getSafeSpawn();
		$nbt = new Compound("", [
			new Long("firstPlayed", floor(microtime(true) * 1000)),
			new Long("lastPlayed", floor(microtime(true) * 1000)),
			new Enum("Pos", [
				new Double(0, $spawn->x),
				new Double(1, $spawn->y),
				new Double(2, $spawn->z)
			]),
			new String("Level", $this->getDefaultLevel()->getName()),
			//new String("SpawnLevel", $this->getDefaultLevel()->getName()),
			//new Int("SpawnX", (int) $spawn->x),
			//new Int("SpawnY", (int) $spawn->y),
			//new Int("SpawnZ", (int) $spawn->z),
			//new Byte("SpawnForced", 1), //TODO
			new Enum("Inventory", []),
			new Compound("Achievements", []),
			new Int("playerGameType", $this->getGamemode()),
			new Enum("Motion", [
				new Double(0, 0.0),
				new Double(1, 0.0),
				new Double(2, 0.0)
			]),
			new Enum("Rotation", [
				new Float(0, 0.0),
				new Float(1, 0.0)
			]),
			new Float("FallDistance", 0.0),
			new Short("Fire", 0),
			new Short("Air", 0),
			new Byte("OnGround", 1),
			new Byte("Invulnerable", 0),
			new String("NameTag", $name),
		]);
		$nbt->Pos->setTagType(NBT::TAG_Double);
		$nbt->Inventory->setTagType(NBT::TAG_Compound);
		$nbt->Motion->setTagType(NBT::TAG_Double);
		$nbt->Rotation->setTagType(NBT::TAG_Float);

		if(file_exists($path . "$name.yml")){ //Importing old PocketMine-MP files
			$data = new Config($path . "$name.yml", Config::YAML, []);
			$nbt["playerGameType"] = (int) $data->get("gamemode");
			$nbt["Level"] = $data->get("position")["level"];
			$nbt["Pos"][0] = $data->get("position")["x"];
			$nbt["Pos"][1] = $data->get("position")["y"];
			$nbt["Pos"][2] = $data->get("position")["z"];
			$nbt["SpawnLevel"] = $data->get("spawn")["level"];
			$nbt["SpawnX"] = (int) $data->get("spawn")["x"];
			$nbt["SpawnY"] = (int) $data->get("spawn")["y"];
			$nbt["SpawnZ"] = (int) $data->get("spawn")["z"];
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerOld", [$name]));
			foreach($data->get("inventory") as $slot => $item){
				if(count($item) === 3){
					$nbt->Inventory[$slot + 9] = new Compound("", [
						new Short("id", $item[0]),
						new Short("Damage", $item[1]),
						new Byte("Count", $item[2]),
						new Byte("Slot", $slot + 9),
						new Byte("TrueSlot", $slot + 9)
					]);
				}
			}
			foreach($data->get("hotbar") as $slot => $itemSlot){
				if(isset($nbt->Inventory[$itemSlot + 9])){
					$item = $nbt->Inventory[$itemSlot + 9];
					$nbt->Inventory[$slot] = new Compound("", [
						new Short("id", $item["id"]),
						new Short("Damage", $item["Damage"]),
						new Byte("Count", $item["Count"]),
						new Byte("Slot", $slot),
						new Byte("TrueSlot", $item["TrueSlot"])
					]);
				}
			}
			foreach($data->get("armor") as $slot => $item){
				if(count($item) === 2){
					$nbt->Inventory[$slot + 100] = new Compound("", [
						new Short("id", $item[0]),
						new Short("Damage", $item[1]),
						new Byte("Count", 1),
						new Byte("Slot", $slot + 100)
					]);
				}
			}
			foreach($data->get("achievements") as $achievement => $status){
				$nbt->Achievements[$achievement] = new Byte($achievement, $status == true ? 1 : 0);
			}
			unlink($path . "$name.yml");
		}
		$this->saveOfflinePlayerData($name, $nbt);

		return $nbt;

	}

	/**
	 * @param string   $name
	 * @param Compound $nbtTag
	 */
	public function saveOfflinePlayerData($name, Compound $nbtTag){
		$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->setData($nbtTag);
			file_put_contents($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed());
		}catch(\Exception $e){
			$this->logger->critical($this->getLanguage()->translateString("pocketmine.data.saveError", [$name, $e->getMessage()]));
			if(\pocketmine\DEBUG > 1 and $this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
		}
	}

	/**
	 * @param string $name
	 *
	 * @return Player
	 */
	public function getPlayer($name){
		$found = null;
		$name = strtolower($name);
		$delta = PHP_INT_MAX;
		foreach($this->getOnlinePlayers() as $player){
			if(stripos($player->getName(), $name) === 0){
				$curDelta = strlen($player->getName()) - strlen($name);
				if($curDelta < $delta){
					$found = $player;
					$delta = $curDelta;
				}
				if($curDelta === 0){
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * @param string $name
	 *
	 * @return Player
	 */
	public function getPlayerExact($name){
		$name = strtolower($name);
		foreach($this->getOnlinePlayers() as $player){
			if(strtolower($player->getName()) === $name){
				return $player;
			}
		}

		return null;
	}

	/**
	 * @param string $partialName
	 *
	 * @return Player[]
	 */
	public function matchPlayer($partialName){
		$partialName = strtolower($partialName);
		$matchedPlayers = [];
		foreach($this->getOnlinePlayers() as $player){
			if(strtolower($player->getName()) === $partialName){
				$matchedPlayers = [$player];
				break;
			}elseif(stripos($player->getName(), $partialName) !== false){
				$matchedPlayers[] = $player;
			}
		}

		return $matchedPlayers;
	}

	/**
	 * @param Player $player
	 */
	public function removePlayer(Player $player){
		if(isset($this->identifiers[$hash = spl_object_hash($player)])){
			$identifier = $this->identifiers[$hash];
			unset($this->players[$identifier]);
			unset($this->identifiers[$hash]);
			return;
		}

		foreach($this->players as $identifier => $p){
			if($player === $p){
				unset($this->players[$identifier]);
				unset($this->identifiers[spl_object_hash($player)]);
				break;
			}
		}
	}

	/**
	 * @return Level[]
	 */
	public function getLevels(){
		return $this->levels;
	}

	/**
	 * @return Level
	 */
	public function getDefaultLevel(){
		return $this->levelDefault;
	}

	/**
	 * Sets the default level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 *
	 * @param Level $level
	 */
	public function setDefaultLevel($level){
		if($level === null or ($this->isLevelLoaded($level->getFolderName()) and $level !== $this->levelDefault)){
			$this->levelDefault = $level;
		}
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelLoaded($name){
		return $this->getLevelByName($name) instanceof Level;
	}

	/**
	 * @param int $levelId
	 *
	 * @return Level
	 */
	public function getLevel($levelId){
		if(isset($this->levels[$levelId])){
			return $this->levels[$levelId];
		}

		return null;
	}

	/**
	 * @param $name
	 *
	 * @return Level
	 */
	public function getLevelByName($name){
		foreach($this->getLevels() as $level){
			if($level->getFolderName() === $name){
				return $level;
			}
		}

		return null;
	}

	/**
	 * @param Level $level
	 * @param bool  $forceUnload
	 *
	 * @return bool
	 */
	public function unloadLevel(Level $level, $forceUnload = false){
		if($level->unload($forceUnload) === true){
			unset($this->levels[$level->getId()]);

			return true;
		}

		return false;
	}

	/**
	 * Loads a level from the data directory
	 *
	 * @param string $name
	 *
	 * @return bool
	 *
	 * @throws LevelException
	 */
	public function loadLevel($name){
		if(trim($name) === ""){
			throw new LevelException("Invalid empty level name");
		}
		if($this->isLevelLoaded($name)){
			return true;
		}elseif(!$this->isLevelGenerated($name)){
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.level.notFound", [$name]));

			return false;
		}

		$path = $this->getDataPath() . "worlds/" . $name . "/";

		$provider = LevelProviderManager::getProvider($path);

		if($provider === null){
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, "Unknown provider"]));

			return false;
		}
		//$entities = new Config($path."entities.yml", Config::YAML);
		//if(file_exists($path . "tileEntities.yml")){
		//	@rename($path . "tileEntities.yml", $path . "tiles.yml");
		//}

		try{
			$level = new Level($this, $name, $path, $provider);
		}catch(\Exception $e){

			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, $e->getMessage()]));
			if($this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
			return false;
		}

		$this->levels[$level->getId()] = $level;

		$level->initLevel();

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));

		$level->setTickRate($this->baseTickRate);

		/*foreach($entities->getAll() as $entity){
			if(!isset($entity["id"])){
				break;
			}
			if($entity["id"] === 64){ //Item Drop
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_ITEM, $entity["Item"]["id"], array(
					"meta" => $entity["Item"]["Damage"],
					"stack" => $entity["Item"]["Count"],
					"x" => $entity["Pos"][0],
					"y" => $entity["Pos"][1],
					"z" => $entity["Pos"][2],
					"yaw" => $entity["Rotation"][0],
					"pitch" => $entity["Rotation"][1],
				));
			}elseif($entity["id"] === FALLING_SAND){
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_FALLING, $entity["id"], $entity);
				$e->setPosition(new Vector3($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2]), $entity["Rotation"][0], $entity["Rotation"][1]);
				$e->setHealth($entity["Health"]);
			}elseif($entity["id"] === OBJECT_PAINTING or $entity["id"] === OBJECT_ARROW){ //Painting
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_OBJECT, $entity["id"], $entity);
				$e->setPosition(new Vector3($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2]), $entity["Rotation"][0], $entity["Rotation"][1]);
				$e->setHealth(1);
			}else{
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_MOB, $entity["id"], $entity);
				$e->setPosition(new Vector3($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2]), $entity["Rotation"][0], $entity["Rotation"][1]);
				$e->setHealth($entity["Health"]);
			}
		}*/

		/*if(file_exists($path . "tiles.yml")){
			$tiles = new Config($path . "tiles.yml", Config::YAML);
			foreach($tiles->getAll() as $tile){
				if(!isset($tile["id"])){
					continue;
				}
				$level->loadChunk($tile["x"] >> 4, $tile["z"] >> 4);

				$nbt = new Compound(false, []);
				foreach($tile as $index => $data){
					switch($index){
						case "Items":
							$tag = new Enum("Items", []);
							$tag->setTagType(NBT::TAG_Compound);
							foreach($data as $slot => $fields){
								$tag[(int) $slot] = new Compound(false, array(
									"Count" => new Byte("Count", $fields["Count"]),
									"Slot" => new Short("Slot", $fields["Slot"]),
									"Damage" => new Short("Damage", $fields["Damage"]),
									"id" => new String("id", $fields["id"])
								));
							}
							$nbt["Items"] = $tag;
							break;

						case "id":
						case "Text1":
						case "Text2":
						case "Text3":
						case "Text4":
							$nbt[$index] = new String($index, $data);
							break;

						case "x":
						case "y":
						case "z":
						case "pairx":
						case "pairz":
							$nbt[$index] = new Int($index, $data);
							break;

						case "BurnTime":
						case "CookTime":
						case "MaxTime":
							$nbt[$index] = new Short($index, $data);
							break;
					}
				}
				switch($tile["id"]){
					case Tile::FURNACE:
						new Furnace($level, $nbt);
						break;
					case Tile::CHEST:
						new Chest($level, $nbt);
						break;
					case Tile::SIGN:
						new Sign($level, $nbt);
						break;
				}
			}
			unlink($path . "tiles.yml");
			$level->save(true, true);
		}*/

		return true;
	}

	/**
	 * Generates a new level if it does not exists
	 *
	 * @param string $name
	 * @param int    $seed
	 * @param string $generator Class name that extends pocketmine\level\generator\Noise
	 * @param array  $options
	 *
	 * @return bool
	 */
	public function generateLevel($name, $seed = null, $generator = null, $options = []){
		if(trim($name) === "" or $this->isLevelGenerated($name)){
			return false;
		}

		$seed = $seed === null ? Binary::readInt(@Utils::getRandomBytes(4, false)) : (int) $seed;

		if(!isset($options["preset"])){
			$options["preset"] = $this->getConfigString("generator-settings", "");
		}

		if(!($generator !== null and class_exists($generator, true) and is_subclass_of($generator, Generator::class))){
			$generator = Generator::getGenerator($this->getLevelType());
		}

		if(($provider = LevelProviderManager::getProviderByName($providerName = $this->getProperty("level-settings.default-format", "mcregion"))) === null){
			$provider = LevelProviderManager::getProviderByName($providerName = "mcregion");
		}

		try{
			$path = $this->getDataPath() . "worlds/" . $name . "/";
			/** @var \pocketmine\level\format\LevelProvider $provider */
			$provider::generate($path, $name, $seed, $generator, $options);

			$level = new Level($this, $name, $path, $provider);
			$this->levels[$level->getId()] = $level;

			$level->initLevel();

			$level->setTickRate($this->baseTickRate);
		}catch(\Exception $e){
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.generateError", [$name, $e->getMessage()]));
			if($this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
			return false;
		}

		$this->getPluginManager()->callEvent(new LevelInitEvent($level));

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));

		$this->getLogger()->notice($this->getLanguage()->translateString("pocketmine.level.backgroundGeneration", [$name]));

		$centerX = $level->getSpawnLocation()->getX() >> 4;
		$centerZ = $level->getSpawnLocation()->getZ() >> 4;

		$order = [];

		for($X = -4; $X <= 4; ++$X){
			for($Z = -4; $Z <= 4; ++$Z){
				$distance = $X ** 2 + $Z ** 2;
				$chunkX = $X + $centerX;
				$chunkZ = $Z + $centerZ;
				$index = Level::chunkHash($chunkX, $chunkZ);
				$order[$index] = $distance;
			}
		}

		asort($order);

		$chunkX = $chunkZ = null;

		foreach($order as $index => $distance){
			Level::getXZ($index, $chunkX, $chunkZ);
			$level->generateChunk($chunkX, $chunkZ, true);
		}

		return true;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelGenerated($name){
		if(trim($name) === ""){
			return false;
		}
		$path = $this->getDataPath() . "worlds/" . $name . "/";
		if(!($this->getLevelByName($name) instanceof Level)){

			if(LevelProviderManager::getProvider($path) === null){
				return false;
			}
			/*if(file_exists($path)){
				$level = new LevelImport($path);
				if($level->import() === false){ //Try importing a world
					return false;
				}
			}else{
				return false;
			}*/
		}

		return true;
	}

	/**
	 * @param string $variable
	 * @param string $defaultValue
	 *
	 * @return string
	 */
	public function getConfigString($variable, $defaultValue = ""){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (string) $v[$variable];
		}

		return $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getProperty($variable, $defaultValue = null){
		$value = $this->config->getNested($variable);

		return $value === null ? $defaultValue : $value;
	}

	/**
	 * @param string $variable
	 * @param string $value
	 */
	public function setConfigString($variable, $value){
		$this->properties->set($variable, $value);
	}

	/**
	 * @param string $variable
	 * @param int    $defaultValue
	 *
	 * @return int
	 */
	public function getConfigInt($variable, $defaultValue = 0){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (int) $v[$variable];
		}

		return $this->properties->exists($variable) ? (int) $this->properties->get($variable) : (int) $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param int    $value
	 */
	public function setConfigInt($variable, $value){
		$this->properties->set($variable, (int) $value);
	}

	/**
	 * @param string  $variable
	 * @param boolean $defaultValue
	 *
	 * @return boolean
	 */
	public function getConfigBoolean($variable, $defaultValue = false){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			$value = $v[$variable];
		}else{
			$value = $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
		}

		if(is_bool($value)){
			return $value;
		}
		switch(strtolower($value)){
			case "on":
			case "true":
			case "1":
			case "yes":
				return true;
		}

		return false;
	}

	/**
	 * @param string $variable
	 * @param bool   $value
	 */
	public function setConfigBool($variable, $value){
		$this->properties->set($variable, $value == true ? "1" : "0");
	}

	/**
	 * @param string $name
	 *
	 * @return PluginIdentifiableCommand
	 */
	public function getPluginCommand($name){
		if(($command = $this->commandMap->getCommand($name)) instanceof PluginIdentifiableCommand){
			return $command;
		}else{
			return null;
		}
	}

	/**
	 * @return BanList
	 */
	public function getNameBans(){
		return $this->banByName;
	}

	/**
	 * @return BanList
	 */
	public function getIPBans(){
		return $this->banByIP;
	}

	/**
	 * @param string $name
	 */
	public function addOp($name){
		$this->operators->set(strtolower($name), true);

		if(($player = $this->getPlayerExact($name)) instanceof Player){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function removeOp($name){
		$this->operators->remove(strtolower($name));

		if(($player = $this->getPlayerExact($name)) instanceof Player){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function addWhitelist($name){
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save();
	}

	/**
	 * @param string $name
	 */
	public function removeWhitelist($name){
		$this->whitelist->remove(strtolower($name));
		$this->whitelist->save();
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isWhitelisted($name){
		return !$this->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isOp($name){
		return $this->operators->exists($name, true);
	}

	/**
	 * @return Config
	 */
	public function getWhitelisted(){
		return $this->whitelist;
	}

	/**
	 * @return Config
	 */
	public function getOps(){
		return $this->operators;
	}

	public function reloadWhitelist(){
		$this->whitelist->reload();
	}

	/**
	 * @return string[]
	 */
	public function getCommandAliases(){
		$section = $this->getProperty("aliases");
		$result = [];
		if(is_array($section)){
			foreach($section as $key => $value){
				$commands = [];
				if(is_array($value)){
					$commands = $value;
				}else{
					$commands[] = $value;
				}

				$result[$key] = $commands;
			}
		}

		return $result;
	}

	/**
	 * @return Server
	 */
	public static function getInstance(){
		return self::$instance;
	}

	/**
	 * @param \ClassLoader    $autoloader
	 * @param \ThreadedLogger $logger
	 * @param string          $filePath
	 * @param string          $dataPath
	 * @param string          $pluginPath
	 */
	public function __construct(\ClassLoader $autoloader, \ThreadedLogger $logger, $filePath, $dataPath, $pluginPath){
		self::$instance = $this;

		$this->autoloader = $autoloader;
		$this->logger = $logger;
		$this->filePath = $filePath;
		if(!file_exists($dataPath . "worlds/")){
			mkdir($dataPath . "worlds/", 0777);
		}

		if(!file_exists($dataPath . "players/")){
			mkdir($dataPath . "players/", 0777);
		}

		if(!file_exists($pluginPath)){
			mkdir($pluginPath, 0777);
		}

		$this->dataPath = realpath($dataPath) . DIRECTORY_SEPARATOR;
		$this->pluginPath = realpath($pluginPath) . DIRECTORY_SEPARATOR;

		$this->console = new CommandReader();

		$version = new VersionString($this->getPocketMineVersion());

		$this->logger->info("Loading pocketmine.yml...");
		if(!file_exists($this->dataPath . "pocketmine.yml")){
			$content = file_get_contents($this->filePath . "src/pocketmine/resources/pocketmine.yml");
			if($version->isDev()){
				$content = str_replace("preferred-channel: stable", "preferred-channel: beta", $content);
			}
			@file_put_contents($this->dataPath . "pocketmine.yml", $content);
		}
		$this->config = new Config($this->dataPath . "pocketmine.yml", Config::YAML, []);

		$this->logger->info("Loading server properties...");
		$this->properties = new Config($this->dataPath . "server.properties", Config::PROPERTIES, [
			"motd" => "Minecraft: PE Server",
			"server-port" => 19132,
			"white-list" => false,
			"announce-player-achievements" => true,
			"spawn-protection" => 16,
			"max-players" => 20,
			"allow-flight" => false,
			"spawn-animals" => true,
			"spawn-mobs" => true,
			"gamemode" => 0,
			"force-gamemode" => false,
			"hardcore" => false,
			"pvp" => true,
			"difficulty" => 1,
			"generator-settings" => "",
			"level-name" => "world",
			"level-seed" => "",
			"level-type" => "DEFAULT",
			"enable-query" => true,
			"enable-rcon" => false,
			"rcon.password" => substr(base64_encode(@Utils::getRandomBytes(20, false)), 3, 10),
			"auto-save" => true,
		]);

		$this->forceLanguage = $this->getProperty("settings.force-language", false);
		$this->baseLang = new BaseLang($this->getProperty("settings.language", BaseLang::FALLBACK_LANGUAGE));
		$this->logger->info($this->getLanguage()->translateString("language.selected", [$this->getLanguage()->getName(), $this->getLanguage()->getLang()]));

		$this->memoryManager = new MemoryManager($this);

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.start", [TextFormat::AQUA . $this->getVersion()]));

		if(($poolSize = $this->getProperty("settings.async-workers", "auto")) === "auto"){
			$poolSize = ServerScheduler::$WORKERS;
			$processors = Utils::getCoreCount();

			if($processors > 0){
				$poolSize = max(2, $processors);
			}
		}

		ServerScheduler::$WORKERS = $poolSize;

		if($this->getProperty("network.batch-threshold", 256) >= 0){
			Network::$BATCH_THRESHOLD = (int) $this->getProperty("network.batch-threshold", 256);
		}else{
			Network::$BATCH_THRESHOLD = -1;
		}
		$this->networkCompressionLevel = $this->getProperty("network.compression-level", 7);
		$this->networkCompressionAsync = $this->getProperty("network.async-compression", true);

		$this->autoTickRate = (bool) $this->getProperty("level-settings.auto-tick-rate", true);
		$this->autoTickRateLimit = (int) $this->getProperty("level-settings.auto-tick-rate-limit", 20);
		$this->alwaysTickPlayers = (int) $this->getProperty("level-settings.always-tick-players", false);
		$this->baseTickRate = (int) $this->getProperty("level-settings.base-tick-rate", 1);

		$this->scheduler = new ServerScheduler();

		if($this->getConfigBoolean("enable-rcon", false) === true){
			$this->rcon = new RCON($this, $this->getConfigString("rcon.password", ""), $this->getConfigInt("rcon.port", $this->getPort()), ($ip = $this->getIp()) != "" ? $ip : "0.0.0.0", $this->getConfigInt("rcon.threads", 1), $this->getConfigInt("rcon.clients-per-thread", 50));
		}

		$this->entityMetadata = new EntityMetadataStore();
		$this->playerMetadata = new PlayerMetadataStore();
		$this->levelMetadata = new LevelMetadataStore();

		$this->operators = new Config($this->dataPath . "ops.txt", Config::ENUM);
		$this->whitelist = new Config($this->dataPath . "white-list.txt", Config::ENUM);
		if(file_exists($this->dataPath . "banned.txt") and !file_exists($this->dataPath . "banned-players.txt")){
			@rename($this->dataPath . "banned.txt", $this->dataPath . "banned-players.txt");
		}
		@touch($this->dataPath . "banned-players.txt");
		$this->banByName = new BanList($this->dataPath . "banned-players.txt");
		$this->banByName->load();
		@touch($this->dataPath . "banned-ips.txt");
		$this->banByIP = new BanList($this->dataPath . "banned-ips.txt");
		$this->banByIP->load();

		$this->maxPlayers = $this->getConfigInt("max-players", 20);
		$this->setAutoSave($this->getConfigBoolean("auto-save", true));

		if($this->getConfigString("memory-limit", -1) !== false){
			$this->logger->notice("The memory-limit setting has been deprecated.");
			$this->logger->notice("There are new memory settings on pocketmine.yml to tune memory and events.");
			$this->logger->notice("You can also reduce the amount of threads and chunks loaded control the memory usage.");
		}

		if($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3){
			$this->setConfigInt("difficulty", 3);
		}

		define("pocketmine\\DEBUG", (int) $this->getProperty("debug.level", 1));
		if($this->logger instanceof MainLogger){
			$this->logger->setLogDebug(\pocketmine\DEBUG > 1);
		}

		if(\pocketmine\DEBUG >= 0){
			@cli_set_process_title($this->getName() . " " . $this->getPocketMineVersion());
		}

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.networkStart", [$this->getIp() === "" ? "*" : $this->getIp(), $this->getPort()]));
		define("BOOTUP_RANDOM", @Utils::getRandomBytes(16));
		$this->serverID = Utils::getServerUniqueId($this->getIp() . $this->getPort());

		$this->network = new Network($this);
		$this->network->setName($this->getMotd());
		$this->network->registerInterface(new RakLibInterface($this));


		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.info", [
			$this->getName(),
			($version->isDev() ? TextFormat::YELLOW : "") . $version->get(true) . TextFormat::WHITE,
			$this->getCodename(),
			$this->getApiVersion()
		]));
		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.license", [$this->getName()]));

		PluginManager::$pluginParentTimer = new TimingsHandler("** Plugins");
		Timings::init();

		$this->consoleSender = new ConsoleCommandSender();
		$this->commandMap = new SimpleCommandMap($this);

		$this->registerEntities();
		$this->registerTiles();

		InventoryType::init();
		Block::init();
		Item::init();
		Biome::init();
		Effect::init();
		/** TODO: @deprecated */
		TextWrapper::init();
		$this->craftingManager = new CraftingManager();

		$this->pluginManager = new PluginManager($this, $this->commandMap);
		$this->pluginManager->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this->consoleSender);
		$this->pluginManager->setUseTimings($this->getProperty("settings.enable-profiling", false));
		$this->pluginManager->registerInterface(PharPluginLoader::class);

		set_exception_handler([$this, "exceptionHandler"]);
		register_shutdown_function([$this, "crashDump"]);

		$this->pluginManager->loadPlugins($this->pluginPath);

		$this->updater = new AutoUpdater($this, $this->getProperty("auto-updater.host", "www.pocketmine.net"));

		$this->enablePlugins(PluginLoadOrder::STARTUP);

		LevelProviderManager::addProvider($this, Anvil::class);
		LevelProviderManager::addProvider($this, McRegion::class);
		if(extension_loaded("leveldb")){
			$this->logger->debug($this->getLanguage()->translateString("pocketmine.debug.enable"));
			LevelProviderManager::addProvider($this, LevelDB::class);
		}


		Generator::addGenerator(Flat::class, "flat");
		Generator::addGenerator(Normal::class, "normal");
		Generator::addGenerator(Normal::class, "default");

		foreach((array) $this->getProperty("worlds", []) as $name => $worldSetting){
			if($this->loadLevel($name) === false){
				$seed = $this->getProperty("worlds.$name.seed", time());
				$options = explode(":", $this->getProperty("worlds.$name.generator", Generator::getGenerator("default")));
				$generator = Generator::getGenerator(array_shift($options));
				if(count($options) > 0){
					$options = [
						"preset" => implode(":", $options),
					];
				}else{
					$options = [];
				}

				$this->generateLevel($name, $seed, $generator, $options);
			}
		}

		if($this->getDefaultLevel() === null){
			$default = $this->getConfigString("level-name", "world");
			if(trim($default) == ""){
				$this->getLogger()->warning("level-name cannot be null, using default");
				$default = "world";
				$this->setConfigString("level-name", "world");
			}
			if($this->loadLevel($default) === false){
				$seed = $this->getConfigInt("level-seed", time());
				$this->generateLevel($default, $seed === 0 ? time() : $seed);
			}

			$this->setDefaultLevel($this->getLevelByName($default));
		}


		$this->properties->save();

		if(!($this->getDefaultLevel() instanceof Level)){
			$this->getLogger()->emergency($this->getLanguage()->translateString("pocketmine.level.defaultError"));
			$this->forceShutdown();

			return;
		}

		if($this->getAutoSave() and $this->getProperty("ticks-per.autosave", 6000) > 0){
			$this->autoSaveTicks = (int) $this->getProperty("ticks-per.autosave", 6000);
		}

		$this->enablePlugins(PluginLoadOrder::POSTWORLD);

		$this->start();
	}

	/**
	 * @param               $message
	 * @param Player[]      $recipients
	 *
	 * @return int
	 */
	public function broadcastMessage($message, $recipients = null){
		if(!is_array($recipients)){
			return $this->broadcast($message, self::BROADCAST_CHANNEL_USERS);
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}
	}

	/**
	 * @param string $message
	 * @param string $permissions
	 *
	 * @return int
	 */
	public function broadcast($message, $permissions){
		/** @var CommandSender[] $recipients */
		$recipients = [];
		foreach(explode(";", $permissions) as $permission){
			foreach($this->pluginManager->getPermissionSubscriptions($permission) as $permissible){
				if($permissible instanceof CommandSender and $permissible->hasPermission($permission)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * Broadcasts a Minecraft packet to a list of players
	 *
	 * @param Player[]   $players
	 * @param DataPacket $packet
	 */
	public static function broadcastPacket(array $players, DataPacket $packet){
		$packet->encode();
		$packet->isEncoded = true;
		if(Network::$BATCH_THRESHOLD >= 0 and strlen($packet->buffer) >= Network::$BATCH_THRESHOLD){
			Server::getInstance()->batchPackets($players, [$packet->buffer], false, $packet->getChannel());
			return;
		}

		foreach($players as $player){
			$player->dataPacket($packet);
		}
		if(isset($packet->__encapsulatedPacket)){
			unset($packet->__encapsulatedPacket);
		}
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param Player[]            $players
	 * @param DataPacket[]|string $packets
	 * @param bool                 $forceSync
	 * @param int                 $channel
	 */
	public function batchPackets(array $players, array $packets, $forceSync = false, $channel = 0){
		$str = "";

		foreach($packets as $p){
			if($p instanceof DataPacket){
				if(!$p->isEncoded){
					$p->encode();
				}
				$str .= $p->buffer;
			}else{
				$str .= $p;
			}
		}

		$targets = [];
		foreach($players as $p){
			$targets[] = $this->identifiers[spl_object_hash($p)];
		}

		if(!$forceSync and $this->networkCompressionAsync){
			$task = new CompressBatchedTask($str, $targets, $this->networkCompressionLevel, $channel);
			$this->getScheduler()->scheduleAsyncTask($task);
		}else{
			$this->broadcastPacketsCallback(zlib_encode($str, ZLIB_ENCODING_DEFLATE, $this->networkCompressionLevel), $targets, $channel);
		}
	}

	public function broadcastPacketsCallback($data, array $identifiers, $channel = 0){
		$pk = new BatchPacket();
		$pk->setChannel($channel);
		$pk->payload = $data;
		$pk->encode();
		$pk->isEncoded = true;

		foreach($identifiers as $i){
			if(isset($this->players[$i])){
				$this->players[$i]->dataPacket($pk);
			}
		}
	}


	/**
	 * @param int $type
	 */
	public function enablePlugins($type){
		foreach($this->pluginManager->getPlugins() as $plugin){
			if(!$plugin->isEnabled() and $plugin->getDescription()->getOrder() === $type){
				$this->enablePlugin($plugin);
			}
		}

		if($type === PluginLoadOrder::POSTWORLD){
			$this->commandMap->registerServerAliases();
			DefaultPermissions::registerCorePermissions();
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function enablePlugin(Plugin $plugin){
		$this->pluginManager->enablePlugin($plugin);
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @deprecated
	 */
	public function loadPlugin(Plugin $plugin){
		$this->enablePlugin($plugin);
	}

	public function disablePlugins(){
		$this->pluginManager->disablePlugins();
	}

	public function checkConsole(){
		Timings::$serverCommandTimer->startTiming();
		if(($line = $this->console->getLine()) !== null){
			$this->pluginManager->callEvent($ev = new ServerCommandEvent($this->consoleSender, $line));
			if(!$ev->isCancelled()){
				$this->dispatchCommand($ev->getSender(), $ev->getCommand());
			}
		}
		Timings::$serverCommandTimer->stopTiming();
	}

	/**
	 * Executes a command from a CommandSender
	 *
	 * @param CommandSender $sender
	 * @param string        $commandLine
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function dispatchCommand(CommandSender $sender, $commandLine){
		if(!($sender instanceof CommandSender)){
			throw new ServerException("CommandSender is not valid");
		}

		if($this->commandMap->dispatch($sender, $commandLine)){
			return true;
		}


		$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.notFound"));

		return false;
	}

	public function reload(){
		$this->logger->info("Saving levels...");

		foreach($this->levels as $level){
			$level->save();
		}

		$this->pluginManager->disablePlugins();
		$this->pluginManager->clearPlugins();
		$this->commandMap->clearCommands();

		$this->logger->info("Reloading properties...");
		$this->properties->reload();
		$this->maxPlayers = $this->getConfigInt("max-players", 20);

		if($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3){
			$this->setConfigInt("difficulty", 3);
		}

		$this->banByIP->load();
		$this->banByName->load();
		$this->reloadWhitelist();
		$this->operators->reload();

		foreach($this->getIPBans()->getEntries() as $entry){
			$this->blockAddress($entry->getName(), -1);
		}

		$this->pluginManager->registerInterface(PharPluginLoader::class);
		$this->pluginManager->loadPlugins($this->pluginPath);
		$this->enablePlugins(PluginLoadOrder::STARTUP);
		$this->enablePlugins(PluginLoadOrder::POSTWORLD);
		TimingsHandler::reload();
	}

	/**
	 * Shutdowns the server correctly
	 */
	public function shutdown(){
		$this->isRunning = false;
		gc_collect_cycles();
	}

	public function forceShutdown(){
		if($this->hasStopped){
			return;
		}

		try{
			$this->hasStopped = true;

			$this->shutdown();
			if($this->rcon instanceof RCON){
				$this->rcon->stop();
			}

			if($this->getProperty("network.upnp-forwarding", false) === true){
				$this->logger->info("[UPnP] Removing port forward...");
				UPnP::RemovePortForward($this->getPort());
			}

			$this->getLogger()->debug("Disabling all plugins");
			$this->pluginManager->disablePlugins();

			foreach($this->players as $player){
				$player->close($player->getLeaveMessage(), $this->getProperty("settings.shutdown-message", "Server closed"));
			}

			$this->getLogger()->debug("Unloading all levels");
			foreach($this->getLevels() as $level){
				$this->unloadLevel($level, true);
			}

			$this->getLogger()->debug("Removing event handlers");
			HandlerList::unregisterAll();

			$this->getLogger()->debug("Stopping all tasks");
			$this->scheduler->cancelAllTasks();
			$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);

			$this->getLogger()->debug("Saving properties");
			$this->properties->save();

			$this->getLogger()->debug("Closing console");
			$this->console->kill();

			$this->getLogger()->debug("Stopping network interfaces");
			foreach($this->network->getInterfaces() as $interface){
				$interface->shutdown();
				$this->network->unregisterInterface($interface);
			}
		}catch(\Exception $e){
			$this->logger->emergency("Crashed while crashing, killing process");
			@kill(getmypid());
		}

	}

	/**
	 * Starts the PocketMine-MP server and starts processing ticks and packets
	 */
	public function start(){
		if($this->getConfigBoolean("enable-query", true) === true){
			$this->queryHandler = new QueryHandler();
		}

		foreach($this->getIPBans()->getEntries() as $entry){
			$this->network->blockAddress($entry->getName(), -1);
		}

		if($this->getProperty("settings.send-usage", true) !== false){
			$this->sendUsageTicker = 6000;
			$this->sendUsage();
		}


		if($this->getProperty("network.upnp-forwarding", false) == true){
			$this->logger->info("[UPnP] Trying to port forward...");
			UPnP::PortForward($this->getPort());
		}

		$this->tickCounter = 0;

		if(function_exists("pcntl_signal")){
			pcntl_signal(SIGTERM, [$this, "handleSignal"]);
			pcntl_signal(SIGINT, [$this, "handleSignal"]);
			pcntl_signal(SIGHUP, [$this, "handleSignal"]);
			$this->dispatchSignals = true;
		}

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.defaultGameMode", [self::getGamemodeString($this->getGamemode())]));

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.startFinished", [round(microtime(true) - \pocketmine\START_TIME, 3)]));

		$this->tickProcessor();
		$this->forceShutdown();

		gc_collect_cycles();
	}

	public function handleSignal($signo){
		if($signo === SIGTERM or $signo === SIGINT or $signo === SIGHUP){
			$this->shutdown();
		}
	}

	public function exceptionHandler(\Exception $e, $trace = null){
		if($e === null){
			return;
		}

		global $lastError;

		if($trace === null){
			$trace = $e->getTrace();
		}

		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errno = $e->getCode();
		$errline = $e->getLine();

		$type = ($errno === E_ERROR or $errno === E_USER_ERROR) ? \LogLevel::ERROR : (($errno === E_USER_WARNING or $errno === E_WARNING) ? \LogLevel::WARNING : \LogLevel::NOTICE);
		if(($pos = strpos($errstr, "\n")) !== false){
			$errstr = substr($errstr, 0, $pos);
		}

		$errfile = cleanPath($errfile);

		if($this->logger instanceof MainLogger){
			$this->logger->logException($e, $trace);
		}

		$lastError = [
			"type" => $type,
			"message" => $errstr,
			"fullFile" => $e->getFile(),
			"file" => $errfile,
			"line" => $errline,
			"trace" => @getTrace(1, $trace)
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}

	public function crashDump(){
		if($this->isRunning === false){
			return;
		}
		$this->isRunning = false;
		$this->hasStopped = false;

		ini_set("error_reporting", 0);
		ini_set("memory_limit", -1); //Fix error dump not dumped on memory problems
		$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.create"));
		try{
			$dump = new CrashDump($this);
		}catch(\Exception $e){
			$this->logger->critical($this->getLanguage()->translateString("pocketmine.crash.error", $e->getMessage()));
			return;
		}

		$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.submit", [$dump->getPath()]));


		if($this->getProperty("auto-report.enabled", true) !== false){
			$report = true;
			$plugin = $dump->getData()["plugin"];
			if(is_string($plugin)){
				$p = $this->pluginManager->getPlugin($plugin);
				if($p instanceof Plugin and !($p->getPluginLoader() instanceof PharPluginLoader)){
					$report = false;
				}
			}elseif(\Phar::running(true) == ""){
				$report = false;
			}
			if($dump->getData()["error"]["type"] === "E_PARSE" or $dump->getData()["error"]["type"] === "E_COMPILE_ERROR"){
				$report = false;
			}

			if($report){
				$reply = Utils::postURL("http://" . $this->getProperty("auto-report.host", "crash.pocketmine.net") . "/submit/api", [
					"report" => "yes",
					"name" => $this->getName() . " " . $this->getPocketMineVersion(),
					"email" => "crash@pocketmine.net",
					"reportPaste" => base64_encode($dump->getEncodedData())
				]);

				if(($data = json_decode($reply)) !== false and isset($data->crashId)){
					$reportId = $data->crashId;
					$reportUrl = $data->crashUrl;
					$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.archive", [$reportUrl, $reportId]));
				}
			}
		}

		//$this->checkMemory();
		//$dump .= "Memory Usage Tracking: \r\n" . chunk_split(base64_encode(gzdeflate(implode(";", $this->memoryStats), 9))) . "\r\n";

		$this->forceShutdown();
		@kill(getmypid());
		exit(1);
	}

	public function __debugInfo(){
		return [];
	}

	private function tickProcessor(){
		while($this->isRunning){
			$this->tick();
			usleep((int) max(1, ($this->nextTick - microtime(true)) * 1000000 - 2000));
		}
	}

	public function addPlayer($identifier, Player $player){
		$this->players[$identifier] = $player;
		$this->identifiers[spl_object_hash($player)] = $identifier;
	}

	private function checkTickUpdates($currentTick){
		//Do level ticks
		foreach($this->getLevels() as $level){
			if($level->getTickRate() > $this->baseTickRate and --$level->tickRateCounter > 0){
				if($this->alwaysTickPlayers){
					foreach($level->getPlayers() as $p){
						$p->onUpdate($currentTick);
					}
				}
				continue;
			}
			try{
				$levelTime = microtime(true);
				$level->doTick($currentTick);
				$tickMs = (microtime(true) - $levelTime) * 1000;

				if($this->autoTickRate){
					if($tickMs < 50 and $level->getTickRate() > $this->baseTickRate){
						$level->setTickRate($r = $level->getTickRate() - 1);
						if($r > $this->baseTickRate){
							$level->tickRateCounter = $level->getTickRate();
						}
						$this->getLogger()->debug("Raising level \"".$level->getName()."\" tick rate to ".$level->getTickRate()." ticks");
					}elseif($tickMs >= 50){
						if($level->getTickRate() === $this->baseTickRate){
							$level->setTickRate(max($this->baseTickRate + 1, min($this->autoTickRateLimit, floor($tickMs / 50))));
							$this->getLogger()->debug("Level \"".$level->getName()."\" took ".round($tickMs, 2)."ms, setting tick rate to ".$level->getTickRate()." ticks");
						}elseif(($tickMs / $level->getTickRate()) >= 50 and $level->getTickRate() < $this->autoTickRateLimit){
							$level->setTickRate($level->getTickRate() + 1);
							$this->getLogger()->debug("Level \"".$level->getName()."\" took ".round($tickMs, 2)."ms, setting tick rate to ".$level->getTickRate()." ticks");
						}
						$level->tickRateCounter = $level->getTickRate();
					}
				}
			}catch(\Exception $e){
				$this->logger->critical($this->getLanguage()->translateString("pocketmine.level.tickError", [$level->getName(), $e->getMessage()]));
				if(\pocketmine\DEBUG > 1 and $this->logger instanceof MainLogger){
					$this->logger->logException($e);
				}
			}
		}
	}

	public function doAutoSave(){
		if($this->getAutoSave()){
			Timings::$worldSaveTimer->startTiming();
			foreach($this->getOnlinePlayers() as $index => $player){
				if($player->isOnline()){
					$player->save();
				}elseif(!$player->isConnected()){
					$this->removePlayer($player);
				}
			}

			foreach($this->getLevels() as $level){
				$level->save(false);
			}
			Timings::$worldSaveTimer->stopTiming();
		}
	}

	public function sendUsage(){
		if($this->lastSendUsage instanceof SendUsageTask){
			if(!$this->lastSendUsage->isGarbage()){ //do not call multiple times
				return;
			}
		}

		$plist = "";
		foreach($this->getPluginManager()->getPlugins() as $p){
			$d = $p->getDescription();
			$plist .= str_replace([";", ":"], "", $d->getName()) . ":" . str_replace([";", ":"], "", $d->getVersion()) . ";";
		}

		$version = new VersionString();
		$this->lastSendUsage = new SendUsageTask("https://stats.pocketmine.net/usage.php", [
			"serverid" => Binary::readLong(substr(hex2bin(str_replace("-", "", $this->serverID)), 0, 8)),
			"port" => $this->getPort(),
			"os" => Utils::getOS(),
			"name" => $this->getName(),
			"memory_total" => $this->getConfigString("memory-limit"), //TODO
			"memory_usage" => Utils::getMemoryUsage(),
			"php_version" => PHP_VERSION,
			"version" => $version->get(true),
			"build" => $version->getBuild(),
			"mc_version" => \pocketmine\MINECRAFT_VERSION,
			"protocol" => \pocketmine\network\protocol\Info::CURRENT_PROTOCOL,
			"online" => count($this->players),
			"max" => $this->getMaxPlayers(),
			"plugins" => $plist,
		]);

		$this->scheduler->scheduleAsyncTask($this->lastSendUsage);
	}


	/**
	 * @return BaseLang
	 */
	public function getLanguage(){
		return $this->baseLang;
	}

	/**
	 * @return bool
	 */
	public function isLanguageForced(){
		return $this->forceLanguage;
	}

	/**
	 * @return Network
	 */
	public function getNetwork(){
		return $this->network;
	}

	/**
	 * @return MemoryManager
	 */
	public function getMemoryManager(){
		return $this->memoryManager;
	}

	private function titleTick(){
		if(!Terminal::hasFormattingCodes()){
			return;
		}
		
		$u = Utils::getMemoryUsage(true);
		$usage = round(($u[0] / 1024) / 1024, 2) . "/" . round(($u[1] / 1024) / 1024, 2) . "/".round(($u[2] / 1024) / 1024, 2)." MB @ " . Utils::getThreadCount() . " threads";

		echo "\x1b]0;" . $this->getName() . " " .
			$this->getPocketMineVersion() .
			" | Online " . count($this->players) . "/" . $this->getMaxPlayers() .
			" | Memory " . $usage .
			" | U " . round($this->network->getUpload() / 1024, 2) .
			" D " . round($this->network->getDownload() / 1024, 2) .
			" kB/s | TPS " . $this->getTicksPerSecond() .
			" | Load " . $this->getTickUsage() . "%\x07";

		$this->network->resetStatistics();
	}

	/**
	 * @param string $address
	 * @param int    $port
	 * @param string $payload
	 *
	 * TODO: move this to Network
	 */
	public function handlePacket($address, $port, $payload){
		try{
			if(strlen($payload) > 2 and substr($payload, 0, 2) === "\xfe\xfd" and $this->queryHandler instanceof QueryHandler){
				$this->queryHandler->handle($address, $port, $payload);
			}
		}catch(\Exception $e){
			if(\pocketmine\DEBUG > 1){
				if($this->logger instanceof MainLogger){
					$this->logger->logException($e);
				}
			}

			$this->getNetwork()->blockAddress($address, 600);
		}
		//TODO: add raw packet events
	}


	/**
	 * Tries to execute a server tick
	 */
	private function tick(){
		$tickTime = microtime(true);
		if(($tickTime - $this->nextTick) < -0.025){ //Allow half a tick of diff
			return false;
		}

		Timings::$serverTickTimer->startTiming();

		++$this->tickCounter;

		$this->checkConsole();

		Timings::$connectionTimer->startTiming();
		$this->network->processInterfaces();

		if($this->rcon !== null){
			$this->rcon->check();
		}

		Timings::$connectionTimer->stopTiming();

		Timings::$schedulerTimer->startTiming();
		$this->scheduler->mainThreadHeartbeat($this->tickCounter);
		Timings::$schedulerTimer->stopTiming();

		$this->checkTickUpdates($this->tickCounter);

		if(($this->tickCounter & 0b1111) === 0){
			$this->titleTick();
			$this->maxTick = 20;
			$this->maxUse = 0;

			if($this->queryHandler !== null and ($this->tickCounter & 0b111111111) === 0){
				try{
					$this->queryHandler->regenerateInfo();
				}catch(\Exception $e){
					if($this->logger instanceof MainLogger){
						$this->logger->logException($e);
					}
				}
			}
		}

		if($this->autoSave and ++$this->autoSaveTicker >= $this->autoSaveTicks){
			$this->autoSaveTicker = 0;
			$this->doAutoSave();
		}

		if($this->sendUsageTicker > 0 and --$this->sendUsageTicker === 0){
			$this->sendUsageTicker = 6000;
			$this->sendUsage();
		}

		if(($this->tickCounter % 100) === 0){
			foreach($this->levels as $level){
				$level->clearCache();
			}

			if($this->getTicksPerSecondAverage() < 12){
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.tickOverload"));
			}
		}

		if($this->dispatchSignals and $this->tickCounter % 5 === 0){
			pcntl_signal_dispatch();
		}

		$this->getMemoryManager()->check();

		Timings::$serverTickTimer->stopTiming();

		TimingsHandler::tick();

		$now = microtime(true);
		$tick = min(20, 1 / max(0.001, $now - $tickTime));
		$use = min(1, ($now - $tickTime) / 0.05);

		if($this->maxTick > $tick){
			$this->maxTick = $tick;
		}

		if($this->maxUse < $use){
			$this->maxUse = $use;
		}

		array_shift($this->tickAverage);
		$this->tickAverage[] = $tick;
		array_shift($this->useAverage);
		$this->useAverage[] = $use;

		if(($this->nextTick - $tickTime) < -1){
			$this->nextTick = $tickTime;
		}
		$this->nextTick += 0.05;

		return true;
	}

	private function registerEntities(){
		Entity::registerEntity(Arrow::class);
		Entity::registerEntity(DroppedItem::class);
		Entity::registerEntity(FallingSand::class);
		Entity::registerEntity(PrimedTNT::class);
		Entity::registerEntity(Snowball::class);
		Entity::registerEntity(Villager::class);
		Entity::registerEntity(Zombie::class);
		Entity::registerEntity(Squid::class);

		Entity::registerEntity(Human::class, true);
	}

	private function registerTiles(){
		Tile::registerTile(Chest::class);
		Tile::registerTile(Furnace::class);
		Tile::registerTile(Sign::class);
	}

}
