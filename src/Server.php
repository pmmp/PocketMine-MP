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

/**
 * PocketMine-MP is the Minecraft: PE multiplayer server software
 * Homepage: http://www.pocketmine.net/
 */
namespace pocketmine;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\SimpleCommandMap;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\console\ConsoleReaderThread;
use pocketmine\crafting\CraftingManager;
use pocketmine\crafting\CraftingManagerFromDataHelper;
use pocketmine\data\java\GameModeIdMap;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\Location;
use pocketmine\event\HandlerListManager;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\lang\Language;
use pocketmine\lang\LanguageNotFoundException;
use pocketmine\lang\TranslationContainer;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\CompressBatchTask;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\encryption\EncryptionContext;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\PacketBroadcaster;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\Network;
use pocketmine\network\query\DedicatedQueryNetworkInterface;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\query\QueryInfo;
use pocketmine\network\upnp\UPnPNetworkInterface;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\player\OfflinePlayer;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginEnableOrder;
use pocketmine\plugin\PluginGraylist;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\ScriptPluginLoader;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\scheduler\AsyncPool;
use pocketmine\snooze\SleeperHandler;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\stats\SendUsageTask;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\updater\AutoUpdater;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Internet;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Process;
use pocketmine\utils\Promise;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\world\format\io\WritableWorldProviderManagerEntry;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use pocketmine\world\WorldManager;
use Ramsey\Uuid\UuidInterface;
use Webmozart\PathUtil\Path;
use function array_shift;
use function array_sum;
use function base64_encode;
use function cli_set_process_title;
use function copy;
use function count;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function get_class;
use function implode;
use function ini_set;
use function is_array;
use function is_string;
use function json_decode;
use function max;
use function microtime;
use function min;
use function mkdir;
use function ob_end_flush;
use function preg_replace;
use function realpath;
use function register_shutdown_function;
use function rename;
use function round;
use function sleep;
use function spl_object_id;
use function sprintf;
use function str_repeat;
use function str_replace;
use function stripos;
use function strlen;
use function strrpos;
use function strtolower;
use function time;
use function touch;
use function trim;
use function yaml_parse;
use function zlib_decode;
use function zlib_encode;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use const PHP_INT_MAX;
use const PTHREADS_INHERIT_NONE;
use const ZLIB_ENCODING_GZIP;

/**
 * The class that manages everything
 */
class Server{
	public const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	public const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	/** @var Server|null */
	private static $instance = null;

	/** @var SleeperHandler */
	private $tickSleeper;

	/** @var BanList */
	private $banByName;

	/** @var BanList */
	private $banByIP;

	/** @var Config */
	private $operators;

	/** @var Config */
	private $whitelist;

	/** @var bool */
	private $isRunning = true;

	/** @var bool */
	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager;

	/** @var float */
	private $profilingTickRate = 20;

	/** @var AutoUpdater */
	private $updater;

	/** @var AsyncPool */
	private $asyncPool;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */
	private $tickCounter = 0;
	/** @var float */
	private $nextTick = 0;
	/** @var float[] */
	private $tickAverage = [20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20];
	/** @var float[] */
	private $useAverage = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	/** @var float */
	private $currentTPS = 20;
	/** @var float */
	private $currentUse = 0;
	/** @var float */
	private $startTime;

	/** @var bool */
	private $doTitleTick = true;

	/** @var int */
	private $sendUsageTicker = 0;

	/** @var \AttachableThreadedLogger */
	private $logger;

	/** @var MemoryManager */
	private $memoryManager;

	/** @var ConsoleReaderThread */
	private $console;

	/** @var SimpleCommandMap */
	private $commandMap;

	/** @var CraftingManager */
	private $craftingManager;

	/** @var ResourcePackManager */
	private $resourceManager;

	/** @var WorldManager */
	private $worldManager;

	/** @var int */
	private $maxPlayers;

	/** @var bool */
	private $onlineMode = true;

	/** @var Network */
	private $network;
	/** @var bool */
	private $networkCompressionAsync = true;

	/** @var Language */
	private $language;
	/** @var bool */
	private $forceLanguage = false;

	/** @var UuidInterface */
	private $serverID;

	/** @var \DynamicClassLoader */
	private $autoloader;
	/** @var string */
	private $dataPath;
	/** @var string */
	private $pluginPath;

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private $uniquePlayers = [];

	/** @var QueryInfo */
	private $queryInfo;

	/** @var ServerConfigGroup */
	private $configGroup;

	/** @var Player[] */
	private $playerList = [];

	/**
	 * @var CommandSender[][]
	 * @phpstan-var array<string, array<int, CommandSender>>
	 */
	private $broadcastSubscribers = [];

	public function getName() : string{
		return VersionInfo::NAME;
	}

	public function isRunning() : bool{
		return $this->isRunning;
	}

	public function getPocketMineVersion() : string{
		return VersionInfo::getVersionObj()->getFullVersion(true);
	}

	public function getVersion() : string{
		return ProtocolInfo::MINECRAFT_VERSION;
	}

	public function getApiVersion() : string{
		return VersionInfo::BASE_VERSION;
	}

	public function getFilePath() : string{
		return \pocketmine\PATH;
	}

	public function getResourcePath() : string{
		return \pocketmine\RESOURCE_PATH;
	}

	public function getDataPath() : string{
		return $this->dataPath;
	}

	public function getPluginPath() : string{
		return $this->pluginPath;
	}

	public function getMaxPlayers() : int{
		return $this->maxPlayers;
	}

	/**
	 * Returns whether the server requires that players be authenticated to Xbox Live. If true, connecting players who
	 * are not logged into Xbox Live will be disconnected.
	 */
	public function getOnlineMode() : bool{
		return $this->onlineMode;
	}

	/**
	 * Alias of {@link #getOnlineMode()}.
	 */
	public function requiresAuthentication() : bool{
		return $this->getOnlineMode();
	}

	public function getPort() : int{
		return $this->configGroup->getConfigInt("server-port", 19132);
	}

	public function getViewDistance() : int{
		return max(2, $this->configGroup->getConfigInt("view-distance", 8));
	}

	/**
	 * Returns a view distance up to the currently-allowed limit.
	 */
	public function getAllowedViewDistance(int $distance) : int{
		return max(2, min($distance, $this->memoryManager->getViewDistance($this->getViewDistance())));
	}

	public function getIp() : string{
		$str = $this->configGroup->getConfigString("server-ip");
		return $str !== "" ? $str : "0.0.0.0";
	}

