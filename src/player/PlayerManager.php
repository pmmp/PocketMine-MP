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

namespace pocketmine\player;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\Location;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use pocketmine\utils\Filesystem;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\Chunk;
use Ramsey\Uuid\UuidInterface;
use Webmozart\PathUtil\Path;
use function strtolower;

class PlayerManager{

	private BanList $banByName;

	private BanList $banByIP;

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public static array $uniquePlayers = [];

	private Config $operators;

	private Config $whitelist;

	/** @var Player[] */
	private array $playerList = [];

	public function __construct(protected Server $server){ }


	public function getNameBans() : BanList{
		return $this->banByName;
	}

	public function getIPBans() : BanList{
		return $this->banByIP;
	}

	/**
	 * @return string[]
	 */
	public static function getUniquePlayers() : array{
		return self::$uniquePlayers;
	}

	/**
	 * @return OfflinePlayer|Player
	 */
	public function getOfflinePlayer(string $name) : Player|OfflinePlayer{
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);

		if($result === null){
			$result = new OfflinePlayer($name, $this->getOfflinePlayerData($name));
		}

		return $result;
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
	 * @return Player[]
	 */
	public function getOnlinePlayers() : array{
		return $this->playerList;
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
					$this->server->getLogger()->debug("Failed to decompress raw player data for \"$name\"");
					$this->handleCorruptedPlayerData($name);
					return null;
				}

				try{
					return (new BigEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
				}catch(NbtDataException $e){ //corrupt data
					$this->server->getLogger()->debug("Failed to decode NBT data for \"$name\": " . $e->getMessage());
					$this->handleCorruptedPlayerData($name);
					return null;
				}
			}
			return null;
		});
	}

	private function getPlayerDataPath(string $username) : string{
		return Path::join($this->server->getDataPath(), 'players', strtolower($username) . '.dat');
	}

	private function handleCorruptedPlayerData(string $name) : void{
		$path = $this->getPlayerDataPath($name);
		rename($path, $path . '.bak');
		$this->server->getLogger()->error($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_data_playerCorrupted($name)));
	}

	/**
	 * Returns whether the server has stored any saved data for this player.
	 */
	public function hasOfflinePlayerData(string $name) : bool{
		return file_exists($this->getPlayerDataPath($name));
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
				$contents = zlib_encode($nbt->write(new TreeRoot($ev->getSaveData())), ZLIB_ENCODING_GZIP);
				if($contents === false){
					throw new AssumptionFailedError("zlib_encode() failed unexpectedly");
				}
				try{
					Filesystem::safeFilePutContents($this->getPlayerDataPath($name), $contents);
				}catch(\RuntimeException | \ErrorException $e){
					$this->server->getLogger()->critical($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_data_saveError($name, $e->getMessage())));
					$this->server->getLogger()->logException($e);
				}
			});
		}
	}

	public function shouldSavePlayerData() : bool{
		return Server::getInstance()->getConfigGroup()->getPropertyBool("player.save-player-data", true);
	}

	/**
	 * @phpstan-return Promise<Player>
	 */
	public function createPlayer(NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, ?CompoundTag $offlinePlayerData) : Promise{
		$ev = new PlayerCreationEvent($session);
		$ev->call();
		$class = $ev->getPlayerClass();

		if($offlinePlayerData !== null and ($world = $this->server->getWorldManager()->getWorldByName($offlinePlayerData->getString("Level", ""))) !== null){
			$playerPos = EntityDataHelper::parseLocation($offlinePlayerData, $world);
			$spawn = $playerPos->asVector3();
		}else{
			$world = $this->server->getWorldManager()->getDefaultWorld();
			if($world === null){
				throw new AssumptionFailedError("Default world should always be loaded");
			}
			$playerPos = null;
			$spawn = $world->getSpawnLocation();
		}
		$playerPromiseResolver = new PromiseResolver();
		$world->requestChunkPopulation($spawn->getFloorX() >> Chunk::COORD_BIT_SIZE, $spawn->getFloorZ() >> Chunk::COORD_BIT_SIZE, null)->onCompletion(
			function() use ($playerPromiseResolver, $class, $session, $playerInfo, $authenticated, $world, $playerPos, $spawn, $offlinePlayerData) : void{
				if(!$session->isConnected()){
					$playerPromiseResolver->reject();
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
				$playerPromiseResolver->resolve($player);
			},
			static function() use ($playerPromiseResolver, $session) : void{
				if($session->isConnected()){
					$session->disconnect("Spawn terrain generation failed");
				}
				$playerPromiseResolver->reject();
			}
		);
		return $playerPromiseResolver->getPromise();
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
	 * Returns the player online with a UUID equivalent to the specified UuidInterface object, or null if not found
	 */
	public function getPlayerByUUID(UuidInterface $uuid) : ?Player{
		return $this->getPlayerByRawUUID($uuid->getBytes());
	}

	/**
	 * Returns the player online with the specified raw UUID, or null if not found
	 */
	public function getPlayerByRawUUID(string $rawUUID) : ?Player{
		return $this->playerList[$rawUUID] ?? null;
	}

	public function addOnlinePlayer(Player $player) : bool{
		$ev = new PlayerLoginEvent($player, "Plugin reason");
		$ev->call();
		if($ev->isCancelled() or !$player->isConnected()){
			$player->disconnect($ev->getKickMessage());

			return false;
		}

		$session = $player->getNetworkSession();
		$position = $player->getPosition();
		$this->server->getLogger()->info($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_player_logIn(
			TextFormat::AQUA . $player->getName() . TextFormat::WHITE,
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

		if($this->server->getSendUsageTicker() > 0){
			self::$uniquePlayers[$rawUUID] = $rawUUID;
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
		return !$this->server->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
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
	 * @return Player[]
	 */
	public function getPlayerList() : array{
		return $this->playerList;
	}

}