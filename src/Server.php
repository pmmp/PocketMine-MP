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
use pocketmine\console\ConsoleReaderChildProcessDaemon;
use pocketmine\crafting\CraftingManager;
use pocketmine\crafting\CraftingManagerFromDataHelper;
use pocketmine\crash\CrashDump;
use pocketmine\crash\CrashDumpRenderer;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\Location;
use pocketmine\event\HandlerListManager;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Language;
use pocketmine\lang\LanguageNotFoundException;
use pocketmine\lang\Translatable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\CompressBatchTask;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\encryption\EncryptionContext;
use pocketmine\network\mcpe\EntityEventBroadcaster;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\PacketBroadcaster;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\CompressionAlgorithm;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\mcpe\StandardEntityEventBroadcaster;
use pocketmine\network\mcpe\StandardPacketBroadcaster;
use pocketmine\network\Network;
use pocketmine\network\NetworkInterfaceStartException;
use pocketmine\network\query\DedicatedQueryNetworkInterface;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\query\QueryInfo;
use pocketmine\network\upnp\UPnPNetworkInterface;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\DatFilePlayerDataProvider;
use pocketmine\player\GameMode;
use pocketmine\player\OfflinePlayer;
use pocketmine\player\Player;
use pocketmine\player\PlayerDataLoadException;
use pocketmine\player\PlayerDataProvider;
use pocketmine\player\PlayerDataSaveException;
use pocketmine\player\PlayerInfo;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\PluginEnableOrder;
use pocketmine\plugin\PluginGraylist;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\ScriptPluginLoader;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\scheduler\AsyncPool;
use pocketmine\snooze\SleeperHandler;
use pocketmine\stats\SendUsageTask;
use pocketmine\thread\log\AttachableThreadSafeLogger;
use pocketmine\thread\ThreadCrashException;
use pocketmine\thread\ThreadSafeClassLoader;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\updater\UpdateChecker;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\BroadcastLoggerForwarder;
use pocketmine\utils\Config;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Internet;
use pocketmine\utils\MainLogger;
use pocketmine\utils\NotCloneable;
use pocketmine\utils\NotSerializable;
use pocketmine\utils\Process;
use pocketmine\utils\SignalHandler;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\world\format\io\WritableWorldProviderManagerEntry;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\InvalidGeneratorOptionsException;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use pocketmine\world\WorldManager;
use pocketmine\YmlServerProperties as Yml;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Filesystem\Path;
use function array_fill;
use function array_sum;
use function base64_encode;
use function chr;
use function cli_set_process_title;
use function copy;
use function count;
use function date;
use function fclose;
use function file_exists;
use function file_put_contents;
use function filemtime;
use function fopen;
use function get_class;
use function ini_set;
use function is_array;
use function is_dir;
use function is_int;
use function is_object;
use function is_resource;
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
use function strval;
use function time;
use function touch;
use function trim;
use function yaml_parse;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use const PHP_INT_MAX;

/**
 * The class that manages everything
 */
class Server{
	use NotCloneable;
	use NotSerializable;

	public const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	public const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	public const DEFAULT_SERVER_NAME = VersionInfo::NAME . " Server";
	public const DEFAULT_MAX_PLAYERS = 20;
	public const DEFAULT_PORT_IPV4 = 19132;
	public const DEFAULT_PORT_IPV6 = 19133;
	public const DEFAULT_MAX_VIEW_DISTANCE = 16;

	/**
	 * Worlds, network, commands and most other things are polled this many times per second on average.
	 * Between ticks, the server will sleep to ensure that the average tick rate is maintained.
	 * It may wake up between ticks if a Snooze notification source is triggered (e.g. to process network packets).
	 */
	public const TARGET_TICKS_PER_SECOND = 20;
	/**
	 * The average time between ticks, in seconds.
	 */
	public const TARGET_SECONDS_PER_TICK = 1 / self::TARGET_TICKS_PER_SECOND;
	public const TARGET_NANOSECONDS_PER_TICK = 1_000_000_000 / self::TARGET_TICKS_PER_SECOND;

	/**
	 * The TPS threshold below which the server will generate log warnings.
	 */
	private const TPS_OVERLOAD_WARNING_THRESHOLD = self::TARGET_TICKS_PER_SECOND * 0.6;

	private const TICKS_PER_WORLD_CACHE_CLEAR = 5 * self::TARGET_TICKS_PER_SECOND;
	private const TICKS_PER_TPS_OVERLOAD_WARNING = 5 * self::TARGET_TICKS_PER_SECOND;
	private const TICKS_PER_STATS_REPORT = 300 * self::TARGET_TICKS_PER_SECOND;

	private const DEFAULT_ASYNC_COMPRESSION_THRESHOLD = 10_000;

	private static ?Server $instance = null;

	private TimeTrackingSleeperHandler $tickSleeper;

	private BanList $banByName;

	private BanList $banByIP;

	private Config $operators;

	private Config $whitelist;

	private bool $isRunning = true;

	private bool $hasStopped = false;

	private PluginManager $pluginManager;

	private float $profilingTickRate = self::TARGET_TICKS_PER_SECOND;

	private UpdateChecker $updater;

	private AsyncPool $asyncPool;

	/** Counts the ticks since the server start */
	private int $tickCounter = 0;
	private float $nextTick = 0;
	/** @var float[] */
	private array $tickAverage;
	/** @var float[] */
	private array $useAverage;
	private float $currentTPS = self::TARGET_TICKS_PER_SECOND;
	private float $currentUse = 0;
	private float $startTime;

	private bool $doTitleTick = true;

	private int $sendUsageTicker = 0;

	private MemoryManager $memoryManager;

	private ?ConsoleReaderChildProcessDaemon $console = null;
	private ?ConsoleCommandSender $consoleSender = null;

	private SimpleCommandMap $commandMap;

	private CraftingManager $craftingManager;

	private ResourcePackManager $resourceManager;

	private WorldManager $worldManager;

	private int $maxPlayers;

	private bool $onlineMode = true;

	private Network $network;
	private bool $networkCompressionAsync = true;
	private int $networkCompressionAsyncThreshold = self::DEFAULT_ASYNC_COMPRESSION_THRESHOLD;

	private Language $language;
	private bool $forceLanguage = false;

	private UuidInterface $serverID;

	private string $dataPath;
	private string $pluginPath;

	private PlayerDataProvider $playerDataProvider;

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $uniquePlayers = [];

	private QueryInfo $queryInfo;

	private ServerConfigGroup $configGroup;

	/** @var Player[] */
	private array $playerList = [];