	/**
	 * @return UuidInterface
	 */
	public function getServerUniqueId(){
		return $this->serverID;
	}

	public function getGamemode() : GameMode{
		return GameModeIdMap::getInstance()->fromId($this->configGroup->getConfigInt("gamemode", 0)) ?? GameMode::SURVIVAL();
	}

	public function getForceGamemode() : bool{
		return $this->configGroup->getConfigBool("force-gamemode", false);
	}

	/**
	 * Returns Server global difficulty. Note that this may be overridden in individual worlds.
	 */
	public function getDifficulty() : int{
		return $this->configGroup->getConfigInt("difficulty", World::DIFFICULTY_NORMAL);
	}

	public function hasWhitelist() : bool{
		return $this->configGroup->getConfigBool("white-list", false);
	}

	public function isHardcore() : bool{
		return $this->configGroup->getConfigBool("hardcore", false);
	}

	public function getMotd() : string{
		return $this->configGroup->getConfigString("motd", VersionInfo::NAME . " Server");
	}

	/**
	 * @return \DynamicClassLoader
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

	public function getResourcePackManager() : ResourcePackManager{
		return $this->resourceManager;
	}

	public function getWorldManager() : WorldManager{
		return $this->worldManager;
	}

	public function getAsyncPool() : AsyncPool{
		return $this->asyncPool;
	}

	public function getTick() : int{
		return $this->tickCounter;
	}

	/**
	 * Returns the last server TPS measure
	 */
	public function getTicksPerSecond() : float{
		return round($this->currentTPS, 2);
	}

	/**
	 * Returns the last server TPS average measure
	 */
	public function getTicksPerSecondAverage() : float{
		return round(array_sum($this->tickAverage) / count($this->tickAverage), 2);
	}

	/**
	 * Returns the TPS usage/load in %
	 */
	public function getTickUsage() : float{
		return round($this->currentUse * 100, 2);
	}

	/**
	 * Returns the TPS usage/load average in %
	 */
	public function getTickUsageAverage() : float{
		return round((array_sum($this->useAverage) / count($this->useAverage)) * 100, 2);
	}

	public function getStartTime() : float{
		return $this->startTime;
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
	public function getOnlinePlayers() : array{
		return $this->playerList;
	}

	public function shouldSavePlayerData() : bool{
		return $this->configGroup->getPropertyBool("player.save-player-data", true);
	}

	/**
	 * @return OfflinePlayer|Player
	 */
	public function getOfflinePlayer(string $name){
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);

		if($result === null){
			$result = new OfflinePlayer($name, $this->getOfflinePlayerData($name));
		}

		return $result;
	}

	private function getPlayerDataPath(string $username) : string{
		return Path::join($this->getDataPath(), 'players', strtolower($username) . '.dat');
	}

	/**
	 * Returns whether the server has stored any saved data for this player.
	 */
	public function hasOfflinePlayerData(string $name) : bool{
		return file_exists($this->getPlayerDataPath($name));
	}