	private SignalHandler $signalHandler;

	/**
	 * @var CommandSender[][]
	 * @phpstan-var array<string, array<int, CommandSender>>
	 */
	private array $broadcastSubscribers = [];

	public function getName() : string{
		return VersionInfo::NAME;
	}

	public function isRunning() : bool{
		return $this->isRunning;
	}

	public function getPocketMineVersion() : string{
		return VersionInfo::VERSION()->getFullVersion(true);
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
		return $this->configGroup->getConfigInt(ServerProperties::SERVER_PORT_IPV4, self::DEFAULT_PORT_IPV4);
	}

	public function getPortV6() : int{
		return $this->configGroup->getConfigInt(ServerProperties::SERVER_PORT_IPV6, self::DEFAULT_PORT_IPV6);
	}

	public function getViewDistance() : int{
		return max(2, $this->configGroup->getConfigInt(ServerProperties::VIEW_DISTANCE, self::DEFAULT_MAX_VIEW_DISTANCE));
	}

	/**
	 * Returns a view distance up to the currently-allowed limit.
	 */
	public function getAllowedViewDistance(int $distance) : int{
		return max(2, min($distance, $this->memoryManager->getViewDistance($this->getViewDistance())));
	}

	public function getIp() : string{
		$str = $this->configGroup->getConfigString(ServerProperties::SERVER_IPV4);
		return $str !== "" ? $str : "0.0.0.0";
	}

	public function getIpV6() : string{
		$str = $this->configGroup->getConfigString(ServerProperties::SERVER_IPV6);
		return $str !== "" ? $str : "::";
	}

	public function getServerUniqueId() : UuidInterface{
		return $this->serverID;
	}

	public function getGamemode() : GameMode{
		return GameMode::fromString($this->configGroup->getConfigString(ServerProperties::GAME_MODE)) ?? GameMode::SURVIVAL;
	}

	public function getForceGamemode() : bool{
		return $this->configGroup->getConfigBool(ServerProperties::FORCE_GAME_MODE, false);
	}

	/**
	 * Returns Server global difficulty. Note that this may be overridden in individual worlds.
	 */
	public function getDifficulty() : int{
		return $this->configGroup->getConfigInt(ServerProperties::DIFFICULTY, World::DIFFICULTY_NORMAL);
	}

	public function hasWhitelist() : bool{
		return $this->configGroup->getConfigBool(ServerProperties::WHITELIST, false);
	}

	public function isHardcore() : bool{
		return $this->configGroup->getConfigBool(ServerProperties::HARDCORE, false);
	}

	public function getMotd() : string{
		return $this->configGroup->getConfigString(ServerProperties::MOTD, self::DEFAULT_SERVER_NAME);
	}

	public function getLoader() : ThreadSafeClassLoader{
		return $this->autoloader;
	}

	public function getLogger() : AttachableThreadSafeLogger{
		return $this->logger;
	}

	public function getUpdater() : UpdateChecker{
		return $this->updater;
	}

	public function getPluginManager() : PluginManager{
		return $this->pluginManager;
	}

	public function getCraftingManager() : CraftingManager{
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

	public function getCommandMap() : SimpleCommandMap{
		return $this->commandMap;
	}

	/**
	 * @return Player[]
	 */
	public function getOnlinePlayers() : array{
		return $this->playerList;
	}

	public function shouldSavePlayerData() : bool{
		return $this->configGroup->getPropertyBool(Yml::PLAYER_SAVE_PLAYER_DATA, true);
	}

	public function getOfflinePlayer(string $name) : Player|OfflinePlayer|null{
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);

		if($result === null){
			$result = new OfflinePlayer($name, $this->getOfflinePlayerData($name));
		}

		return $result;
	}

	/**
	 * Returns whether the server has stored any saved data for this player.
	 */
	public function hasOfflinePlayerData(string $name) : bool{
		return $this->playerDataProvider->hasData($name);
	}