	private function handleCorruptedPlayerData(string $name) : void{
		$path = $this->getPlayerDataPath($name);
		rename($path, $path . '.bak');
		$this->logger->error($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_DATA_PLAYERCORRUPTED, [$name]));
	}

	public function getOfflinePlayerData(string $name) : ?CompoundTag{
		return Timings::$syncPlayerDataLoad->time(function() use ($name) : ?CompoundTag{
			$name = strtolower($name);
			$path = $this->getPlayerDataPath($name);

			if(file_exists($path)){
				$contents = @file_get_contents($path);
				if($contents === false){
					throw new \RuntimeException("Failed to read player data file \"$path\" (permission denied?)");
				}
				$decompressed = @zlib_decode($contents);
				if($decompressed === false){
					$this->logger->debug("Failed to decompress raw player data for \"$name\"");
					$this->handleCorruptedPlayerData($name);
					return null;
				}

				try{
					return (new BigEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
				}catch(NbtDataException $e){ //corrupt data
					$this->logger->debug("Failed to decode NBT data for \"$name\": " . $e->getMessage());
					$this->handleCorruptedPlayerData($name);
					return null;
				}
			}
			return null;
		});
	}

	public function saveOfflinePlayerData(string $name, CompoundTag $nbtTag) : void{
		$ev = new PlayerDataSaveEvent($nbtTag, $name, $this->getPlayerExact($name));
		if(!$this->shouldSavePlayerData()){
			$ev->cancel();
		}

		$ev->call();

		if(!$ev->isCancelled()){
			Timings::$syncPlayerDataSave->time(function() use ($name, $ev) : void{
				$nbt = new BigEndianNbtSerializer();
				try{
					file_put_contents($this->getPlayerDataPath($name), zlib_encode($nbt->write(new TreeRoot($ev->getSaveData())), ZLIB_ENCODING_GZIP));
				}catch(\ErrorException $e){
					$this->logger->critical($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_DATA_SAVEERROR, [$name, $e->getMessage()]));
					$this->logger->logException($e);
				}
			});
		}
	}

	/**
	 * @phpstan-return Promise<Player>
	 */
	public function createPlayer(NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, ?CompoundTag $offlinePlayerData) : Promise{
		$ev = new PlayerCreationEvent($session);
		$ev->call();
		$class = $ev->getPlayerClass();

		if($offlinePlayerData !== null and ($world = $this->worldManager->getWorldByName($offlinePlayerData->getString("Level", ""))) !== null){
			$playerPos = EntityDataHelper::parseLocation($offlinePlayerData, $world);
			$spawn = $playerPos->asVector3();
		}else{
			$world = $this->worldManager->getDefaultWorld();
			if($world === null){
				throw new AssumptionFailedError("Default world should always be loaded");
			}
			$playerPos = null;
			$spawn = $world->getSpawnLocation();
		}
		$playerPromise = new Promise();
		$world->requestChunkPopulation($spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4, null)->onCompletion(
			function() use ($playerPromise, $class, $session, $playerInfo, $authenticated, $world, $playerPos, $spawn, $offlinePlayerData) : void{
				if(!$session->isConnected()){
					$playerPromise->reject();
					return;
				}

				/* Stick with the original spawn at the time of generation request, even if it changed since then.
				 * This is because we know for sure that that chunk will be generated, but the one at the new location
				 * might not be, and it would be much more complex to go back and redo the whole thing.
				 *
				 * TODO: this relies on the assumption that getSafeSpawn() will only alter the Y coordinate of the
				 * provided position. If this assumption is broken, we'll start seeing crashes in here.
				 */

				/**
				 * @see Player::__construct()
				 * @var Player $player
				 */
				$player = new $class($this, $session, $playerInfo, $authenticated, $playerPos ?? Location::fromObject($world->getSafeSpawn($spawn), $world), $offlinePlayerData);
				if(!$player->hasPlayedBefore()){
					$player->onGround = true;  //TODO: this hack is needed for new players in-air ticks - they don't get detected as on-ground until they move
				}
				$playerPromise->resolve($player);
			},
			static function() use ($playerPromise, $session) : void{
				if($session->isConnected()){
					$session->disconnect("Spawn terrain generation failed");
				}
				$playerPromise->reject();
			}
		);
		return $playerPromise;
	}

	/**
	 * Returns an online player whose name begins with or equals the given string (case insensitive).
	 * The closest match will be returned, or null if there are no online matches.
	 *
	 * @see Server::getPlayerExact()
	 */
	public function getPlayerByPrefix(string $name) : ?Player{
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
	 * Returns an online player with the given name (case insensitive), or null if not found.
	 */
	public function getPlayerExact(string $name) : ?Player{
		$name = strtolower($name);
		foreach($this->getOnlinePlayers() as $player){
			if(strtolower($player->getName()) === $name){
				return $player;
			}
		}

		return null;
	}

	/**
	 * Returns the player online with the specified raw UUID, or null if not found
	 */
	public function getPlayerByRawUUID(string $rawUUID) : ?Player{
		return $this->playerList[$rawUUID] ?? null;
	}

	/**
	 * Returns the player online with a UUID equivalent to the specified UuidInterface object, or null if not found
	 */
	public function getPlayerByUUID(UuidInterface $uuid) : ?Player{
		return $this->getPlayerByRawUUID($uuid->getBytes());
	}

	public function getConfigGroup() : ServerConfigGroup{
		return $this->configGroup;
	}

	/**
	 * @return Command|PluginOwned|null
	 * @phpstan-return (Command&PluginOwned)|null
	 */
	public function getPluginCommand(string $name){
		if(($command = $this->commandMap->getCommand($name)) instanceof PluginOwned){
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

	public function addOp(string $name) : void{
		$this->operators->set(strtolower($name), true);

		if(($player = $this->getPlayerExact($name)) !== null){
			$player->setBasePermission(DefaultPermissions::ROOT_OPERATOR, true);
		}
		$this->operators->save();
	}

	public function removeOp(string $name) : void{
		$this->operators->remove(strtolower($name));

		if(($player = $this->getPlayerExact($name)) !== null){
			$player->unsetBasePermission(DefaultPermissions::ROOT_OPERATOR);
		}
		$this->operators->save();
	}

	public function addWhitelist(string $name) : void{
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save();
	}

	public function removeWhitelist(string $name) : void{
		$this->whitelist->remove(strtolower($name));
		$this->whitelist->save();
	}

	public function isWhitelisted(string $name) : bool{
		return !$this->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
	}

	public function isOp(string $name) : bool{
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

	/**
	 * @return string[][]
	 */
	public function getCommandAliases() : array{
		$section = $this->configGroup->getProperty("aliases");
		$result = [];
		if(is_array($section)){
			foreach($section as $key => $value){
				$commands = [];
				if(is_array($value)){
					$commands = $value;
				}else{
					$commands[] = (string) $value;
				}

				$result[$key] = $commands;
			}
		}

		return $result;
	}

	public static function getInstance() : Server{
		if(self::$instance === null){
			throw new \RuntimeException("Attempt to retrieve Server instance outside server thread");
		}
		return self::$instance;
	}

	public function __construct(\DynamicClassLoader $autoloader, \AttachableThreadedLogger $logger, string $dataPath, string $pluginPath){
		if(self::$instance !== null){
			throw new \InvalidStateException("Only one server instance can exist at once");
		}
		self::$instance = $this;
		$this->startTime = microtime(true);

		$this->tickSleeper = new SleeperHandler();
		$this->autoloader = $autoloader;
		$this->logger = $logger;

		try{
			foreach([
				$dataPath,
				$pluginPath,
				Path::join($dataPath, "worlds"),
				Path::join($dataPath, "players")
			] as $neededPath){
				if(!file_exists($neededPath)){
					mkdir($neededPath, 0777);
				}
			}

			$this->dataPath = realpath($dataPath) . DIRECTORY_SEPARATOR;
			$this->pluginPath = realpath($pluginPath) . DIRECTORY_SEPARATOR;

			$this->logger->info("Loading server configuration");
			$pocketmineYmlPath = Path::join($this->dataPath, "pocketmine.yml");
			if(!file_exists($pocketmineYmlPath)){
				$content = file_get_contents(Path::join(\pocketmine\RESOURCE_PATH, "pocketmine.yml"));
				if(VersionInfo::IS_DEVELOPMENT_BUILD){
					$content = str_replace("preferred-channel: stable", "preferred-channel: beta", $content);
				}
				@file_put_contents($pocketmineYmlPath, $content);
			}

			$this->configGroup = new ServerConfigGroup(
				new Config($pocketmineYmlPath, Config::YAML, []),
				new Config(Path::join($this->dataPath, "server.properties"), Config::PROPERTIES, [
					"motd" => VersionInfo::NAME . " Server",
					"server-port" => 19132,
					"white-list" => false,
					"max-players" => 20,
					"gamemode" => 0,
					"force-gamemode" => false,
					"hardcore" => false,
					"pvp" => true,
					"difficulty" => World::DIFFICULTY_NORMAL,
					"generator-settings" => "",
					"level-name" => "world",
					"level-seed" => "",
					"level-type" => "DEFAULT",
					"enable-query" => true,
					"auto-save" => true,
					"view-distance" => 8,
					"xbox-auth" => true,
					"language" => "eng"
				])
			);

			$debugLogLevel = $this->configGroup->getPropertyInt("debug.level", 1);
			if($this->logger instanceof MainLogger){
				$this->logger->setLogDebug($debugLogLevel > 1);
			}

			$this->forceLanguage = $this->configGroup->getPropertyBool("settings.force-language", false);
			$selectedLang = $this->configGroup->getConfigString("language", $this->configGroup->getPropertyString("settings.language", Language::FALLBACK_LANGUAGE));
			try{
				$this->language = new Language($selectedLang);
			}catch(LanguageNotFoundException $e){
				$this->logger->error($e->getMessage());
				try{
					$this->language = new Language(Language::FALLBACK_LANGUAGE);
				}catch(LanguageNotFoundException $e){
					$this->logger->emergency("Fallback language \"" . Language::FALLBACK_LANGUAGE . "\" not found");
					return;
				}
			}

			$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::LANGUAGE_SELECTED, [$this->getLanguage()->getName(), $this->getLanguage()->getLang()]));

			if(VersionInfo::IS_DEVELOPMENT_BUILD){
				if(!$this->configGroup->getPropertyBool("settings.enable-dev-builds", false)){
					$this->logger->emergency($this->language->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR1, [VersionInfo::NAME]));
					$this->logger->emergency($this->language->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR2));
					$this->logger->emergency($this->language->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR3));
					$this->logger->emergency($this->language->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR4, ["settings.enable-dev-builds"]));
					$this->logger->emergency($this->language->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR5, ["https://github.com/pmmp/PocketMine-MP/releases"]));
					$this->forceShutdown();

					return;
				}

				$this->logger->warning(str_repeat("-", 40));
				$this->logger->warning($this->language->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING1, [VersionInfo::NAME]));
				$this->logger->warning($this->language->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING2));
				$this->logger->warning($this->language->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING3));
				$this->logger->warning(str_repeat("-", 40));
			}

			$this->memoryManager = new MemoryManager($this);

			$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_START, [TextFormat::AQUA . $this->getVersion() . TextFormat::RESET]));

			if(($poolSize = $this->configGroup->getPropertyString("settings.async-workers", "auto")) === "auto"){
				$poolSize = 2;
				$processors = Utils::getCoreCount() - 2;

				if($processors > 0){
					$poolSize = max(1, $processors);
				}
			}else{
				$poolSize = max(1, (int) $poolSize);
			}

			$this->asyncPool = new AsyncPool($poolSize, max(-1, $this->configGroup->getPropertyInt("memory.async-worker-hard-limit", 256)), $this->autoloader, $this->logger, $this->tickSleeper);

			$netCompressionThreshold = -1;
			if($this->configGroup->getPropertyInt("network.batch-threshold", 256) >= 0){
				$netCompressionThreshold = $this->configGroup->getPropertyInt("network.batch-threshold", 256);
			}

			$netCompressionLevel = $this->configGroup->getPropertyInt("network.compression-level", 6);
			if($netCompressionLevel < 1 or $netCompressionLevel > 9){
				$this->logger->warning("Invalid network compression level $netCompressionLevel set, setting to default 6");
				$netCompressionLevel = 6;
			}
			ZlibCompressor::setInstance(new ZlibCompressor($netCompressionLevel, $netCompressionThreshold, ZlibCompressor::DEFAULT_MAX_DECOMPRESSION_SIZE));

			$this->networkCompressionAsync = $this->configGroup->getPropertyBool("network.async-compression", true);

			EncryptionContext::$ENABLED = $this->configGroup->getPropertyBool("network.enable-encryption", true);

			$this->doTitleTick = $this->configGroup->getPropertyBool("console.title-tick", true) && Terminal::hasFormattingCodes();

			$this->operators = new Config(Path::join($this->dataPath, "ops.txt"), Config::ENUM);
			$this->whitelist = new Config(Path::join($this->dataPath, "white-list.txt"), Config::ENUM);

			$bannedTxt = Path::join($this->dataPath, "banned.txt");
			$bannedPlayersTxt = Path::join($this->dataPath, "banned-players.txt");
			if(file_exists($bannedTxt) and !file_exists($bannedPlayersTxt)){
				@rename($bannedTxt, $bannedPlayersTxt);
			}
			@touch($bannedPlayersTxt);
			$this->banByName = new BanList($bannedPlayersTxt);
			$this->banByName->load();
			$bannedIpsTxt = Path::join($this->dataPath, "banned-ips.txt");
			@touch($bannedIpsTxt);
			$this->banByIP = new BanList($bannedIpsTxt);
			$this->banByIP->load();

			$this->maxPlayers = $this->configGroup->getConfigInt("max-players", 20);

			$this->onlineMode = $this->configGroup->getConfigBool("xbox-auth", true);
			if($this->onlineMode){
				$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_AUTH_ENABLED));
			}else{
				$this->logger->warning($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_AUTH_DISABLED));
				$this->logger->warning($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_AUTHWARNING));
				$this->logger->warning($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_AUTHPROPERTY_DISABLED));
			}

			if($this->configGroup->getConfigBool("hardcore", false) and $this->getDifficulty() < World::DIFFICULTY_HARD){
				$this->configGroup->setConfigInt("difficulty", World::DIFFICULTY_HARD);
			}

			@cli_set_process_title($this->getName() . " " . $this->getPocketMineVersion());

			$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());

			$this->getLogger()->debug("Server unique id: " . $this->getServerUniqueId());
			$this->getLogger()->debug("Machine unique id: " . Utils::getMachineUniqueId());

			$this->network = new Network($this->logger);
			$this->network->setName($this->getMotd());

			$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_INFO, [
				$this->getName(),
				(VersionInfo::IS_DEVELOPMENT_BUILD ? TextFormat::YELLOW : "") . $this->getPocketMineVersion() . TextFormat::RESET
			]));
			$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_LICENSE, [$this->getName()]));

			Timings::init();
			TimingsHandler::setEnabled($this->configGroup->getPropertyBool("settings.enable-profiling", false));
			$this->profilingTickRate = $this->configGroup->getPropertyInt("settings.profile-report-trigger", 20);

			DefaultPermissions::registerCorePermissions();

			$this->commandMap = new SimpleCommandMap($this);

			$this->craftingManager = CraftingManagerFromDataHelper::make(Path::join(\pocketmine\RESOURCE_PATH, "vanilla", "recipes.json"));

			$this->resourceManager = new ResourcePackManager(Path::join($this->getDataPath(), "resource_packs"), $this->logger);

			$pluginGraylist = null;
			$graylistFile = Path::join($this->dataPath, "plugin_list.yml");
			if(!file_exists($graylistFile)){
				copy(Path::join(\pocketmine\RESOURCE_PATH, 'plugin_list.yml'), $graylistFile);
			}
			try{
				$pluginGraylist = PluginGraylist::fromArray(yaml_parse(file_get_contents($graylistFile)));
			}catch(\InvalidArgumentException $e){
				$this->logger->emergency("Failed to load $graylistFile: " . $e->getMessage());
				$this->forceShutdown();
				return;
			}
			$this->pluginManager = new PluginManager($this, $this->configGroup->getPropertyBool("plugins.legacy-data-dir", true) ? null : Path::join($this->getDataPath(), "plugin_data"), $pluginGraylist);
			$this->pluginManager->registerInterface(new PharPluginLoader($this->autoloader));
			$this->pluginManager->registerInterface(new ScriptPluginLoader());

			$providerManager = new WorldProviderManager();
			if(
				($format = $providerManager->getProviderByName($formatName = $this->configGroup->getPropertyString("level-settings.default-format", ""))) !== null and
				$format instanceof WritableWorldProviderManagerEntry
			){
				$providerManager->setDefault($format);
			}elseif($formatName !== ""){
				$this->logger->warning($this->language->translateString(KnownTranslationKeys::POCKETMINE_LEVEL_BADDEFAULTFORMAT, [$formatName]));
			}

			$this->worldManager = new WorldManager($this, Path::join($this->dataPath, "worlds"), $providerManager);
			$this->worldManager->setAutoSave($this->configGroup->getConfigBool("auto-save", $this->worldManager->getAutoSave()));
			$this->worldManager->setAutoSaveInterval($this->configGroup->getPropertyInt("ticks-per.autosave", 6000));

			$this->updater = new AutoUpdater($this, $this->configGroup->getPropertyString("auto-updater.host", "update.pmmp.io"));

			$this->queryInfo = new QueryInfo($this);

			register_shutdown_function([$this, "crashDump"]);

			$this->pluginManager->loadPlugins($this->pluginPath);
			$this->enablePlugins(PluginEnableOrder::STARTUP());

			foreach((array) $this->configGroup->getProperty("worlds", []) as $name => $options){
				if($options === null){
					$options = [];
				}elseif(!is_array($options)){
					continue;
				}
				if(!$this->worldManager->loadWorld($name, true)){
					$creationOptions = WorldCreationOptions::create();
					//TODO: error checking

					if(isset($options["generator"])){
						$generatorOptions = explode(":", $options["generator"]);
						$creationOptions->setGeneratorClass(GeneratorManager::getInstance()->getGenerator(array_shift($generatorOptions)));
						if(count($generatorOptions) > 0){
							$creationOptions->setGeneratorOptions(implode(":", $generatorOptions));
						}
					}
					if(isset($options["difficulty"]) && is_string($options["difficulty"])){
						$creationOptions->setDifficulty(World::getDifficultyFromString($options["difficulty"]));
					}
					if(isset($options["preset"]) && is_string($options["preset"])){
						$creationOptions->setGeneratorOptions($options["preset"]);
					}
					if(isset($options["seed"])){
						$convertedSeed = Generator::convertSeed((string) ($options["seed"] ?? ""));
						if($convertedSeed !== null){
							$creationOptions->setSeed($convertedSeed);
						}
					}

					$this->worldManager->generateWorld($name, $creationOptions);
				}
			}

			if($this->worldManager->getDefaultWorld() === null){
				$default = $this->configGroup->getConfigString("level-name", "world");
				if(trim($default) == ""){
					$this->getLogger()->warning("level-name cannot be null, using default");
					$default = "world";
					$this->configGroup->setConfigString("level-name", "world");
				}
				if(!$this->worldManager->loadWorld($default, true)){
					$creationOptions = WorldCreationOptions::create()
						->setGeneratorClass(GeneratorManager::getInstance()->getGenerator($this->configGroup->getConfigString("level-type")))
						->setGeneratorOptions($this->configGroup->getConfigString("generator-settings"));
					$convertedSeed = Generator::convertSeed($this->configGroup->getConfigString("level-seed"));
					if($convertedSeed !== null){
						$creationOptions->setSeed($convertedSeed);
					}
					$this->worldManager->generateWorld($default, $creationOptions);
				}

				$world = $this->worldManager->getWorldByName($default);
				if($world === null){
					$this->getLogger()->emergency($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_LEVEL_DEFAULTERROR));
					$this->forceShutdown();

					return;
				}
				$this->worldManager->setDefaultWorld($world);
			}

			$this->enablePlugins(PluginEnableOrder::POSTWORLD());

			$useQuery = $this->configGroup->getConfigBool("enable-query", true);
			if(!$this->network->registerInterface(new RakLibInterface($this)) && $useQuery){
				//RakLib would normally handle the transport for Query packets
				//if it's not registered we need to make sure Query still works
				$this->network->registerInterface(new DedicatedQueryNetworkInterface($this->getIp(), $this->getPort(), new \PrefixedLogger($this->logger, "Dedicated Query Interface")));
			}
			$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_NETWORKSTART, [$this->getIp(), $this->getPort()]));

			if($useQuery){
				$this->network->registerRawPacketHandler(new QueryHandler($this));
			}

			foreach($this->getIPBans()->getEntries() as $entry){
				$this->network->blockAddress($entry->getName(), -1);
			}

			if($this->configGroup->getPropertyBool("network.upnp-forwarding", false)){
				$this->network->registerInterface(new UPnPNetworkInterface($this->logger, Internet::getInternalIP(), $this->getPort()));
			}

			if($this->configGroup->getPropertyBool("settings.send-usage", true)){
				$this->sendUsageTicker = 6000;
				$this->sendUsage(SendUsageTask::TYPE_OPEN);
			}

			$this->configGroup->save();

			$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DEFAULTGAMEMODE, [$this->getGamemode()->getTranslationKey()]));
			$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_DONATE, [TextFormat::AQUA . "https://patreon.com/pocketminemp" . TextFormat::RESET]));
			$this->logger->info($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_STARTFINISHED, [round(microtime(true) - $this->startTime, 3)]));

			//TODO: move console parts to a separate component
			$consoleSender = new ConsoleCommandSender($this, $this->language);
			$this->subscribeToBroadcastChannel(self::BROADCAST_CHANNEL_ADMINISTRATIVE, $consoleSender);
			$this->subscribeToBroadcastChannel(self::BROADCAST_CHANNEL_USERS, $consoleSender);

			$consoleNotifier = new SleeperNotifier();
			$commandBuffer = new \Threaded();
			$this->console = new ConsoleReaderThread($commandBuffer, $consoleNotifier);
			$this->tickSleeper->addNotifier($consoleNotifier, function() use ($commandBuffer, $consoleSender) : void{
				Timings::$serverCommand->startTiming();
				while(($line = $commandBuffer->shift()) !== null){
					$this->dispatchCommand($consoleSender, (string) $line);
				}
				Timings::$serverCommand->stopTiming();
			});
			$this->console->start(PTHREADS_INHERIT_NONE);

			$this->tickProcessor();
			$this->forceShutdown();
		}catch(\Throwable $e){
			$this->exceptionHandler($e);
		}
	}

	/**
	 * Subscribes to a particular message broadcast channel.
	 * The channel ID can be any arbitrary string.
	 */
	public function subscribeToBroadcastChannel(string $channelId, CommandSender $subscriber) : void{
		$this->broadcastSubscribers[$channelId][spl_object_id($subscriber)] = $subscriber;
	}

	/**
	 * Unsubscribes from a particular message broadcast channel.
	 */
	public function unsubscribeFromBroadcastChannel(string $channelId, CommandSender $subscriber) : void{
		if(isset($this->broadcastSubscribers[$channelId][spl_object_id($subscriber)])){
			unset($this->broadcastSubscribers[$channelId][spl_object_id($subscriber)]);
			if(count($this->broadcastSubscribers[$channelId]) === 0){
				unset($this->broadcastSubscribers[$channelId]);
			}
		}
	}

	/**
	 * Unsubscribes from all broadcast channels.
	 */
	public function unsubscribeFromAllBroadcastChannels(CommandSender $subscriber) : void{
		foreach($this->broadcastSubscribers as $channelId => $recipients){
			$this->unsubscribeFromBroadcastChannel($channelId, $subscriber);
		}
	}

	/**
	 * Returns a list of all the CommandSenders subscribed to the given broadcast channel.
	 *
	 * @return CommandSender[]
	 * @phpstan-return array<int, CommandSender>
	 */
	public function getBroadcastChannelSubscribers(string $channelId) : array{
		return $this->broadcastSubscribers[$channelId] ?? [];
	}

	/**
	 * @param TranslationContainer|string $message
	 * @param CommandSender[]|null        $recipients
	 */
	public function broadcastMessage($message, ?array $recipients = null) : int{
		$recipients = $recipients ?? $this->getBroadcastChannelSubscribers(self::BROADCAST_CHANNEL_USERS);

		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * @return Player[]
	 */
	private function getPlayerBroadcastSubscribers(string $channelId) : array{
		/** @var Player[] $players */
		$players = [];
		foreach($this->broadcastSubscribers[$channelId] as $subscriber){
			if($subscriber instanceof Player){
				$players[spl_object_id($subscriber)] = $subscriber;
			}
		}
		return $players;
	}

	/**
	 * @param Player[]|null $recipients
	 */
	public function broadcastTip(string $tip, ?array $recipients = null) : int{
		$recipients = $recipients ?? $this->getPlayerBroadcastSubscribers(self::BROADCAST_CHANNEL_USERS);

		foreach($recipients as $recipient){
			$recipient->sendTip($tip);
		}

		return count($recipients);
	}

	/**
	 * @param Player[]|null $recipients
	 */
	public function broadcastPopup(string $popup, ?array $recipients = null) : int{
		$recipients = $recipients ?? $this->getPlayerBroadcastSubscribers(self::BROADCAST_CHANNEL_USERS);

		foreach($recipients as $recipient){
			$recipient->sendPopup($popup);
		}

		return count($recipients);
	}

	/**
	 * @param int           $fadeIn Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int           $stay Duration in ticks to stay on screen for
	 * @param int           $fadeOut Duration in ticks for fade-out.
	 * @param Player[]|null $recipients
	 */
	public function broadcastTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1, ?array $recipients = null) : int{
		$recipients = $recipients ?? $this->getPlayerBroadcastSubscribers(self::BROADCAST_CHANNEL_USERS);

		foreach($recipients as $recipient){
			$recipient->sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
		}

		return count($recipients);
	}

	/**
	 * @param Player[]            $players
	 * @param ClientboundPacket[] $packets
	 */
	public function broadcastPackets(array $players, array $packets) : bool{
		if(count($packets) === 0){
			throw new \InvalidArgumentException("Cannot broadcast empty list of packets");
		}

		return Timings::$broadcastPackets->time(function() use ($players, $packets) : bool{
			/** @var NetworkSession[] $recipients */
			$recipients = [];
			foreach($players as $player){
				if($player->isConnected()){
					$recipients[] = $player->getNetworkSession();
				}
			}
			if(count($recipients) === 0){
				return false;
			}

			$ev = new DataPacketSendEvent($recipients, $packets);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
			$recipients = $ev->getTargets();

			/** @var PacketBroadcaster[] $broadcasters */
			$broadcasters = [];
			/** @var NetworkSession[][] $broadcasterTargets */
			$broadcasterTargets = [];
			foreach($recipients as $recipient){
				$broadcaster = $recipient->getBroadcaster();
				$broadcasters[spl_object_id($broadcaster)] = $broadcaster;
				$broadcasterTargets[spl_object_id($broadcaster)][] = $recipient;
			}
			foreach($broadcasters as $broadcaster){
				$broadcaster->broadcastPackets($broadcasterTargets[spl_object_id($broadcaster)], $packets);
			}

			return true;
		});
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param bool|null $sync Compression on the main thread (true) or workers (false). Default is automatic (null).
	 */
	public function prepareBatch(PacketBatch $stream, Compressor $compressor, ?bool $sync = null) : CompressBatchPromise{
		try{
			Timings::$playerNetworkSendCompress->startTiming();

			$buffer = $stream->getBuffer();

			if($sync === null){
				$sync = !($this->networkCompressionAsync && $compressor->willCompress($buffer));
			}

			$promise = new CompressBatchPromise();
			if(!$sync){
				$task = new CompressBatchTask($buffer, $promise, $compressor);
				$this->asyncPool->submitTask($task);
			}else{
				$promise->resolve($compressor->compress($buffer));
			}

			return $promise;
		}finally{
			Timings::$playerNetworkSendCompress->stopTiming();
		}
	}

	public function enablePlugins(PluginEnableOrder $type) : void{
		foreach($this->pluginManager->getPlugins() as $plugin){
			if(!$plugin->isEnabled() and $plugin->getDescription()->getOrder()->equals($type)){
				$this->pluginManager->enablePlugin($plugin);
			}
		}

		if($type->equals(PluginEnableOrder::POSTWORLD())){
			$this->commandMap->registerServerAliases();
		}
	}

	/**
	 * Executes a command from a CommandSender
	 */
	public function dispatchCommand(CommandSender $sender, string $commandLine, bool $internal = false) : bool{
		if(!$internal){
			$ev = new CommandEvent($sender, $commandLine);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}

			$commandLine = $ev->getCommand();
		}

		if($this->commandMap->dispatch($sender, $commandLine)){
			return true;
		}

		$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_NOTFOUND));

		return false;
	}

	/**
	 * Shuts the server down correctly
	 */
	public function shutdown() : void{
		$this->isRunning = false;
	}

	public function forceShutdown() : void{
		if($this->hasStopped){
			return;
		}

		if($this->doTitleTick){
			echo "\x1b]0;\x07";
		}

		try{
			if(!$this->isRunning()){
				$this->sendUsage(SendUsageTask::TYPE_CLOSE);
			}

			$this->hasStopped = true;

			$this->shutdown();

			if($this->pluginManager instanceof PluginManager){
				$this->getLogger()->debug("Disabling all plugins");
				$this->pluginManager->disablePlugins();
			}

			if($this->network instanceof Network){
				$this->network->getSessionManager()->close($this->configGroup->getPropertyString("settings.shutdown-message", "Server closed"));
			}

			if($this->worldManager instanceof WorldManager){
				$this->getLogger()->debug("Unloading all worlds");
				foreach($this->worldManager->getWorlds() as $world){
					$this->worldManager->unloadWorld($world, true);
				}
			}

			$this->getLogger()->debug("Removing event handlers");
			HandlerListManager::global()->unregisterAll();

			if($this->asyncPool instanceof AsyncPool){
				$this->getLogger()->debug("Shutting down async task worker pool");
				$this->asyncPool->shutdown();
			}

			if($this->configGroup !== null){
				$this->getLogger()->debug("Saving properties");
				$this->configGroup->save();
			}

			if($this->console instanceof ConsoleReaderThread){
				$this->getLogger()->debug("Closing console");
				$this->console->shutdown();
				$this->console->notify();
			}

			if($this->network instanceof Network){
				$this->getLogger()->debug("Stopping network interfaces");
				foreach($this->network->getInterfaces() as $interface){
					$this->getLogger()->debug("Stopping network interface " . get_class($interface));
					$this->network->unregisterInterface($interface);
				}
			}
		}catch(\Throwable $e){
			$this->logger->logException($e);
			$this->logger->emergency("Crashed while crashing, killing process");
			@Process::kill(Process::pid());
		}

	}

	/**
	 * @return QueryInfo
	 */
	public function getQueryInformation(){
		return $this->queryInfo;
	}

	/**
	 * @param mixed[][]|null $trace
	 * @phpstan-param list<array<string, mixed>>|null $trace
	 */
	public function exceptionHandler(\Throwable $e, $trace = null) : void{
		while(@ob_end_flush()){}
		global $lastError;

		if($trace === null){
			$trace = $e->getTrace();
		}

		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errline = $e->getLine();

		$errstr = preg_replace('/\s+/', ' ', trim($errstr));

		$errfile = Filesystem::cleanPath($errfile);

		$this->logger->logException($e, $trace);

		$lastError = [
			"type" => get_class($e),
			"message" => $errstr,
			"fullFile" => $e->getFile(),
			"file" => $errfile,
			"line" => $errline,
			"trace" => $trace
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}

	public function crashDump() : void{
		while(@ob_end_flush()){}
		if(!$this->isRunning){
			return;
		}
		if($this->sendUsageTicker > 0){
			$this->sendUsage(SendUsageTask::TYPE_CLOSE);
		}
		$this->hasStopped = false;

		ini_set("error_reporting", '0');
		ini_set("memory_limit", '-1'); //Fix error dump not dumped on memory problems
		try{
			$this->logger->emergency($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_CRASH_CREATE));
			$dump = new CrashDump($this);

			$this->logger->emergency($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_CRASH_SUBMIT, [$dump->getPath()]));

			if($this->configGroup->getPropertyBool("auto-report.enabled", true)){
				$report = true;

				$stamp = Path::join($this->getDataPath(), "crashdumps", ".last_crash");
				$crashInterval = 120; //2 minutes
				if(file_exists($stamp) and !($report = (filemtime($stamp) + $crashInterval < time()))){
					$this->logger->debug("Not sending crashdump due to last crash less than $crashInterval seconds ago");
				}
				@touch($stamp); //update file timestamp

				$plugin = $dump->getData()["plugin"];
				if(is_string($plugin)){
					$p = $this->pluginManager->getPlugin($plugin);
					if($p instanceof Plugin and !($p->getPluginLoader() instanceof PharPluginLoader)){
						$this->logger->debug("Not sending crashdump due to caused by non-phar plugin");
						$report = false;
					}
				}

				if($dump->getData()["error"]["type"] === \ParseError::class){
					$report = false;
				}

				if(strrpos(VersionInfo::getGitHash(), "-dirty") !== false or VersionInfo::getGitHash() === str_repeat("00", 20)){
					$this->logger->debug("Not sending crashdump due to locally modified");
					$report = false; //Don't send crashdumps for locally modified builds
				}

				if($report){
					$url = ($this->configGroup->getPropertyBool("auto-report.use-https", true) ? "https" : "http") . "://" . $this->configGroup->getPropertyString("auto-report.host", "crash.pmmp.io") . "/submit/api";
					$postUrlError = "Unknown error";
					$reply = Internet::postURL($url, [
						"report" => "yes",
						"name" => $this->getName() . " " . $this->getPocketMineVersion(),
						"email" => "crash@pocketmine.net",
						"reportPaste" => base64_encode($dump->getEncodedData())
					], 10, [], $postUrlError);

					if($reply !== null and ($data = json_decode($reply->getBody())) !== null){
						if(isset($data->crashId) and isset($data->crashUrl)){
							$reportId = $data->crashId;
							$reportUrl = $data->crashUrl;
							$this->logger->emergency($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_CRASH_ARCHIVE, [$reportUrl, $reportId]));
						}elseif(isset($data->error)){
							$this->logger->emergency("Automatic crash report submission failed: $data->error");
						}
					}else{
						$this->logger->emergency("Failed to communicate with crash archive: $postUrlError");
					}
				}
			}
		}catch(\Throwable $e){
			$this->logger->logException($e);
			try{
				$this->logger->critical($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_CRASH_ERROR, [$e->getMessage()]));
			}catch(\Throwable $e){}
		}

		$this->forceShutdown();
		$this->isRunning = false;

		//Force minimum uptime to be >= 120 seconds, to reduce the impact of spammy crash loops
		$spacing = ((int) $this->startTime) - time() + 120;
		if($spacing > 0){
			echo "--- Waiting $spacing seconds to throttle automatic restart (you can kill the process safely now) ---" . PHP_EOL;
			sleep($spacing);
		}
		@Process::kill(Process::pid());
		exit(1);
	}

	/**
	 * @return mixed[]
	 */
	public function __debugInfo() : array{
		return [];
	}

	public function getTickSleeper() : SleeperHandler{
		return $this->tickSleeper;
	}

	private function tickProcessor() : void{
		$this->nextTick = microtime(true);

		while($this->isRunning){
			$this->tick();

			//sleeps are self-correcting - if we undersleep 1ms on this tick, we'll sleep an extra ms on the next tick
			$this->tickSleeper->sleepUntil($this->nextTick);
		}
	}

	public function addOnlinePlayer(Player $player) : void{
		foreach($this->playerList as $p){
			$p->getNetworkSession()->onPlayerAdded($player);
		}
		$rawUUID = $player->getUniqueId()->getBytes();
		$this->playerList[$rawUUID] = $player;

		if($this->sendUsageTicker > 0){
			$this->uniquePlayers[$rawUUID] = $rawUUID;
		}
	}

	public function removeOnlinePlayer(Player $player) : void{
		if(isset($this->playerList[$rawUUID = $player->getUniqueId()->getBytes()])){
			unset($this->playerList[$rawUUID]);
			foreach($this->playerList as $p){
				$p->getNetworkSession()->onPlayerRemoved($player);
			}
		}
	}

	public function sendUsage(int $type = SendUsageTask::TYPE_STATUS) : void{
		if($this->configGroup->getPropertyBool("anonymous-statistics.enabled", true)){
			$this->asyncPool->submitTask(new SendUsageTask($this, $type, $this->uniquePlayers));
		}
		$this->uniquePlayers = [];
	}

	/**
	 * @return Language
	 */
	public function getLanguage(){
		return $this->language;
	}

	public function isLanguageForced() : bool{
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

	private function titleTick() : void{
		Timings::$titleTick->startTiming();
		$d = Process::getRealMemoryUsage();

		$u = Process::getAdvancedMemoryUsage();
		$usage = sprintf("%g/%g/%g/%g MB @ %d threads", round(($u[0] / 1024) / 1024, 2), round(($d[0] / 1024) / 1024, 2), round(($u[1] / 1024) / 1024, 2), round(($u[2] / 1024) / 1024, 2), Process::getThreadCount());

		$online = count($this->playerList);
		$connecting = $this->network->getConnectionCount() - $online;
		$bandwidthStats = $this->network->getBandwidthTracker();

		echo "\x1b]0;" . $this->getName() . " " .
			$this->getPocketMineVersion() .
			" | Online $online/" . $this->getMaxPlayers() .
			($connecting > 0 ? " (+$connecting connecting)" : "") .
			" | Memory " . $usage .
			" | U " . round($bandwidthStats->getSend()->getAverageBytes() / 1024, 2) .
			" D " . round($bandwidthStats->getReceive()->getAverageBytes() / 1024, 2) .
			" kB/s | TPS " . $this->getTicksPerSecondAverage() .
			" | Load " . $this->getTickUsageAverage() . "%\x07";

		Timings::$titleTick->stopTiming();
	}

	/**
	 * Tries to execute a server tick
	 */
	private function tick() : void{
		$tickTime = microtime(true);
		if(($tickTime - $this->nextTick) < -0.025){ //Allow half a tick of diff
			return;
		}

		Timings::$serverTick->startTiming();

		++$this->tickCounter;

		Timings::$scheduler->startTiming();
		$this->pluginManager->tickSchedulers($this->tickCounter);
		Timings::$scheduler->stopTiming();

		Timings::$schedulerAsync->startTiming();
		$this->asyncPool->collectTasks();
		Timings::$schedulerAsync->stopTiming();

		$this->worldManager->tick($this->tickCounter);

		Timings::$connection->startTiming();
		$this->network->tick();
		Timings::$connection->stopTiming();

		if(($this->tickCounter % 20) === 0){
			if($this->doTitleTick){
				$this->titleTick();
			}
			$this->currentTPS = 20;
			$this->currentUse = 0;

			$queryRegenerateEvent = new QueryRegenerateEvent(new QueryInfo($this));
			$queryRegenerateEvent->call();
			$this->queryInfo = $queryRegenerateEvent->getQueryInfo();

			$this->network->updateName();
			$this->network->getBandwidthTracker()->rotateAverageHistory();
		}

		if($this->sendUsageTicker > 0 and --$this->sendUsageTicker === 0){
			$this->sendUsageTicker = 6000;
			$this->sendUsage(SendUsageTask::TYPE_STATUS);
		}

		if(($this->tickCounter % 100) === 0){
			foreach($this->worldManager->getWorlds() as $world){
				$world->clearCache();
			}

			if($this->getTicksPerSecondAverage() < 12){
				$this->logger->warning($this->getLanguage()->translateString(KnownTranslationKeys::POCKETMINE_SERVER_TICKOVERLOAD));
			}
		}

		$this->getMemoryManager()->check();

		Timings::$serverTick->stopTiming();

		$now = microtime(true);
		$this->currentTPS = min(20, 1 / max(0.001, $now - $tickTime));
		$this->currentUse = min(1, ($now - $tickTime) / 0.05);

		TimingsHandler::tick($this->currentTPS <= $this->profilingTickRate);

		$idx = $this->tickCounter % 20;
		$this->tickAverage[$idx] = $this->currentTPS;
		$this->useAverage[$idx] = $this->currentUse;

		if(($this->nextTick - $tickTime) < -1){
			$this->nextTick = $tickTime;
		}else{
			$this->nextTick += 0.05;
		}
	}

	/**
	 * Called when something attempts to serialize the server instance.
	 *
	 * @throws \BadMethodCallException because Server instances cannot be serialized
	 */
	public function __sleep(){
		throw new \BadMethodCallException("Cannot serialize Server instance");
	}
}