	public function getOfflinePlayerData(string $name) : ?CompoundTag{
		return Timings::$syncPlayerDataLoad->time(function() use ($name) : ?CompoundTag{
			try{
				return $this->playerDataProvider->loadData($name);
			}catch(PlayerDataLoadException $e){
				$this->logger->debug("Failed to load player data for $name: " . $e->getMessage());
				$this->logger->error($this->language->translate(KnownTranslationFactory::pocketmine_data_playerCorrupted($name)));
				return null;
			}
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
				try{
					$this->playerDataProvider->saveData($name, $ev->getSaveData());
				}catch(PlayerDataSaveException $e){
					$this->logger->critical($this->language->translate(KnownTranslationFactory::pocketmine_data_saveError($name, $e->getMessage())));
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

		if($offlinePlayerData !== null && ($world = $this->worldManager->getWorldByName($offlinePlayerData->getString(Player::TAG_LEVEL, ""))) !== null){
			$playerPos = EntityDataHelper::parseLocation($offlinePlayerData, $world);
		}else{
			$world = $this->worldManager->getDefaultWorld();
			if($world === null){
				throw new AssumptionFailedError("Default world should always be loaded");
			}
			$playerPos = null;
		}
		/** @phpstan-var PromiseResolver<Player> $playerPromiseResolver */
		$playerPromiseResolver = new PromiseResolver();

		$createPlayer = function(Location $location) use ($playerPromiseResolver, $class, $session, $playerInfo, $authenticated, $offlinePlayerData) : void{
			/** @see Player::__construct() */
			$player = new $class($this, $session, $playerInfo, $authenticated, $location, $offlinePlayerData);
			if(!$player->hasPlayedBefore()){
				$player->onGround = true; //TODO: this hack is needed for new players in-air ticks - they don't get detected as on-ground until they move
			}
			$playerPromiseResolver->resolve($player);
		};

		if($playerPos === null){ //new player or no valid position due to world not being loaded
			$world->requestSafeSpawn()->onCompletion(
				function(Position $spawn) use ($createPlayer, $playerPromiseResolver, $session, $world) : void{
					if(!$session->isConnected()){
						$playerPromiseResolver->reject();
						return;
					}
					$createPlayer(Location::fromObject($spawn, $world));
				},
				function() use ($playerPromiseResolver, $session) : void{
					if($session->isConnected()){
						$session->disconnectWithError(KnownTranslationFactory::pocketmine_disconnect_error_respawn());
					}
					$playerPromiseResolver->reject();
				}
			);
		}else{ //returning player with a valid position - safe spawn not required
			$createPlayer($playerPos);
		}

		return $playerPromiseResolver->getPromise();
	}

	/**
	 * @deprecated This method's results are unpredictable. The string "Steve" will return the player named "SteveJobs",
	 * until another player named "SteveJ" joins the server, at which point it will return that player instead. Prefer
	 * filtering the results of {@link Server::getOnlinePlayers()} yourself.
	 *
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

	public function getNameBans() : BanList{
		return $this->banByName;
	}

	public function getIPBans() : BanList{
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
		$lowercaseName = strtolower($name);
		foreach($this->operators->getAll() as $operatorName => $_){
			$operatorName = (string) $operatorName;
			if($lowercaseName === strtolower($operatorName)){
				$this->operators->remove($operatorName);
			}
		}

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
		return !$this->hasWhitelist() || $this->operators->exists($name, true) || $this->whitelist->exists($name, true);
	}

	public function isOp(string $name) : bool{
		return $this->operators->exists($name, true);
	}

	public function getWhitelisted() : Config{
		return $this->whitelist;
	}

	public function getOps() : Config{
		return $this->operators;
	}

	/**
	 * @return string[][]
	 * @phpstan-return array<string, list<string>>
	 */
	public function getCommandAliases() : array{
		$section = $this->configGroup->getProperty(Yml::ALIASES);
		$result = [];
		if(is_array($section)){
			foreach(Utils::promoteKeys($section) as $key => $value){
				//TODO: more validation needed here
				//key might not be a string, value might not be list<string>
				$commands = [];
				if(is_array($value)){
					$commands = $value;
				}else{
					$commands[] = (string) $value;
				}

				$result[(string) $key] = $commands;
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

	public function __construct(
		private ThreadSafeClassLoader $autoloader,
		private AttachableThreadSafeLogger $logger,
		string $dataPath,
		string $pluginPath
	){
		if(self::$instance !== null){
			throw new \LogicException("Only one server instance can exist at once");
		}
		self::$instance = $this;
		$this->startTime = microtime(true);
		$this->tickAverage = array_fill(0, self::TARGET_TICKS_PER_SECOND, self::TARGET_TICKS_PER_SECOND);
		$this->useAverage = array_fill(0, self::TARGET_TICKS_PER_SECOND, 0);

		Timings::init();
		$this->tickSleeper = new TimeTrackingSleeperHandler(Timings::$serverInterrupts);

		$this->signalHandler = new SignalHandler(function() : void{
			$this->logger->info("Received signal interrupt, stopping the server");
			$this->shutdown();
		});

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
				$content = Filesystem::fileGetContents(Path::join(\pocketmine\RESOURCE_PATH, "pocketmine.yml"));
				if(VersionInfo::IS_DEVELOPMENT_BUILD){
					$content = str_replace("preferred-channel: stable", "preferred-channel: beta", $content);
				}
				@file_put_contents($pocketmineYmlPath, $content);
			}

			$this->configGroup = new ServerConfigGroup(
				new Config($pocketmineYmlPath, Config::YAML, []),
				new Config(Path::join($this->dataPath, "server.properties"), Config::PROPERTIES, [
					ServerProperties::MOTD => self::DEFAULT_SERVER_NAME,
					ServerProperties::SERVER_PORT_IPV4 => self::DEFAULT_PORT_IPV4,
					ServerProperties::SERVER_PORT_IPV6 => self::DEFAULT_PORT_IPV6,
					ServerProperties::ENABLE_IPV6 => true,
					ServerProperties::WHITELIST => false,
					ServerProperties::MAX_PLAYERS => self::DEFAULT_MAX_PLAYERS,
					ServerProperties::GAME_MODE => GameMode::SURVIVAL->name, //TODO: this probably shouldn't use the enum name directly
					ServerProperties::FORCE_GAME_MODE => false,
					ServerProperties::HARDCORE => false,
					ServerProperties::PVP => true,
					ServerProperties::DIFFICULTY => World::DIFFICULTY_NORMAL,
					ServerProperties::DEFAULT_WORLD_GENERATOR_SETTINGS => "",
					ServerProperties::DEFAULT_WORLD_NAME => "world",
					ServerProperties::DEFAULT_WORLD_SEED => "",
					ServerProperties::DEFAULT_WORLD_GENERATOR => "DEFAULT",
					ServerProperties::ENABLE_QUERY => true,
					ServerProperties::AUTO_SAVE => true,
					ServerProperties::VIEW_DISTANCE => self::DEFAULT_MAX_VIEW_DISTANCE,
					ServerProperties::XBOX_AUTH => true,
					ServerProperties::LANGUAGE => "eng"
				])
			);

			$debugLogLevel = $this->configGroup->getPropertyInt(Yml::DEBUG_LEVEL, 1);
			if($this->logger instanceof MainLogger){
				$this->logger->setLogDebug($debugLogLevel > 1);
			}

			$this->forceLanguage = $this->configGroup->getPropertyBool(Yml::SETTINGS_FORCE_LANGUAGE, false);
			$selectedLang = $this->configGroup->getConfigString(ServerProperties::LANGUAGE, $this->configGroup->getPropertyString("settings.language", Language::FALLBACK_LANGUAGE));
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

			$this->logger->info($this->language->translate(KnownTranslationFactory::language_selected($this->language->getName(), $this->language->getLang())));

			if(VersionInfo::IS_DEVELOPMENT_BUILD){
				if(!$this->configGroup->getPropertyBool(Yml::SETTINGS_ENABLE_DEV_BUILDS, false)){
					$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_server_devBuild_error1(VersionInfo::NAME)));
					$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_server_devBuild_error2()));
					$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_server_devBuild_error3()));
					$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_server_devBuild_error4(Yml::SETTINGS_ENABLE_DEV_BUILDS)));
					$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_server_devBuild_error5("https://github.com/pmmp/PocketMine-MP/releases")));
					$this->forceShutdownExit();

					return;
				}

				$this->logger->warning(str_repeat("-", 40));
				$this->logger->warning($this->language->translate(KnownTranslationFactory::pocketmine_server_devBuild_warning1(VersionInfo::NAME)));
				$this->logger->warning($this->language->translate(KnownTranslationFactory::pocketmine_server_devBuild_warning2()));
				$this->logger->warning($this->language->translate(KnownTranslationFactory::pocketmine_server_devBuild_warning3()));
				$this->logger->warning(str_repeat("-", 40));
			}

			$this->memoryManager = new MemoryManager($this);

			$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_start(TextFormat::AQUA . $this->getVersion() . TextFormat::RESET)));

			if(($poolSize = $this->configGroup->getPropertyString(Yml::SETTINGS_ASYNC_WORKERS, "auto")) === "auto"){
				$poolSize = 2;
				$processors = Utils::getCoreCount() - 2;

				if($processors > 0){
					$poolSize = max(1, $processors);
				}
			}else{
				$poolSize = max(1, (int) $poolSize);
			}

			$this->asyncPool = new AsyncPool($poolSize, max(-1, $this->configGroup->getPropertyInt(Yml::MEMORY_ASYNC_WORKER_HARD_LIMIT, 256)), $this->autoloader, $this->logger, $this->tickSleeper);

			$netCompressionThreshold = -1;
			if($this->configGroup->getPropertyInt(Yml::NETWORK_BATCH_THRESHOLD, 256) >= 0){
				$netCompressionThreshold = $this->configGroup->getPropertyInt(Yml::NETWORK_BATCH_THRESHOLD, 256);
			}
			if($netCompressionThreshold < 0){
				$netCompressionThreshold = null;
			}

			$netCompressionLevel = $this->configGroup->getPropertyInt(Yml::NETWORK_COMPRESSION_LEVEL, 6);
			if($netCompressionLevel < 1 || $netCompressionLevel > 9){
				$this->logger->warning("Invalid network compression level $netCompressionLevel set, setting to default 6");
				$netCompressionLevel = 6;
			}
			ZlibCompressor::setInstance(new ZlibCompressor($netCompressionLevel, $netCompressionThreshold, ZlibCompressor::DEFAULT_MAX_DECOMPRESSION_SIZE));

			$this->networkCompressionAsync = $this->configGroup->getPropertyBool(Yml::NETWORK_ASYNC_COMPRESSION, true);
			$this->networkCompressionAsyncThreshold = max(
				$this->configGroup->getPropertyInt(Yml::NETWORK_ASYNC_COMPRESSION_THRESHOLD, self::DEFAULT_ASYNC_COMPRESSION_THRESHOLD),
				$netCompressionThreshold ?? self::DEFAULT_ASYNC_COMPRESSION_THRESHOLD
			);

			EncryptionContext::$ENABLED = $this->configGroup->getPropertyBool(Yml::NETWORK_ENABLE_ENCRYPTION, true);

			$this->doTitleTick = $this->configGroup->getPropertyBool(Yml::CONSOLE_TITLE_TICK, true) && Terminal::hasFormattingCodes();

			$this->operators = new Config(Path::join($this->dataPath, "ops.txt"), Config::ENUM);
			$this->whitelist = new Config(Path::join($this->dataPath, "white-list.txt"), Config::ENUM);

			$bannedTxt = Path::join($this->dataPath, "banned.txt");
			$bannedPlayersTxt = Path::join($this->dataPath, "banned-players.txt");
			if(file_exists($bannedTxt) && !file_exists($bannedPlayersTxt)){
				@rename($bannedTxt, $bannedPlayersTxt);
			}
			@touch($bannedPlayersTxt);
			$this->banByName = new BanList($bannedPlayersTxt);
			$this->banByName->load();
			$bannedIpsTxt = Path::join($this->dataPath, "banned-ips.txt");
			@touch($bannedIpsTxt);
			$this->banByIP = new BanList($bannedIpsTxt);
			$this->banByIP->load();

			$this->maxPlayers = $this->configGroup->getConfigInt(ServerProperties::MAX_PLAYERS, self::DEFAULT_MAX_PLAYERS);

			$this->onlineMode = $this->configGroup->getConfigBool(ServerProperties::XBOX_AUTH, true);
			if($this->onlineMode){
				$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_auth_enabled()));
			}else{
				$this->logger->warning($this->language->translate(KnownTranslationFactory::pocketmine_server_auth_disabled()));
				$this->logger->warning($this->language->translate(KnownTranslationFactory::pocketmine_server_authWarning()));
				$this->logger->warning($this->language->translate(KnownTranslationFactory::pocketmine_server_authProperty_disabled()));
			}

			if($this->configGroup->getConfigBool(ServerProperties::HARDCORE, false) && $this->getDifficulty() < World::DIFFICULTY_HARD){
				$this->configGroup->setConfigInt(ServerProperties::DIFFICULTY, World::DIFFICULTY_HARD);
			}

			@cli_set_process_title($this->getName() . " " . $this->getPocketMineVersion());

			$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());

			$this->logger->debug("Server unique id: " . $this->getServerUniqueId());
			$this->logger->debug("Machine unique id: " . Utils::getMachineUniqueId());

			$this->network = new Network($this->logger);
			$this->network->setName($this->getMotd());

			$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_info(
				$this->getName(),
				(VersionInfo::IS_DEVELOPMENT_BUILD ? TextFormat::YELLOW : "") . $this->getPocketMineVersion() . TextFormat::RESET
			)));
			$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_license($this->getName())));

			TimingsHandler::setEnabled($this->configGroup->getPropertyBool(Yml::SETTINGS_ENABLE_PROFILING, false));
			$this->profilingTickRate = $this->configGroup->getPropertyInt(Yml::SETTINGS_PROFILE_REPORT_TRIGGER, self::TARGET_TICKS_PER_SECOND);

			DefaultPermissions::registerCorePermissions();

			$this->commandMap = new SimpleCommandMap($this);

			$this->craftingManager = CraftingManagerFromDataHelper::make(Path::join(\pocketmine\BEDROCK_DATA_PATH, "recipes"));

			$this->resourceManager = new ResourcePackManager(Path::join($this->dataPath, "resource_packs"), $this->logger);

			$pluginGraylist = null;
			$graylistFile = Path::join($this->dataPath, "plugin_list.yml");
			if(!file_exists($graylistFile)){
				copy(Path::join(\pocketmine\RESOURCE_PATH, 'plugin_list.yml'), $graylistFile);
			}
			try{
				$pluginGraylist = PluginGraylist::fromArray(yaml_parse(Filesystem::fileGetContents($graylistFile)));
			}catch(\InvalidArgumentException $e){
				$this->logger->emergency("Failed to load $graylistFile: " . $e->getMessage());
				$this->forceShutdownExit();
				return;
			}
			$this->pluginManager = new PluginManager($this, $this->configGroup->getPropertyBool(Yml::PLUGINS_LEGACY_DATA_DIR, true) ? null : Path::join($this->dataPath, "plugin_data"), $pluginGraylist);
			$this->pluginManager->registerInterface(new PharPluginLoader($this->autoloader));
			$this->pluginManager->registerInterface(new ScriptPluginLoader());

			$providerManager = new WorldProviderManager();
			if(
				($format = $providerManager->getProviderByName($formatName = $this->configGroup->getPropertyString(Yml::LEVEL_SETTINGS_DEFAULT_FORMAT, ""))) !== null &&
				$format instanceof WritableWorldProviderManagerEntry
			){
				$providerManager->setDefault($format);
			}elseif($formatName !== ""){
				$this->logger->warning($this->language->translate(KnownTranslationFactory::pocketmine_level_badDefaultFormat($formatName)));
			}

			$this->worldManager = new WorldManager($this, Path::join($this->dataPath, "worlds"), $providerManager);
			$this->worldManager->setAutoSave($this->configGroup->getConfigBool(ServerProperties::AUTO_SAVE, $this->worldManager->getAutoSave()));
			$this->worldManager->setAutoSaveInterval($this->configGroup->getPropertyInt(Yml::TICKS_PER_AUTOSAVE, $this->worldManager->getAutoSaveInterval()));

			$this->updater = new UpdateChecker($this, $this->configGroup->getPropertyString(Yml::AUTO_UPDATER_HOST, "update.pmmp.io"));

			$this->queryInfo = new QueryInfo($this);

			$this->playerDataProvider = new DatFilePlayerDataProvider(Path::join($this->dataPath, "players"));

			register_shutdown_function($this->crashDump(...));

			$loadErrorCount = 0;
			$this->pluginManager->loadPlugins($this->pluginPath, $loadErrorCount);
			if($loadErrorCount > 0){
				$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_plugin_someLoadErrors()));
				$this->forceShutdownExit();
				return;
			}
			if(!$this->enablePlugins(PluginEnableOrder::STARTUP)){
				$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_plugin_someEnableErrors()));
				$this->forceShutdownExit();
				return;
			}

			if(!$this->startupPrepareWorlds()){
				$this->forceShutdownExit();
				return;
			}

			if(!$this->enablePlugins(PluginEnableOrder::POSTWORLD)){
				$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_plugin_someEnableErrors()));
				$this->forceShutdownExit();
				return;
			}

			if(!$this->startupPrepareNetworkInterfaces()){
				$this->forceShutdownExit();
				return;
			}

			if($this->configGroup->getPropertyBool(Yml::ANONYMOUS_STATISTICS_ENABLED, true)){
				$this->sendUsageTicker = self::TICKS_PER_STATS_REPORT;
				$this->sendUsage(SendUsageTask::TYPE_OPEN);
			}

			$this->configGroup->save();

			$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_defaultGameMode($this->getGamemode()->getTranslatableName())));
			$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_donate(TextFormat::AQUA . "https://patreon.com/pocketminemp" . TextFormat::RESET)));
			$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_startFinished(strval(round(microtime(true) - $this->startTime, 3)))));

			$forwarder = new BroadcastLoggerForwarder($this, $this->logger, $this->language);
			$this->subscribeToBroadcastChannel(self::BROADCAST_CHANNEL_ADMINISTRATIVE, $forwarder);
			$this->subscribeToBroadcastChannel(self::BROADCAST_CHANNEL_USERS, $forwarder);

			//TODO: move console parts to a separate component
			if($this->configGroup->getPropertyBool(Yml::CONSOLE_ENABLE_INPUT, true)){
				$this->console = new ConsoleReaderChildProcessDaemon($this->logger);
			}

			$this->tickProcessor();
			$this->forceShutdown();
		}catch(\Throwable $e){
			$this->exceptionHandler($e);
		}
	}

	private function startupPrepareWorlds() : bool{
		$getGenerator = function(string $generatorName, string $generatorOptions, string $worldName) : ?string{
			$generatorEntry = GeneratorManager::getInstance()->getGenerator($generatorName);
			if($generatorEntry === null){
				$this->logger->error($this->language->translate(KnownTranslationFactory::pocketmine_level_generationError(
					$worldName,
					KnownTranslationFactory::pocketmine_level_unknownGenerator($generatorName)
				)));
				return null;
			}
			try{
				$generatorEntry->validateGeneratorOptions($generatorOptions);
			}catch(InvalidGeneratorOptionsException $e){
				$this->logger->error($this->language->translate(KnownTranslationFactory::pocketmine_level_generationError(
					$worldName,
					KnownTranslationFactory::pocketmine_level_invalidGeneratorOptions($generatorOptions, $generatorName, $e->getMessage())
				)));
				return null;
			}
			return $generatorEntry->getGeneratorClass();
		};

		$anyWorldFailedToLoad = false;

		foreach(Utils::promoteKeys((array) $this->configGroup->getProperty(Yml::WORLDS, [])) as $name => $options){
			if(!is_string($name)){
				//TODO: this probably should be an error
				continue;
			}
			if($options === null){
				$options = [];
			}elseif(!is_array($options)){
				//TODO: this probably should be an error
				continue;
			}
			if(!$this->worldManager->loadWorld($name, true)){
				if($this->worldManager->isWorldGenerated($name)){
					//allow checking if other worlds are loadable, so the user gets all the errors in one go
					$anyWorldFailedToLoad = true;
					continue;
				}
				$creationOptions = WorldCreationOptions::create();
				//TODO: error checking

				$generatorName = $options["generator"] ?? "default";
				$generatorOptions = isset($options["preset"]) && is_string($options["preset"]) ? $options["preset"] : "";

				$generatorClass = $getGenerator($generatorName, $generatorOptions, $name);
				if($generatorClass === null){
					$anyWorldFailedToLoad = true;
					continue;
				}
				$creationOptions->setGeneratorClass($generatorClass);
				$creationOptions->setGeneratorOptions($generatorOptions);

				$creationOptions->setDifficulty($this->getDifficulty());
				if(isset($options["difficulty"]) && is_string($options["difficulty"])){
					$creationOptions->setDifficulty(World::getDifficultyFromString($options["difficulty"]));
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
			$default = $this->configGroup->getConfigString(ServerProperties::DEFAULT_WORLD_NAME, "world");
			if(trim($default) == ""){
				$this->logger->warning("level-name cannot be null, using default");
				$default = "world";
				$this->configGroup->setConfigString(ServerProperties::DEFAULT_WORLD_NAME, "world");
			}
			if(!$this->worldManager->loadWorld($default, true)){
				if($this->worldManager->isWorldGenerated($default)){
					$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_level_defaultError()));

					return false;
				}
				$generatorName = $this->configGroup->getConfigString(ServerProperties::DEFAULT_WORLD_GENERATOR);
				$generatorOptions = $this->configGroup->getConfigString(ServerProperties::DEFAULT_WORLD_GENERATOR_SETTINGS);
				$generatorClass = $getGenerator($generatorName, $generatorOptions, $default);

				if($generatorClass === null){
					$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_level_defaultError()));
					return false;
				}
				$creationOptions = WorldCreationOptions::create()
					->setGeneratorClass($generatorClass)
					->setGeneratorOptions($generatorOptions);
				$convertedSeed = Generator::convertSeed($this->configGroup->getConfigString(ServerProperties::DEFAULT_WORLD_SEED));
				if($convertedSeed !== null){
					$creationOptions->setSeed($convertedSeed);
				}
				$creationOptions->setDifficulty($this->getDifficulty());
				$this->worldManager->generateWorld($default, $creationOptions);
			}

			$world = $this->worldManager->getWorldByName($default);
			if($world === null){
				throw new AssumptionFailedError("We just loaded/generated the default world, so it must exist");
			}
			$this->worldManager->setDefaultWorld($world);
		}

		return !$anyWorldFailedToLoad;
	}

	private function startupPrepareConnectableNetworkInterfaces(
		string $ip,
		int $port,
		bool $ipV6,
		bool $useQuery,
		PacketBroadcaster $packetBroadcaster,
		EntityEventBroadcaster $entityEventBroadcaster,
		TypeConverter $typeConverter
	) : bool{
		$prettyIp = $ipV6 ? "[$ip]" : $ip;
		try{
			$rakLibRegistered = $this->network->registerInterface(new RakLibInterface($this, $ip, $port, $ipV6, $packetBroadcaster, $entityEventBroadcaster, $typeConverter));
		}catch(NetworkInterfaceStartException $e){
			$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_server_networkStartFailed(
				$ip,
				(string) $port,
				$e->getMessage()
			)));
			return false;
		}
		if($rakLibRegistered){
			$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_networkStart($prettyIp, (string) $port)));
		}
		if($useQuery){
			if(!$rakLibRegistered){
				//RakLib would normally handle the transport for Query packets
				//if it's not registered we need to make sure Query still works
				$this->network->registerInterface(new DedicatedQueryNetworkInterface($ip, $port, $ipV6, new \PrefixedLogger($this->logger, "Dedicated Query Interface")));
			}
			$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_server_query_running($prettyIp, (string) $port)));
		}
		return true;
	}

	private function startupPrepareNetworkInterfaces() : bool{
		$useQuery = $this->configGroup->getConfigBool(ServerProperties::ENABLE_QUERY, true);

		$typeConverter = TypeConverter::getInstance();
		$packetBroadcaster = new StandardPacketBroadcaster($this);
		$entityEventBroadcaster = new StandardEntityEventBroadcaster($packetBroadcaster, $typeConverter);

		if(
			!$this->startupPrepareConnectableNetworkInterfaces($this->getIp(), $this->getPort(), false, $useQuery, $packetBroadcaster, $entityEventBroadcaster, $typeConverter) ||
			(
				$this->configGroup->getConfigBool(ServerProperties::ENABLE_IPV6, true) &&
				!$this->startupPrepareConnectableNetworkInterfaces($this->getIpV6(), $this->getPortV6(), true, $useQuery, $packetBroadcaster, $entityEventBroadcaster, $typeConverter)
			)
		){
			return false;
		}

		if($useQuery){
			$this->network->registerRawPacketHandler(new QueryHandler($this));
		}

		foreach($this->getIPBans()->getEntries() as $entry){
			$this->network->blockAddress($entry->getName(), -1);
		}

		if($this->configGroup->getPropertyBool(Yml::NETWORK_UPNP_FORWARDING, false)){
			$this->network->registerInterface(new UPnPNetworkInterface($this->logger, Internet::getInternalIP(), $this->getPort()));
		}

		return true;
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
			if(count($this->broadcastSubscribers[$channelId]) === 1){
				unset($this->broadcastSubscribers[$channelId]);
			}else{
				unset($this->broadcastSubscribers[$channelId][spl_object_id($subscriber)]);
			}
		}
	}

	/**
	 * Unsubscribes from all broadcast channels.
	 */
	public function unsubscribeFromAllBroadcastChannels(CommandSender $subscriber) : void{
		foreach(Utils::stringifyKeys($this->broadcastSubscribers) as $channelId => $recipients){
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
	 * @param CommandSender[]|null $recipients
	 */
	public function broadcastMessage(Translatable|string $message, ?array $recipients = null) : int{
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
	 * @param int           $fadeIn     Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int           $stay       Duration in ticks to stay on screen for
	 * @param int           $fadeOut    Duration in ticks for fade-out.
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
	 * @internal
	 * Promises to compress the given batch buffer using the selected compressor, optionally on a separate thread.
	 *
	 * If the buffer is smaller than the batch-threshold (usually 256), the buffer will be compressed at level 0 if supported
	 * by the compressor. This means that the payload will be wrapped with the appropriate header and footer, but not
	 * actually compressed.
	 *
	 * If the buffer is larger than the async-compression-threshold (usually 10,000), the buffer may be compressed in
	 * a separate thread (if available).
	 *
	 * @param bool|null $sync Compression on the main thread (true) or workers (false). Default is automatic (null).
	 */
	public function prepareBatch(string $buffer, Compressor $compressor, ?bool $sync = null, ?TimingsHandler $timings = null) : CompressBatchPromise|string{
		$timings ??= Timings::$playerNetworkSendCompress;
		try{
			$timings->startTiming();

			$threshold = $compressor->getCompressionThreshold();
			if($threshold === null || strlen($buffer) < $compressor->getCompressionThreshold()){
				$compressionType = CompressionAlgorithm::NONE;
				$compressed = $buffer;

			}else{
				$sync ??= !$this->networkCompressionAsync;

				if(!$sync && strlen($buffer) >= $this->networkCompressionAsyncThreshold){
					$promise = new CompressBatchPromise();
					$task = new CompressBatchTask($buffer, $promise, $compressor);
					$this->asyncPool->submitTask($task);
					return $promise;
				}

				$compressionType = $compressor->getNetworkId();
				$compressed = $compressor->compress($buffer);
			}

			return chr($compressionType) . $compressed;
		}finally{
			$timings->stopTiming();
		}
	}

	public function enablePlugins(PluginEnableOrder $type) : bool{
		$allSuccess = true;
		foreach($this->pluginManager->getPlugins() as $plugin){
			if(!$plugin->isEnabled() && $plugin->getDescription()->getOrder() === $type){
				if(!$this->pluginManager->enablePlugin($plugin)){
					$allSuccess = false;
				}
			}
		}

		if($type === PluginEnableOrder::POSTWORLD){
			$this->commandMap->registerServerAliases();
		}

		return $allSuccess;
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

		return $this->commandMap->dispatch($sender, $commandLine);
	}

	/**
	 * Shuts the server down correctly
	 */
	public function shutdown() : void{
		if($this->isRunning){
			$this->isRunning = false;
			$this->signalHandler->unregister();
		}
	}

	private function forceShutdownExit() : void{
		$this->forceShutdown();
		Process::kill(Process::pid());
	}

	public function forceShutdown() : void{
		if($this->hasStopped){
			return;
		}

		if($this->doTitleTick){
			echo "\x1b]0;\x07";
		}

		if($this->isRunning){
			$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_server_forcingShutdown()));
		}
		try{
			if(!$this->isRunning()){
				$this->sendUsage(SendUsageTask::TYPE_CLOSE);
			}

			$this->hasStopped = true;

			$this->shutdown();

			if(isset($this->pluginManager)){
				$this->logger->debug("Disabling all plugins");
				$this->pluginManager->disablePlugins();
			}

			if(isset($this->network)){
				$this->network->getSessionManager()->close($this->configGroup->getPropertyString(Yml::SETTINGS_SHUTDOWN_MESSAGE, "Server closed"));
			}

			if(isset($this->worldManager)){
				$this->logger->debug("Unloading all worlds");
				foreach($this->worldManager->getWorlds() as $world){
					$this->worldManager->unloadWorld($world, true);
				}
			}

			$this->logger->debug("Removing event handlers");
			HandlerListManager::global()->unregisterAll();

			if(isset($this->asyncPool)){
				$this->logger->debug("Shutting down async task worker pool");
				$this->asyncPool->shutdown();
			}

			if(isset($this->configGroup)){
				$this->logger->debug("Saving properties");
				$this->configGroup->save();
			}

			if($this->console !== null){
				$this->logger->debug("Closing console");
				$this->console->quit();
			}

			if(isset($this->network)){
				$this->logger->debug("Stopping network interfaces");
				foreach($this->network->getInterfaces() as $interface){
					$this->logger->debug("Stopping network interface " . get_class($interface));
					$this->network->unregisterInterface($interface);
				}
			}
		}catch(\Throwable $e){
			$this->logger->logException($e);
			$this->logger->emergency("Crashed while crashing, killing process");
			@Process::kill(Process::pid());
		}

	}

	public function getQueryInformation() : QueryInfo{
		return $this->queryInfo;
	}

	/**
	 * @param mixed[][]|null $trace
	 * @phpstan-param list<array<string, mixed>>|null $trace
	 */
	public function exceptionHandler(\Throwable $e, ?array $trace = null) : void{
		while(@ob_end_flush()){}
		global $lastError;

		if($trace === null){
			$trace = $e->getTrace();
		}

		//If this is a thread crash, this logs where the exception came from on the main thread, as opposed to the
		//crashed thread. This is intentional, and might be useful for debugging
		//Assume that the thread already logged the original exception with the correct stack trace
		$this->logger->logException($e, $trace);

		if($e instanceof ThreadCrashException){
			$info = $e->getCrashInfo();
			$type = $info->getType();
			$errstr = $info->getMessage();
			$errfile = $info->getFile();
			$errline = $info->getLine();
			$printableTrace = $info->getTrace();
			$thread = $info->getThreadName();
		}else{
			$type = get_class($e);
			$errstr = $e->getMessage();
			$errfile = $e->getFile();
			$errline = $e->getLine();
			$printableTrace = Utils::printableTraceWithMetadata($trace);
			$thread = "Main";
		}

		$errstr = preg_replace('/\s+/', ' ', trim($errstr));

		$lastError = [
			"type" => $type,
			"message" => $errstr,
			"fullFile" => $errfile,
			"file" => Filesystem::cleanPath($errfile),
			"line" => $errline,
			"trace" => $printableTrace,
			"thread" => $thread
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}

	private function writeCrashDumpFile(CrashDump $dump) : string{
		$crashFolder = Path::join($this->dataPath, "crashdumps");
		if(!is_dir($crashFolder)){
			mkdir($crashFolder);
		}
		$crashDumpPath = Path::join($crashFolder, date("D_M_j-H.i.s-T_Y", (int) $dump->getData()->time) . ".log");

		$fp = @fopen($crashDumpPath, "wb");
		if(!is_resource($fp)){
			throw new \RuntimeException("Unable to open new file to generate crashdump");
		}
		$writer = new CrashDumpRenderer($fp, $dump->getData());
		$writer->renderHumanReadable();
		$dump->encodeData($writer);

		fclose($fp);
		return $crashDumpPath;
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
			$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_crash_create()));
			$dump = new CrashDump($this, $this->pluginManager ?? null);

			$crashDumpPath = $this->writeCrashDumpFile($dump);

			$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_crash_submit($crashDumpPath)));

			if($this->configGroup->getPropertyBool(Yml::AUTO_REPORT_ENABLED, true)){
				$report = true;

				$stamp = Path::join($this->dataPath, "crashdumps", ".last_crash");
				$crashInterval = 120; //2 minutes
				if(($lastReportTime = @filemtime($stamp)) !== false && $lastReportTime + $crashInterval >= time()){
					$report = false;
					$this->logger->debug("Not sending crashdump due to last crash less than $crashInterval seconds ago");
				}
				@touch($stamp); //update file timestamp

				if($dump->getData()->error["type"] === \ParseError::class){
					$report = false;
				}

				if(strrpos(VersionInfo::GIT_HASH(), "-dirty") !== false || VersionInfo::GIT_HASH() === str_repeat("00", 20)){
					$this->logger->debug("Not sending crashdump due to locally modified");
					$report = false; //Don't send crashdumps for locally modified builds
				}

				if($report){
					$url = ($this->configGroup->getPropertyBool(Yml::AUTO_REPORT_USE_HTTPS, true) ? "https" : "http") . "://" . $this->configGroup->getPropertyString(Yml::AUTO_REPORT_HOST, "crash.pmmp.io") . "/submit/api";
					$postUrlError = "Unknown error";
					$reply = Internet::postURL($url, [
						"report" => "yes",
						"name" => $this->getName() . " " . $this->getPocketMineVersion(),
						"email" => "crash@pocketmine.net",
						"reportPaste" => base64_encode($dump->getEncodedData())
					], 10, [], $postUrlError);

					if($reply !== null && is_object($data = json_decode($reply->getBody()))){
						if(isset($data->crashId) && is_int($data->crashId) && isset($data->crashUrl) && is_string($data->crashUrl)){
							$reportId = $data->crashId;
							$reportUrl = $data->crashUrl;
							$this->logger->emergency($this->language->translate(KnownTranslationFactory::pocketmine_crash_archive($reportUrl, (string) $reportId)));
						}elseif(isset($data->error) && is_string($data->error)){
							$this->logger->emergency("Automatic crash report submission failed: $data->error");
						}else{
							$this->logger->emergency("Invalid JSON response received from crash archive: " . $reply->getBody());
						}
					}else{
						$this->logger->emergency("Failed to communicate with crash archive: $postUrlError");
					}
				}
			}
		}catch(\Throwable $e){
			$this->logger->logException($e);
			try{
				$this->logger->critical($this->language->translate(KnownTranslationFactory::pocketmine_crash_error($e->getMessage())));
			}catch(\Throwable $e){}
		}

		$this->forceShutdown();
		$this->isRunning = false;

		//Force minimum uptime to be >= 120 seconds, to reduce the impact of spammy crash loops
		$uptime = time() - ((int) $this->startTime);
		$minUptime = 120;
		$spacing = $minUptime - $uptime;
		if($spacing > 0){
			echo "--- Uptime {$uptime}s - waiting {$spacing}s to throttle automatic restart (you can kill the process safely now) ---" . PHP_EOL;
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

	public function addOnlinePlayer(Player $player) : bool{
		$ev = new PlayerLoginEvent($player, "Plugin reason");
		$ev->call();
		if($ev->isCancelled() || !$player->isConnected()){
			$player->disconnect($ev->getKickMessage());

			return false;
		}

		$session = $player->getNetworkSession();
		$position = $player->getPosition();
		$this->logger->info($this->language->translate(KnownTranslationFactory::pocketmine_player_logIn(
			TextFormat::AQUA . $player->getName() . TextFormat::RESET,
			$session->getIp(),
			(string) $session->getPort(),
			(string) $player->getId(),
			$position->getWorld()->getDisplayName(),
			(string) round($position->x, 4),
			(string) round($position->y, 4),
			(string) round($position->z, 4)
		)));

		foreach($this->playerList as $p){
			$p->getNetworkSession()->onPlayerAdded($player);
		}
		$rawUUID = $player->getUniqueId()->getBytes();
		$this->playerList[$rawUUID] = $player;

		if($this->sendUsageTicker > 0){
			$this->uniquePlayers[$rawUUID] = $rawUUID;
		}

		return true;
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
		if($this->configGroup->getPropertyBool(Yml::ANONYMOUS_STATISTICS_ENABLED, true)){
			$this->asyncPool->submitTask(new SendUsageTask($this, $type, $this->uniquePlayers));
		}
		$this->uniquePlayers = [];
	}

	public function getLanguage() : Language{
		return $this->language;
	}

	public function isLanguageForced() : bool{
		return $this->forceLanguage;
	}

	public function getNetwork() : Network{
		return $this->network;
	}

	public function getMemoryManager() : MemoryManager{
		return $this->memoryManager;
	}

	private function titleTick() : void{
		Timings::$titleTick->startTiming();

		$u = Process::getAdvancedMemoryUsage();
		$usage = sprintf("%g/%g/%g MB @ %d threads", round(($u[0] / 1024) / 1024, 2), round(($u[1] / 1024) / 1024, 2), round(($u[2] / 1024) / 1024, 2), Process::getThreadCount());

		$online = count($this->playerList);
		$connecting = $this->network->getConnectionCount() - $online;
		$bandwidthStats = $this->network->getBandwidthTracker();

		echo "\x1b]0;" . $this->getName() . " " .
			$this->getPocketMineVersion() .
			" | Online $online/" . $this->maxPlayers .
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

		if(($this->tickCounter % self::TARGET_TICKS_PER_SECOND) === 0){
			if($this->doTitleTick){
				$this->titleTick();
			}
			$this->currentTPS = self::TARGET_TICKS_PER_SECOND;
			$this->currentUse = 0;

			$queryRegenerateEvent = new QueryRegenerateEvent(new QueryInfo($this));
			$queryRegenerateEvent->call();
			$this->queryInfo = $queryRegenerateEvent->getQueryInfo();

			$this->network->updateName();
			$this->network->getBandwidthTracker()->rotateAverageHistory();
		}

		if($this->sendUsageTicker > 0 && --$this->sendUsageTicker === 0){
			$this->sendUsageTicker = self::TICKS_PER_STATS_REPORT;
			$this->sendUsage(SendUsageTask::TYPE_STATUS);
		}

		if(($this->tickCounter % self::TICKS_PER_WORLD_CACHE_CLEAR) === 0){
			foreach($this->worldManager->getWorlds() as $world){
				$world->clearCache();
			}
		}

		if(($this->tickCounter % self::TICKS_PER_TPS_OVERLOAD_WARNING) === 0 && $this->getTicksPerSecondAverage() < self::TPS_OVERLOAD_WARNING_THRESHOLD){
			$this->logger->warning($this->language->translate(KnownTranslationFactory::pocketmine_server_tickOverload()));
		}

		$this->memoryManager->check();

		if($this->console !== null){
			Timings::$serverCommand->startTiming();
			while(($line = $this->console->readLine()) !== null){
				$this->consoleSender ??= new ConsoleCommandSender($this, $this->language);
				$this->dispatchCommand($this->consoleSender, $line);
			}
			Timings::$serverCommand->stopTiming();
		}

		Timings::$serverTick->stopTiming();

		$now = microtime(true);
		$totalTickTimeSeconds = $now - $tickTime + ($this->tickSleeper->getNotificationProcessingTime() / 1_000_000_000);
		$this->currentTPS = min(self::TARGET_TICKS_PER_SECOND, 1 / max(0.001, $totalTickTimeSeconds));
		$this->currentUse = min(1, $totalTickTimeSeconds / self::TARGET_SECONDS_PER_TICK);

		TimingsHandler::tick($this->currentTPS <= $this->profilingTickRate);

		$idx = $this->tickCounter % self::TARGET_TICKS_PER_SECOND;
		$this->tickAverage[$idx] = $this->currentTPS;
		$this->useAverage[$idx] = $this->currentUse;
		$this->tickSleeper->resetNotificationProcessingTime();

		if(($this->nextTick - $tickTime) < -1){
			$this->nextTick = $tickTime;
		}else{
			$this->nextTick += self::TARGET_SECONDS_PER_TICK;
		}
	}
}
