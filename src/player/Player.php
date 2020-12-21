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

use pocketmine\block\Bed;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\UnknownBlock;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\crafting\CraftingGrid;
use pocketmine\entity\animation\Animation;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\animation\CriticalHitAnimation;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDisplayNameChangeEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\item\ConsumableItem;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\item\Releasable;
use pocketmine\lang\Language;
use pocketmine\lang\TranslationContainer;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PlayerMetadataFlags;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissibleDelegateTrait;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use pocketmine\uuid\UUID;
use pocketmine\world\ChunkListener;
use pocketmine\world\ChunkListenerNoOpTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\sound\EntityAttackNoDamageSound;
use pocketmine\world\sound\EntityAttackSound;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\World;
use function abs;
use function assert;
use function count;
use function explode;
use function floor;
use function get_class;
use function is_int;
use function max;
use function microtime;
use function min;
use function preg_match;
use function round;
use function spl_object_id;
use function sqrt;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use const M_PI;
use const M_SQRT3;
use const PHP_INT_MAX;

/**
 * Main class that handles networking, recovery, and packet sending to the server part
 */
class Player extends Human implements CommandSender, ChunkListener, IPlayer{
	use PermissibleDelegateTrait;

	private const MOVES_PER_TICK = 2;
	private const MOVE_BACKLOG_SIZE = 100 * self::MOVES_PER_TICK; //100 ticks backlog (5 seconds)

	/**
	 * Validates the given username.
	 */
	public static function isValidUserName(?string $name) : bool{
		if($name === null){
			return false;
		}

		$lname = strtolower($name);
		$len = strlen($name);
		return $lname !== "rcon" and $lname !== "console" and $len >= 1 and $len <= 16 and preg_match("/[^A-Za-z0-9_ ]/", $name) === 0;
	}

	/** @var NetworkSession|null */
	protected $networkSession;

	/** @var bool */
	public $spawned = false;

	/** @var string */
	protected $username;
	/** @var string */
	protected $displayName;
	/** @var string */
	protected $xuid = "";
	/** @var bool */
	protected $authenticated;
	/** @var PlayerInfo */
	protected $playerInfo;

	/** @var Inventory|null */
	protected $currentWindow = null;
	/** @var Inventory[] */
	protected $permanentWindows = [];
	/** @var PlayerCursorInventory */
	protected $cursorInventory;
	/** @var CraftingGrid */
	protected $craftingGrid;

	/** @var int */
	protected $messageCounter = 2;

	/** @var int */
	protected $firstPlayed;
	/** @var int */
	protected $lastPlayed;
	/** @var GameMode */
	protected $gamemode;

	/**
	 * @var UsedChunkStatus[] chunkHash => status
	 * @phpstan-var array<int, UsedChunkStatus>
	 */
	protected $usedChunks = [];
	/** @var bool[] chunkHash => dummy */
	protected $loadQueue = [];
	/** @var int */
	protected $nextChunkOrderRun = 5;

	/** @var int */
	protected $viewDistance = -1;
	/** @var int */
	protected $spawnThreshold;
	/** @var int */
	protected $spawnChunkLoadCount = 0;
	/** @var int */
	protected $chunksPerTick;
	/** @var ChunkSelector */
	protected $chunkSelector;
	/** @var TickingChunkLoader */
	protected $chunkLoader;

	/** @var bool[] map: raw UUID (string) => bool */
	protected $hiddenPlayers = [];

	/** @var float */
	protected $moveRateLimit = 10 * self::MOVES_PER_TICK;
	/** @var float|null */
	protected $lastMovementProcess = null;
	/** @var Vector3|null */
	protected $forceMoveSync = null;

	/** @var int */
	protected $inAirTicks = 0;
	/** @var float */
	protected $stepHeight = 0.6;

	/** @var Vector3|null */
	protected $sleeping = null;
	/** @var Position|null */
	private $spawnPosition = null;

	//TODO: Abilities
	/** @var bool */
	protected $autoJump = true;
	/** @var bool */
	protected $allowFlight = false;
	/** @var bool */
	protected $flying = false;

	/** @var int|null */
	protected $lineHeight = null;
	/** @var string */
	protected $locale = "en_US";

	/** @var int */
	protected $startAction = -1;
	/** @var int[] ID => ticks map */
	protected $usedItemsCooldown = [];

	/** @var int */
	protected $formIdCounter = 0;
	/** @var Form[] */
	protected $forms = [];

	/** @var \Logger */
	protected $logger;

	/** @var SurvivalBlockBreakHandler|null */
	protected $blockBreakHandler = null;

	public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, ?CompoundTag $namedtag){
		$username = TextFormat::clean($playerInfo->getUsername());
		$this->logger = new \PrefixedLogger($server->getLogger(), "Player: $username");

		$this->server = $server;
		$this->networkSession = $session;
		$this->playerInfo = $playerInfo;
		$this->authenticated = $authenticated;

		$this->username = $username;
		$this->displayName = $this->username;
		$this->locale = $this->playerInfo->getLocale();

		$this->uuid = $this->playerInfo->getUuid();
		$this->xuid = $this->playerInfo instanceof XboxLivePlayerInfo ? $this->playerInfo->getXuid() : "";

		$rootPermissions = [DefaultPermissions::ROOT_USER => true];
		if($this->server->isOp($this->username)){
			$rootPermissions[DefaultPermissions::ROOT_OPERATOR] = true;
		}
		$this->perm = new PermissibleBase($rootPermissions);
		$this->chunksPerTick = (int) $this->server->getConfigGroup()->getProperty("chunk-sending.per-tick", 4);
		$this->spawnThreshold = (int) (($this->server->getConfigGroup()->getProperty("chunk-sending.spawn-radius", 4) ** 2) * M_PI);
		$this->chunkSelector = new ChunkSelector();

		if($namedtag !== null and ($world = $this->server->getWorldManager()->getWorldByName($namedtag->getString("Level", ""))) !== null){
			$spawn = EntityDataHelper::parseLocation($namedtag, $world);
			$onGround = $namedtag->getByte("OnGround", 1) === 1;
		}else{
			$world = $this->server->getWorldManager()->getDefaultWorld();
			$spawn = Location::fromObject($world->getSafeSpawn(), $world);
			$onGround = true;
		}

		$this->chunkLoader = new TickingChunkLoader($spawn);

		//load the spawn chunk so we can see the terrain
		$world->registerChunkLoader($this->chunkLoader, $spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4, true);
		$world->registerChunkListener($this, $spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4);
		$this->usedChunks[World::chunkHash($spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4)] = UsedChunkStatus::NEEDED();

		parent::__construct($spawn, $this->playerInfo->getSkin(), $namedtag);
		$this->onGround = $onGround; //TODO: this hack is needed for new players in-air ticks - they don't get detected as on-ground until they move

		$ev = new PlayerLoginEvent($this, "Plugin reason");
		$ev->call();
		if($ev->isCancelled() or !$this->isConnected()){
			$this->disconnect($ev->getKickMessage());

			return;
		}

		$this->perm->recalculatePermissions();

		$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn", [
			TextFormat::AQUA . $this->username . TextFormat::WHITE,
			$this->networkSession->getIp(),
			$this->networkSession->getPort(),
			$this->id,
			$this->getWorld()->getDisplayName(),
			round($this->location->x, 4),
			round($this->location->y, 4),
			round($this->location->z, 4)
		]));

		$this->server->addOnlinePlayer($this);
	}

	protected function initHumanData(CompoundTag $nbt) : void{
		$this->setNameTag($this->username);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->addDefaultWindows();

		$this->firstPlayed = $nbt->getLong("firstPlayed", $now = (int) (microtime(true) * 1000));
		$this->lastPlayed = $nbt->getLong("lastPlayed", $now);

		if(!$this->server->getForceGamemode() and ($gameModeTag = $nbt->getTag("playerGameType")) instanceof IntTag){
			$this->internalSetGameMode(GameMode::fromMagicNumber($gameModeTag->getValue() & 0x03)); //TODO: bad hack here to avoid crashes on corrupted data
		}else{
			$this->internalSetGameMode($this->server->getGamemode());
		}

		$this->keepMovement = true;

		$this->setNameTagVisible();
		$this->setNameTagAlwaysVisible();
		$this->setCanClimb();

		if(($world = $this->server->getWorldManager()->getWorldByName($nbt->getString("SpawnLevel", ""))) instanceof World){
			$this->spawnPosition = new Position($nbt->getInt("SpawnX"), $nbt->getInt("SpawnY"), $nbt->getInt("SpawnZ"), $world);
		}
	}

	/**
	 * @return TranslationContainer|string
	 */
	public function getLeaveMessage(){
		if($this->spawned){
			return new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.left", [
				$this->getDisplayName()
			]);
		}

		return "";
	}

	public function isAuthenticated() : bool{
		return $this->authenticated;
	}

	/**
	 * If the player is logged into Xbox Live, returns their Xbox user ID (XUID) as a string. Returns an empty string if
	 * the player is not logged into Xbox Live.
	 */
	public function getXuid() : string{
		return $this->xuid;
	}

	/**
	 * Returns the player's UUID. This should be preferred over their Xbox user ID (XUID) because UUID is a standard
	 * format which will never change, and all players will have one regardless of whether they are logged into Xbox
	 * Live.
	 *
	 * The UUID is comprised of:
	 * - when logged into XBL: a hash of their XUID (and as such will not change for the lifetime of the XBL account)
	 * - when NOT logged into XBL: a hash of their name + clientID + secret device ID.
	 *
	 * WARNING: UUIDs of players **not logged into Xbox Live** CAN BE FAKED and SHOULD NOT be trusted!
	 *
	 * (In the olden days this method used to return a fake UUID computed by the server, which was used by plugins such
	 * as SimpleAuth for authentication. This is NOT SAFE anymore as this UUID is now what was given by the client, NOT
	 * a server-computed UUID.)
	 */
	public function getUniqueId() : UUID{
		return parent::getUniqueId();
	}

	/**
	 * TODO: not sure this should be nullable
	 */
	public function getFirstPlayed() : ?int{
		return $this->firstPlayed;
	}

	/**
	 * TODO: not sure this should be nullable
	 */
	public function getLastPlayed() : ?int{
		return $this->lastPlayed;
	}

	public function hasPlayedBefore() : bool{
		return $this->lastPlayed - $this->firstPlayed > 1; // microtime(true) - microtime(true) may have less than one millisecond difference
	}

	public function setAllowFlight(bool $value) : void{
		$this->allowFlight = $value;
		$this->networkSession->syncAdventureSettings($this);
	}

	public function getAllowFlight() : bool{
		return $this->allowFlight;
	}

	public function setFlying(bool $value) : void{
		if($this->flying !== $value){
			$this->flying = $value;
			$this->resetFallDistance();
			$this->networkSession->syncAdventureSettings($this);
		}
	}

	public function isFlying() : bool{
		return $this->flying;
	}

	public function setAutoJump(bool $value) : void{
		$this->autoJump = $value;
		$this->networkSession->syncAdventureSettings($this);
	}

	public function hasAutoJump() : bool{
		return $this->autoJump;
	}

	public function spawnTo(Player $player) : void{
		if($this->isAlive() and $player->isAlive() and $player->getWorld() === $this->getWorld() and $player->canSee($this) and !$this->isSpectator()){
			parent::spawnTo($player);
		}
	}

	public function getServer() : Server{
		return $this->server;
	}

	public function getScreenLineHeight() : int{
		return $this->lineHeight ?? 7;
	}

	public function setScreenLineHeight(?int $height) : void{
		if($height !== null and $height < 1){
			throw new \InvalidArgumentException("Line height must be at least 1");
		}
		$this->lineHeight = $height;
	}

	public function canSee(Player $player) : bool{
		return !isset($this->hiddenPlayers[$player->getUniqueId()->toBinary()]);
	}

	public function hidePlayer(Player $player) : void{
		if($player === $this){
			return;
		}
		$this->hiddenPlayers[$player->getUniqueId()->toBinary()] = true;
		$player->despawnFrom($this);
	}

	public function showPlayer(Player $player) : void{
		if($player === $this){
			return;
		}
		unset($this->hiddenPlayers[$player->getUniqueId()->toBinary()]);
		if($player->isOnline()){
			$player->spawnTo($this);
		}
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function canBeCollidedWith() : bool{
		return !$this->isSpectator() and parent::canBeCollidedWith();
	}

	public function resetFallDistance() : void{
		parent::resetFallDistance();
		$this->inAirTicks = 0;
	}

	public function getViewDistance() : int{
		return $this->viewDistance;
	}

	public function setViewDistance(int $distance) : void{
		$this->viewDistance = $this->server->getAllowedViewDistance($distance);

		$this->spawnThreshold = (int) (min($this->viewDistance, $this->server->getConfigGroup()->getProperty("chunk-sending.spawn-radius", 4)) ** 2 * M_PI);

		$this->nextChunkOrderRun = 0;

		$this->networkSession->syncViewAreaRadius($this->viewDistance);

		$this->logger->debug("Setting view distance to " . $this->viewDistance . " (requested " . $distance . ")");
	}

	public function isOnline() : bool{
		return $this->isConnected();
	}

	public function isConnected() : bool{
		return $this->networkSession !== null and $this->networkSession->isConnected();
	}

	public function getNetworkSession() : NetworkSession{
		return $this->networkSession;
	}

	/**
	 * Gets the username
	 */
	public function getName() : string{
		return $this->username;
	}

	/**
	 * Returns the "friendly" display name of this player to use in the chat.
	 */
	public function getDisplayName() : string{
		return $this->displayName;
	}

	public function setDisplayName(string $name) : void{
		$ev = new PlayerDisplayNameChangeEvent($this, $this->displayName, $name);
		$ev->call();

		$this->displayName = $ev->getNewName();
	}

	/**
	 * Returns the player's locale, e.g. en_US.
	 */
	public function getLocale() : string{
		return $this->locale;
	}

	public function getLanguage() : Language{
		return $this->server->getLanguage();
	}

	/**
	 * Called when a player changes their skin.
	 * Plugin developers should not use this, use setSkin() and sendSkin() instead.
	 */
	public function changeSkin(Skin $skin, string $newSkinName, string $oldSkinName) : bool{
		$ev = new PlayerChangeSkinEvent($this, $this->getSkin(), $skin);
		$ev->call();

		if($ev->isCancelled()){
			$this->sendSkin([$this]);
			return true;
		}

		$this->setSkin($ev->getNewSkin());
		$this->sendSkin($this->server->getOnlinePlayers());
		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * If null is given, will additionally send the skin to the player itself as well as its viewers.
	 */
	public function sendSkin(?array $targets = null) : void{
		parent::sendSkin($targets ?? $this->server->getOnlinePlayers());
	}

	/**
	 * Returns whether the player is currently using an item (right-click and hold).
	 */
	public function isUsingItem() : bool{
		return $this->startAction > -1;
	}

	public function setUsingItem(bool $value) : void{
		$this->startAction = $value ? $this->server->getTick() : -1;
	}

	/**
	 * Returns how long the player has been using their currently-held item for. Used for determining arrow shoot force
	 * for bows.
	 */
	public function getItemUseDuration() : int{
		return $this->startAction === -1 ? -1 : ($this->server->getTick() - $this->startAction);
	}

	/**
	 * Returns the server tick on which the player's cooldown period expires for the given item.
	 */
	public function getItemCooldownExpiry(Item $item) : int{
		$this->checkItemCooldowns();
		return $this->usedItemsCooldown[$item->getId()] ?? 0;
	}

	/**
	 * Returns whether the player has a cooldown period left before it can use the given item again.
	 */
	public function hasItemCooldown(Item $item) : bool{
		$this->checkItemCooldowns();
		return isset($this->usedItemsCooldown[$item->getId()]);
	}

	/**
	 * Resets the player's cooldown time for the given item back to the maximum.
	 */
	public function resetItemCooldown(Item $item, ?int $ticks = null) : void{
		$ticks = $ticks ?? $item->getCooldownTicks();
		if($ticks > 0){
			$this->usedItemsCooldown[$item->getId()] = $this->server->getTick() + $ticks;
		}
	}

	protected function checkItemCooldowns() : void{
		$serverTick = $this->server->getTick();
		foreach($this->usedItemsCooldown as $itemId => $cooldownUntil){
			if($cooldownUntil <= $serverTick){
				unset($this->usedItemsCooldown[$itemId]);
			}
		}
	}

	protected function setPosition(Vector3 $pos) : bool{
		$oldWorld = $this->location->isValid() ? $this->location->getWorld() : null;
		if(parent::setPosition($pos)){
			$newWorld = $this->getWorld();
			if($oldWorld !== $newWorld){
				if($oldWorld !== null){
					foreach($this->usedChunks as $index => $status){
						World::getXZ($index, $X, $Z);
						$this->unloadChunk($X, $Z, $oldWorld);
					}
				}

				$this->usedChunks = [];
				$this->loadQueue = [];
				$this->networkSession->onEnterWorld();
			}

			return true;
		}

		return false;
	}

	protected function unloadChunk(int $x, int $z, ?World $world = null) : void{
		$world = $world ?? $this->getWorld();
		$index = World::chunkHash($x, $z);
		if(isset($this->usedChunks[$index])){
			$chunk = $world->getChunk($x, $z);
			if($chunk !== null){ //this might be a chunk that hasn't been generated yet
				foreach($chunk->getEntities() as $entity){
					if($entity !== $this){
						$entity->despawnFrom($this);
					}
				}
			}
			$this->networkSession->stopUsingChunk($x, $z);
			unset($this->usedChunks[$index]);
		}
		$world->unregisterChunkLoader($this->chunkLoader, $x, $z);
		$world->unregisterChunkListener($this, $x, $z);
		unset($this->loadQueue[$index]);
	}

	protected function spawnEntitiesOnChunk(int $chunkX, int $chunkZ) : void{
		foreach($this->getWorld()->getChunk($chunkX, $chunkZ)->getEntities() as $entity){
			if($entity !== $this and !$entity->isFlaggedForDespawn()){
				$entity->spawnTo($this);
			}
		}
	}

	/**
	 * Requests chunks from the world to be sent, up to a set limit every tick. This operates on the results of the most recent chunk
	 * order.
	 */
	protected function requestChunks() : void{
		if(!$this->isConnected()){
			return;
		}

		Timings::$playerChunkSendTimer->startTiming();

		$count = 0;
		foreach($this->loadQueue as $index => $distance){
			if($count >= $this->chunksPerTick){
				break;
			}

			$X = null;
			$Z = null;
			World::getXZ($index, $X, $Z);
			assert(is_int($X) and is_int($Z));

			++$count;

			$this->usedChunks[$index] = UsedChunkStatus::NEEDED();
			$this->getWorld()->registerChunkLoader($this->chunkLoader, $X, $Z, true);
			$this->getWorld()->registerChunkListener($this, $X, $Z);

			if(!$this->getWorld()->requestChunkPopulation($X, $Z)){
				continue;
			}

			unset($this->loadQueue[$index]);
			$this->usedChunks[$index] = UsedChunkStatus::REQUESTED();

			$this->networkSession->startUsingChunk($X, $Z, function(int $chunkX, int $chunkZ) use ($index) : void{
				$this->usedChunks[$index] = UsedChunkStatus::SENT();
				if($this->spawnChunkLoadCount === -1){
					$this->spawnEntitiesOnChunk($chunkX, $chunkZ);
				}elseif($this->spawnChunkLoadCount++ === $this->spawnThreshold){
					$this->spawnChunkLoadCount = -1;

					foreach($this->usedChunks as $chunkHash => $status){
						if($status->equals(UsedChunkStatus::SENT())){
							World::getXZ($chunkHash, $_x, $_z);
							$this->spawnEntitiesOnChunk($_x, $_z);
						}
					}

					$this->networkSession->notifyTerrainReady();
				}
			});
		}

		Timings::$playerChunkSendTimer->stopTiming();
	}

	private function recheckBroadcastPermissions() : void{
		foreach([Server::BROADCAST_CHANNEL_USERS, Server::BROADCAST_CHANNEL_ADMINISTRATIVE] as $channel){
			if($this->hasPermission($channel)){
				$this->server->subscribeToBroadcastChannel($channel, $this);
			}else{
				$this->server->unsubscribeFromBroadcastChannel($channel, $this);
			}
		}
	}

	/**
	 * Called by the network system when the pre-spawn sequence is completed (e.g. after sending spawn chunks).
	 * This fires join events and broadcasts join messages to other online players.
	 */
	public function doFirstSpawn() : void{
		if($this->spawned){
			return;
		}
		$this->spawned = true;
		$this->recheckBroadcastPermissions();
		$this->getPermissionRecalculationCallbacks()->add(function(array $changedPermissionsOldValues) : void{
			if(isset($changedPermissionsOldValues[Server::BROADCAST_CHANNEL_ADMINISTRATIVE]) || isset($changedPermissionsOldValues[Server::BROADCAST_CHANNEL_USERS])){
				$this->recheckBroadcastPermissions();
			}
		});

		$ev = new PlayerJoinEvent($this,
			new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.joined", [
				$this->getDisplayName()
			])
		);
		$ev->call();
		if(strlen(trim((string) $ev->getJoinMessage())) > 0){
			$this->server->broadcastMessage($ev->getJoinMessage());
		}

		$this->noDamageTicks = 60;

		$this->spawnToAll();

		if($this->server->getUpdater()->hasUpdate() and $this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE) and $this->server->getConfigGroup()->getProperty("auto-updater.on-update.warn-ops", true)){
			$this->server->getUpdater()->showPlayerUpdate($this);
		}

		if($this->getHealth() <= 0){
			$this->respawn();
		}
	}

	/**
	 * Calculates which new chunks this player needs to use, and which currently-used chunks it needs to stop using.
	 * This is based on factors including the player's current render radius and current position.
	 */
	protected function orderChunks() : void{
		if(!$this->isConnected() or $this->viewDistance === -1){
			return;
		}

		Timings::$playerChunkOrderTimer->startTiming();

		$newOrder = [];
		$unloadChunks = $this->usedChunks;

		foreach($this->chunkSelector->selectChunks(
			$this->server->getAllowedViewDistance($this->viewDistance),
			$this->location->getFloorX() >> 4,
			$this->location->getFloorZ() >> 4
		) as $hash){
			if(!isset($this->usedChunks[$hash]) or $this->usedChunks[$hash]->equals(UsedChunkStatus::NEEDED())){
				$newOrder[$hash] = true;
			}
			unset($unloadChunks[$hash]);
		}

		foreach($unloadChunks as $index => $status){
			World::getXZ($index, $X, $Z);
			$this->unloadChunk($X, $Z);
		}

		$this->loadQueue = $newOrder;
		if(count($this->loadQueue) > 0 or count($unloadChunks) > 0){
			$this->chunkLoader->setCurrentLocation($this->location);
			$this->networkSession->syncViewAreaCenterPoint($this->location, $this->viewDistance);
		}

		Timings::$playerChunkOrderTimer->stopTiming();
	}

	/**
	 * Returns whether the player is using the chunk with the given coordinates, irrespective of whether the chunk has
	 * been sent yet.
	 */
	public function isUsingChunk(int $chunkX, int $chunkZ) : bool{
		return isset($this->usedChunks[World::chunkHash($chunkX, $chunkZ)]);
	}

	/**
	 * @return UsedChunkStatus[] chunkHash => status
	 * @phpstan-return array<int, UsedChunkStatus>
	 */
	public function getUsedChunks() : array{
		return $this->usedChunks;
	}

	/**
	 * Returns whether the target chunk has been sent to this player.
	 */
	public function hasReceivedChunk(int $chunkX, int $chunkZ) : bool{
		$status = $this->usedChunks[World::chunkHash($chunkX, $chunkZ)] ?? null;
		return $status !== null and $status->equals(UsedChunkStatus::SENT());
	}

	/**
	 * Ticks the chunk-requesting mechanism.
	 */
	public function doChunkRequests() : void{
		if($this->nextChunkOrderRun !== PHP_INT_MAX and $this->nextChunkOrderRun-- <= 0){
			$this->nextChunkOrderRun = PHP_INT_MAX;
			$this->orderChunks();
		}

		if(count($this->loadQueue) > 0){
			$this->requestChunks();
		}
	}

	/**
	 * @return Position
	 */
	public function getSpawn(){
		if($this->hasValidCustomSpawn()){
			return $this->spawnPosition;
		}else{
			$world = $this->server->getWorldManager()->getDefaultWorld();

			return $world->getSafeSpawn();
		}
	}

	public function hasValidCustomSpawn() : bool{
		return $this->spawnPosition !== null and $this->spawnPosition->isValid();
	}

	/**
	 * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a
	 * Position object
	 *
	 * @param Vector3|Position|null $pos
	 */
	public function setSpawn(?Vector3 $pos) : void{
		if($pos !== null){
			if(!($pos instanceof Position)){
				$world = $this->getWorld();
			}else{
				$world = $pos->getWorld();
			}
			$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $world);
		}else{
			$this->spawnPosition = null;
		}
		$this->networkSession->syncPlayerSpawnPoint($this->getSpawn());
	}

	public function isSleeping() : bool{
		return $this->sleeping !== null;
	}

	public function sleepOn(Vector3 $pos) : bool{
		$pos = $pos->floor();
		$b = $this->getWorld()->getBlock($pos);

		$ev = new PlayerBedEnterEvent($this, $b);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		if($b instanceof Bed){
			$b->setOccupied();
			$this->getWorld()->setBlock($pos, $b);
		}

		$this->sleeping = $pos;

		$this->setSpawn($pos);

		$this->getWorld()->setSleepTicks(60);

		return true;
	}

	public function stopSleep() : void{
		if($this->sleeping instanceof Vector3){
			$b = $this->getWorld()->getBlock($this->sleeping);
			if($b instanceof Bed){
				$b->setOccupied(false);
				$this->getWorld()->setBlock($this->sleeping, $b);
			}
			(new PlayerBedLeaveEvent($this, $b))->call();

			$this->sleeping = null;

			$this->getWorld()->setSleepTicks(0);

			$this->networkSession->sendDataPacket(AnimatePacket::create($this->getId(), AnimatePacket::ACTION_STOP_SLEEP));
		}
	}

	public function getGamemode() : GameMode{
		return $this->gamemode;
	}

	protected function internalSetGameMode(GameMode $gameMode) : void{
		$this->gamemode = $gameMode;

		$this->allowFlight = $this->isCreative();
		$this->hungerManager->setEnabled($this->isSurvival());

		if($this->isSpectator()){
			$this->setFlying(true);
			$this->setSilent();
			$this->onGround = false;

			//TODO: HACK! this syncs the onground flag with the client so that flying works properly
			//this is a yucky hack but we don't have any other options :(
			$this->sendPosition($this->location, null, null, MovePlayerPacket::MODE_TELEPORT);
		}else{
			if($this->isSurvival()){
				$this->setFlying(false);
			}
			$this->setSilent(false);
			$this->checkGroundState(0, 0, 0, 0, 0, 0);
		}
	}

	/**
	 * Sets the gamemode, and if needed, kicks the Player.
	 */
	public function setGamemode(GameMode $gm) : bool{
		if($this->gamemode->equals($gm)){
			return false;
		}

		$ev = new PlayerGameModeChangeEvent($this, $gm);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		$this->internalSetGameMode($gm);

		if($this->isSpectator()){
			$this->despawnFromAll();
		}else{
			$this->spawnToAll();
		}

		$this->networkSession->syncGameMode($this->gamemode);
		return true;
	}

	/**
	 * NOTE: Because Survival and Adventure Mode share some similar behaviour, this method will also return true if the player is
	 * in Adventure Mode. Supply the $literal parameter as true to force a literal Survival Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 */
	public function isSurvival(bool $literal = false) : bool{
		return $this->gamemode->equals(GameMode::SURVIVAL()) or (!$literal and $this->gamemode->equals(GameMode::ADVENTURE()));
	}

	/**
	 * NOTE: Because Creative and Spectator Mode share some similar behaviour, this method will also return true if the player is
	 * in Spectator Mode. Supply the $literal parameter as true to force a literal Creative Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 */
	public function isCreative(bool $literal = false) : bool{
		return $this->gamemode->equals(GameMode::CREATIVE()) or (!$literal and $this->gamemode->equals(GameMode::SPECTATOR()));
	}

	/**
	 * NOTE: Because Adventure and Spectator Mode share some similar behaviour, this method will also return true if the player is
	 * in Spectator Mode. Supply the $literal parameter as true to force a literal Adventure Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 */
	public function isAdventure(bool $literal = false) : bool{
		return $this->gamemode->equals(GameMode::ADVENTURE()) or (!$literal and $this->gamemode->equals(GameMode::SPECTATOR()));
	}

	public function isSpectator() : bool{
		return $this->gamemode->equals(GameMode::SPECTATOR());
	}

	/**
	 * TODO: make this a dynamic ability instead of being hardcoded
	 */
	public function hasFiniteResources() : bool{
		return $this->gamemode->equals(GameMode::SURVIVAL()) or $this->gamemode->equals(GameMode::ADVENTURE());
	}

	public function isFireProof() : bool{
		return $this->isCreative();
	}

	public function getDrops() : array{
		if($this->hasFiniteResources()){
			return parent::getDrops();
		}

		return [];
	}

	public function getXpDropAmount() : int{
		if($this->hasFiniteResources()){
			return parent::getXpDropAmount();
		}

		return 0;
	}

	protected function checkGroundState(float $movX, float $movY, float $movZ, float $dx, float $dy, float $dz) : void{
		if($this->isSpectator()){
			$this->onGround = false;
		}else{
			$bb = clone $this->boundingBox;
			$bb->minY = $this->location->y - 0.2;
			$bb->maxY = $this->location->y + 0.2;

			$this->onGround = $this->isCollided = count($this->getWorld()->getCollisionBlocks($bb, true)) > 0;
		}
	}

	public function canBeMovedByCurrents() : bool{
		return false; //currently has no server-side movement
	}

	protected function checkNearEntities() : void{
		foreach($this->getWorld()->getNearbyEntities($this->boundingBox->expandedCopy(1, 0.5, 1), $this) as $entity){
			$entity->scheduleUpdate();

			if(!$entity->isAlive() or $entity->isFlaggedForDespawn()){
				continue;
			}

			$entity->onCollideWithPlayer($this);
		}
	}

	/**
	 * Sets the coordinates the player will move to next. This is processed at the end of each tick. Unless you have
	 * some particularly specialized logic, you probably want to use teleport() instead of this.
	 *
	 * This is used for processing movements sent by the player over network.
	 *
	 * @param Vector3 $newPos Coordinates of the player's feet, centered horizontally at the base of their bounding box.
	 *
	 * @return bool if the
	 */
	public function updateNextPosition(Vector3 $newPos) : bool{
		//TODO: teleport acks are a network specific thing and shouldn't be here

		$newPos = $newPos->asVector3();
		if($this->forceMoveSync !== null and $newPos->distanceSquared($this->forceMoveSync) > 1){  //Tolerate up to 1 block to avoid problems with client-sided physics when spawning in blocks
			$this->sendPosition($this->location, null, null, MovePlayerPacket::MODE_RESET);
			$this->logger->debug("Got outdated pre-teleport movement, received " . $newPos . ", expected " . $this->location->asVector3());
			//Still getting movements from before teleport, ignore them
			return false;
		}

		// Once we get a movement within a reasonable distance, treat it as a teleport ACK and remove position lock
		$this->forceMoveSync = null;

		$this->handleMovement($newPos);
		return true;
	}

	public function getInAirTicks() : int{
		return $this->inAirTicks;
	}

	protected function handleMovement(Vector3 $newPos) : void{
		$this->moveRateLimit--;
		if($this->moveRateLimit < 0){
			return;
		}

		$oldPos = $this->getLocation();
		$distanceSquared = $newPos->distanceSquared($oldPos);

		$revert = false;

		if($distanceSquared > 100){
			//TODO: this is probably too big if we process every movement
			/* !!! BEWARE YE WHO ENTER HERE !!!
			 *
			 * This is NOT an anti-cheat check. It is a safety check.
			 * Without it hackers can teleport with freedom on their own and cause lots of undesirable behaviour, like
			 * freezes, lag spikes and memory exhaustion due to sync chunk loading and collision checks across large distances.
			 * Not only that, but high-latency players can trigger such behaviour innocently.
			 *
			 * If you must tamper with this code, be aware that this can cause very nasty results. Do not waste our time
			 * asking for help if you suffer the consequences of messing with this.
			 */
			$this->logger->debug("Moved too fast, reverting movement");
			$this->logger->debug("Old position: " . $this->location->asVector3() . ", new position: " . $newPos);
			$revert = true;
		}elseif(!$this->getWorld()->isInLoadedTerrain($newPos) or !$this->getWorld()->isChunkGenerated($newPos->getFloorX() >> 4, $newPos->getFloorZ() >> 4)){
			$revert = true;
			$this->nextChunkOrderRun = 0;
		}

		if(!$revert and $distanceSquared != 0){
			$dx = $newPos->x - $this->location->x;
			$dy = $newPos->y - $this->location->y;
			$dz = $newPos->z - $this->location->z;

			$this->move($dx, $dy, $dz);
		}

		if($revert){
			$this->revertMovement($oldPos);
		}
	}

	/**
	 * Fires movement events and synchronizes player movement, every tick.
	 */
	protected function processMostRecentMovements() : void{
		$now = microtime(true);
		$multiplier = $this->lastMovementProcess !== null ? ($now - $this->lastMovementProcess) * 20 : 1;
		$exceededRateLimit = $this->moveRateLimit < 0;
		$this->moveRateLimit = min(self::MOVE_BACKLOG_SIZE, max(0, $this->moveRateLimit) + self::MOVES_PER_TICK * $multiplier);
		$this->lastMovementProcess = $now;

		$from = clone $this->lastLocation;
		$to = clone $this->location;

		$delta = $to->distanceSquared($from);
		$deltaAngle = abs($this->lastLocation->yaw - $to->yaw) + abs($this->lastLocation->pitch - $to->pitch);

		if($delta > 0.0001 or $deltaAngle > 1.0){
			$this->lastLocation = clone $to; //avoid PlayerMoveEvent modifying this

			$ev = new PlayerMoveEvent($this, $from, $to);

			$ev->call();

			if($ev->isCancelled()){
				$this->revertMovement($from);
				return;
			}

			if($to->distanceSquared($ev->getTo()) > 0.01){ //If plugins modify the destination
				$this->teleport($ev->getTo());
				return;
			}

			$this->broadcastMovement();

			$distance = sqrt((($from->x - $to->x) ** 2) + (($from->z - $to->z) ** 2));
			//TODO: check swimming (adds 0.015 exhaustion in MCPE)
			if($this->isSprinting()){
				$this->hungerManager->exhaust(0.1 * $distance, PlayerExhaustEvent::CAUSE_SPRINTING);
			}else{
				$this->hungerManager->exhaust(0.01 * $distance, PlayerExhaustEvent::CAUSE_WALKING);
			}

			if($this->nextChunkOrderRun > 20){
				$this->nextChunkOrderRun = 20;
			}
		}

		if($exceededRateLimit){ //client and server positions will be out of sync if this happens
			$this->server->getLogger()->debug("Player " . $this->getName() . " exceeded movement rate limit, forcing to last accepted position");
			$this->sendPosition($this->location, $this->location->getYaw(), $this->location->getPitch(), MovePlayerPacket::MODE_RESET);
		}
	}

	protected function revertMovement(Location $from) : void{
		$this->lastLocation = $from;

		$this->setPosition($from);
		$this->sendPosition($from, $from->yaw, $from->pitch, MovePlayerPacket::MODE_RESET);
	}

	public function fall(float $fallDistance) : void{
		if(!$this->flying){
			parent::fall($fallDistance);
		}
	}

	public function jump() : void{
		(new PlayerJumpEvent($this))->call();
		parent::jump();
	}

	public function setMotion(Vector3 $motion) : bool{
		if(parent::setMotion($motion)){
			$this->broadcastMotion();
			$this->networkSession->sendDataPacket(SetActorMotionPacket::create($this->id, $motion));

			return true;
		}
		return false;
	}

	protected function updateMovement(bool $teleport = false) : void{

	}

	protected function tryChangeMovement() : void{

	}

	public function onUpdate(int $currentTick) : bool{
		$tickDiff = $currentTick - $this->lastUpdate;

		if($tickDiff <= 0){
			return true;
		}

		$this->messageCounter = 2;

		$this->lastUpdate = $currentTick;

		if(!$this->isAlive() and $this->spawned){
			$this->onDeathUpdate($tickDiff);
			return true;
		}

		$this->timings->startTiming();

		if($this->spawned){
			$this->processMostRecentMovements();
			$this->motion = new Vector3(0, 0, 0); //TODO: HACK! (Fixes player knockback being messed up)
			if($this->onGround){
				$this->inAirTicks = 0;
			}else{
				$this->inAirTicks += $tickDiff;
			}

			Timings::$timerEntityBaseTick->startTiming();
			$this->entityBaseTick($tickDiff);
			Timings::$timerEntityBaseTick->stopTiming();

			if(!$this->isSpectator() and $this->isAlive()){
				Timings::$playerCheckNearEntitiesTimer->startTiming();
				$this->checkNearEntities();
				Timings::$playerCheckNearEntitiesTimer->stopTiming();
			}

			if($this->blockBreakHandler !== null and !$this->blockBreakHandler->update()){
				$this->blockBreakHandler = null;
			}
		}

		$this->timings->stopTiming();

		return true;
	}

	public function canBreathe() : bool{
		return $this->isCreative() or parent::canBreathe();
	}

	/**
	 * Returns whether the player can interact with the specified position. This checks distance and direction.
	 *
	 * @param float   $maxDiff defaults to half of the 3D diagonal width of a block
	 */
	public function canInteract(Vector3 $pos, float $maxDistance, float $maxDiff = M_SQRT3 / 2) : bool{
		$eyePos = $this->getEyePos();
		if($eyePos->distanceSquared($pos) > $maxDistance ** 2){
			return false;
		}

		$dV = $this->getDirectionVector();
		$eyeDot = $dV->dot($eyePos);
		$targetDot = $dV->dot($pos);
		return ($targetDot - $eyeDot) >= -$maxDiff;
	}

	/**
	 * Sends a chat message as this player. If the message begins with a / (forward-slash) it will be treated
	 * as a command.
	 */
	public function chat(string $message) : bool{
		$this->doCloseInventory();

		$message = TextFormat::clean($message, false);
		foreach(explode("\n", $message) as $messagePart){
			if(trim($messagePart) !== "" and strlen($messagePart) <= 255 and $this->messageCounter-- > 0){
				if(strpos($messagePart, './') === 0){
					$messagePart = substr($messagePart, 1);
				}

				$ev = new PlayerCommandPreprocessEvent($this, $messagePart);
				$ev->call();

				if($ev->isCancelled()){
					break;
				}

				if(strpos($ev->getMessage(), "/") === 0){
					Timings::$playerCommandTimer->startTiming();
					$this->server->dispatchCommand($ev->getPlayer(), substr($ev->getMessage(), 1));
					Timings::$playerCommandTimer->stopTiming();
				}else{
					$ev = new PlayerChatEvent($this, $ev->getMessage(), $this->server->getBroadcastChannelSubscribers(Server::BROADCAST_CHANNEL_USERS));
					$ev->call();
					if(!$ev->isCancelled()){
						$this->server->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(), [$ev->getPlayer()->getDisplayName(), $ev->getMessage()]), $ev->getRecipients());
					}
				}
			}
		}

		return true;
	}

	public function selectHotbarSlot(int $hotbarSlot) : bool{
		if(!$this->inventory->isHotbarSlot($hotbarSlot)){ //TODO: exception here?
			return false;
		}
		if($hotbarSlot === $this->inventory->getHeldItemIndex()){
			return true;
		}

		$ev = new PlayerItemHeldEvent($this, $this->inventory->getItem($hotbarSlot), $hotbarSlot);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		$this->inventory->setHeldItemIndex($hotbarSlot, false);
		$this->setUsingItem(false);

		return true;
	}

	/**
	 * Activates the item in hand, for example throwing a projectile.
	 *
	 * @return bool if it did something
	 */
	public function useHeldItem() : bool{
		$directionVector = $this->getDirectionVector();
		$item = $this->inventory->getItemInHand();
		$oldItem = clone $item;

		$ev = new PlayerItemUseEvent($this, $item, $directionVector);
		if($this->hasItemCooldown($item) or $this->isSpectator()){
			$ev->cancel();
		}

		$ev->call();

		if($ev->isCancelled()){
			return false;
		}

		$result = $item->onClickAir($this, $directionVector);
		if($result->equals(ItemUseResult::FAIL())){
			return false;
		}

		$this->resetItemCooldown($item);
		if($this->hasFiniteResources() and !$item->equalsExact($oldItem) and $oldItem->equalsExact($this->inventory->getItemInHand())){
			$this->inventory->setItemInHand($item);
		}

		$this->setUsingItem($item instanceof Releasable);

		return true;
	}

	/**
	 * Consumes the currently-held item.
	 *
	 * @return bool if the consumption succeeded.
	 */
	public function consumeHeldItem() : bool{
		$slot = $this->inventory->getItemInHand();
		if($slot instanceof ConsumableItem){
			$oldItem = clone $slot;

			$ev = new PlayerItemConsumeEvent($this, $slot);
			if($this->hasItemCooldown($slot)){
				$ev->cancel();
			}
			$ev->call();

			if($ev->isCancelled() or !$this->consumeObject($slot)){
				return false;
			}

			$this->setUsingItem(false);
			$this->resetItemCooldown($slot);

			if($this->hasFiniteResources() && $oldItem->equalsExact($this->inventory->getItemInHand())){
				$slot->pop();
				$this->inventory->setItemInHand($slot);
				$this->inventory->addItem($slot->getResidue());
			}

			return true;
		}

		return false;
	}

	/**
	 * Releases the held item, for example to fire a bow. This should be preceded by a call to useHeldItem().
	 *
	 * @return bool if it did something.
	 */
	public function releaseHeldItem() : bool{
		try{
			$item = $this->inventory->getItemInHand();
			if(!$this->isUsingItem() or $this->hasItemCooldown($item)){
				return false;
			}

			$oldItem = clone $item;

			$result = $item->onReleaseUsing($this);
			if($result->equals(ItemUseResult::SUCCESS())){
				$this->resetItemCooldown($item);
				if(!$item->equalsExact($oldItem) and $oldItem->equalsExact($this->inventory->getItemInHand())){
					$this->inventory->setItemInHand($item);
				}
				return true;
			}

			return false;
		}finally{
			$this->setUsingItem(false);
		}
	}

	public function pickBlock(Vector3 $pos, bool $addTileNBT) : bool{
		$block = $this->getWorld()->getBlock($pos);
		if($block instanceof UnknownBlock){
			return true;
		}

		$item = $block->getPickedItem($addTileNBT);

		$ev = new PlayerBlockPickEvent($this, $block, $item);
		$existingSlot = $this->inventory->first($item);
		if($existingSlot === -1 and $this->hasFiniteResources()){
			$ev->cancel();
		}
		$ev->call();

		if(!$ev->isCancelled()){
			if($existingSlot !== -1){
				if($existingSlot < $this->inventory->getHotbarSize()){
					$this->inventory->setHeldItemIndex($existingSlot);
				}else{
					$this->inventory->swap($this->inventory->getHeldItemIndex(), $existingSlot);
				}
			}else{
				$firstEmpty = $this->inventory->firstEmpty();
				if($firstEmpty === -1){ //full inventory
					$this->inventory->setItemInHand($item);
				}elseif($firstEmpty < $this->inventory->getHotbarSize()){
					$this->inventory->setItem($firstEmpty, $item);
					$this->inventory->setHeldItemIndex($firstEmpty);
				}else{
					$this->inventory->swap($this->inventory->getHeldItemIndex(), $firstEmpty);
					$this->inventory->setItemInHand($item);
				}
			}
		}

		return true;
	}

	/**
	 * Performs a left-click (attack) action on the block.
	 *
	 * @return bool if an action took place successfully
	 */
	public function attackBlock(Vector3 $pos, int $face) : bool{
		if($pos->distanceSquared($this->location) > 10000){
			return false; //TODO: maybe this should throw an exception instead?
		}

		$target = $this->getWorld()->getBlock($pos);

		$ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, null, $face, PlayerInteractEvent::LEFT_CLICK_BLOCK);
		if($this->isSpectator()){
			$ev->cancel();
		}
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
		if($target->onAttack($this->inventory->getItemInHand(), $face, $this)){
			return true;
		}

		$block = $target->getSide($face);
		if($block->getId() === BlockLegacyIds::FIRE){
			$this->getWorld()->setBlock($block->getPos(), VanillaBlocks::AIR());
			$this->getWorld()->addSound($block->getPos()->add(0.5, 0.5, 0.5), new FireExtinguishSound());
			return true;
		}

		if(!$this->isCreative()){
			$this->blockBreakHandler = SurvivalBlockBreakHandler::createIfNecessary($this, $pos, $target, $face, 16);
		}

		return true;
	}

	public function continueBreakBlock(Vector3 $pos, int $face) : void{
		if($this->blockBreakHandler !== null and $this->blockBreakHandler->getBlockPos()->distanceSquared($pos) < 0.0001){
			//TODO: check the targeted block matches the one we're told to target
			$this->blockBreakHandler->setTargetedFace($face);
		}
	}

	public function stopBreakBlock(Vector3 $pos) : void{
		if($this->blockBreakHandler !== null and $this->blockBreakHandler->getBlockPos()->distanceSquared($pos) < 0.0001){
			$this->blockBreakHandler = null;
		}
	}

	/**
	 * Breaks the block at the given position using the currently-held item.
	 *
	 * @return bool if the block was successfully broken, false if a rollback needs to take place.
	 */
	public function breakBlock(Vector3 $pos) : bool{
		$this->doCloseInventory();

		if($this->canInteract($pos->add(0.5, 0.5, 0.5), $this->isCreative() ? 13 : 7)){
			$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
			$this->stopBreakBlock($pos);
			$item = $this->inventory->getItemInHand();
			$oldItem = clone $item;
			if($this->getWorld()->useBreakOn($pos, $item, $this, true)){
				if($this->hasFiniteResources() and !$item->equalsExact($oldItem) and $oldItem->equalsExact($this->inventory->getItemInHand())){
					$this->inventory->setItemInHand($item);
				}
				$this->hungerManager->exhaust(0.025, PlayerExhaustEvent::CAUSE_MINING);
				return true;
			}
		}

		return false;
	}

	/**
	 * Touches the block at the given position with the currently-held item.
	 *
	 * @return bool if it did something
	 */
	public function interactBlock(Vector3 $pos, int $face, Vector3 $clickOffset) : bool{
		$this->setUsingItem(false);

		if($this->canInteract($pos->add(0.5, 0.5, 0.5), 13)){
			$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
			$item = $this->inventory->getItemInHand(); //this is a copy of the real item
			$oldItem = clone $item;
			if($this->getWorld()->useItemOn($pos, $item, $face, $clickOffset, $this, true)){
				if($this->hasFiniteResources() and !$item->equalsExact($oldItem) and $oldItem->equalsExact($this->inventory->getItemInHand())){
					$this->inventory->setItemInHand($item);
				}
				return true;
			}
		}

		return false;
	}

	/**
	 * Attacks the given entity with the currently-held item.
	 * TODO: move this up the class hierarchy
	 *
	 * @return bool if the entity was dealt damage
	 */
	public function attackEntity(Entity $entity) : bool{
		if(!$entity->isAlive()){
			return false;
		}
		if($entity instanceof ItemEntity or $entity instanceof Arrow){
			$this->kick("Attempting to attack an invalid entity");
			$this->logger->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidEntity", [$this->getName()]));
			return false;
		}

		$heldItem = $this->inventory->getItemInHand();
		$oldItem = clone $heldItem;

		$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $heldItem->getAttackPoints());
		if($this->isSpectator() or !$this->canInteract($entity->getLocation(), 8) or ($entity instanceof Player and !$this->server->getConfigGroup()->getConfigBool("pvp"))){
			$ev->cancel();
		}

		$meleeEnchantmentDamage = 0;
		/** @var EnchantmentInstance[] $meleeEnchantments */
		$meleeEnchantments = [];
		foreach($heldItem->getEnchantments() as $enchantment){
			$type = $enchantment->getType();
			if($type instanceof MeleeWeaponEnchantment and $type->isApplicableTo($entity)){
				$meleeEnchantmentDamage += $type->getDamageBonus($enchantment->getLevel());
				$meleeEnchantments[] = $enchantment;
			}
		}
		$ev->setModifier($meleeEnchantmentDamage, EntityDamageEvent::MODIFIER_WEAPON_ENCHANTMENTS);

		if(!$this->isSprinting() and !$this->isFlying() and $this->fallDistance > 0 and !$this->effectManager->has(VanillaEffects::BLINDNESS()) and !$this->isUnderwater()){
			$ev->setModifier($ev->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
		}

		$entity->attack($ev);

		$soundPos = $entity->getPosition()->add(0, $entity->width / 2, 0);
		if($ev->isCancelled()){
			$this->getWorld()->addSound($soundPos, new EntityAttackNoDamageSound());
			return false;
		}
		$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
		$this->getWorld()->addSound($soundPos, new EntityAttackSound());

		if($ev->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0 and $entity instanceof Living){
			$entity->broadcastAnimation(new CriticalHitAnimation($entity));
		}

		foreach($meleeEnchantments as $enchantment){
			$type = $enchantment->getType();
			assert($type instanceof MeleeWeaponEnchantment);
			$type->onPostAttack($this, $entity, $enchantment->getLevel());
		}

		if($this->isAlive()){
			//reactive damage like thorns might cause us to be killed by attacking another mob, which
			//would mean we'd already have dropped the inventory by the time we reached here
			if($heldItem->onAttackEntity($entity) and $this->hasFiniteResources() and $oldItem->equalsExact($this->inventory->getItemInHand())){ //always fire the hook, even if we are survival
				$this->inventory->setItemInHand($heldItem);
			}

			$this->hungerManager->exhaust(0.3, PlayerExhaustEvent::CAUSE_ATTACK);
		}

		return true;
	}

	/**
	 * Interacts with the given entity using the currently-held item.
	 */
	public function interactEntity(Entity $entity, Vector3 $clickPos) : bool{
		//TODO
		return false;
	}

	public function toggleSprint(bool $sprint) : bool{
		$ev = new PlayerToggleSprintEvent($this, $sprint);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->setSprinting($sprint);
		return true;
	}

	public function toggleSneak(bool $sneak) : bool{
		$ev = new PlayerToggleSneakEvent($this, $sneak);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->setSneaking($sneak);
		return true;
	}

	public function toggleFlight(bool $fly) : bool{
		$ev = new PlayerToggleFlightEvent($this, $fly);
		if(!$this->allowFlight){
			$ev->cancel();
		}
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		//don't use setFlying() here, to avoid feedback loops - TODO: get rid of this hack
		$this->flying = $fly;
		$this->resetFallDistance();
		return true;
	}

	/**
	 * Drops an item on the ground in front of the player.
	 */
	public function dropItem(Item $item) : void{
		$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
		$this->getWorld()->dropItem($this->location->add(0, 1.3, 0), $item, $this->getDirectionVector()->multiply(0.4), 40);
	}

	/**
	 * Adds a title text to the user's screen, with an optional subtitle.
	 *
	 * @param int    $fadeIn Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int    $stay Duration in ticks to stay on screen for
	 * @param int    $fadeOut Duration in ticks for fade-out.
	 */
	public function sendTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1) : void{
		$this->setTitleDuration($fadeIn, $stay, $fadeOut);
		if($subtitle !== ""){
			$this->sendSubTitle($subtitle);
		}
		$this->networkSession->onTitle($title);
	}

	/**
	 * Sets the subtitle message, without sending a title.
	 */
	public function sendSubTitle(string $subtitle) : void{
		$this->networkSession->onSubTitle($subtitle);
	}

	/**
	 * Adds small text to the user's screen.
	 */
	public function sendActionBarMessage(string $message) : void{
		$this->networkSession->onActionBar($message);
	}

	/**
	 * Removes the title from the client's screen.
	 */
	public function removeTitles() : void{
		$this->networkSession->onClearTitle();
	}

	/**
	 * Resets the title duration settings to defaults and removes any existing titles.
	 */
	public function resetTitles() : void{
		$this->networkSession->onResetTitleOptions();
	}

	/**
	 * Sets the title duration.
	 *
	 * @param int $fadeIn Title fade-in time in ticks.
	 * @param int $stay Title stay time in ticks.
	 * @param int $fadeOut Title fade-out time in ticks.
	 */
	public function setTitleDuration(int $fadeIn, int $stay, int $fadeOut) : void{
		if($fadeIn >= 0 and $stay >= 0 and $fadeOut >= 0){
			$this->networkSession->onTitleDuration($fadeIn, $stay, $fadeOut);
		}
	}

	/**
	 * Sends a direct chat message to a player
	 *
	 * @param TranslationContainer|string $message
	 */
	public function sendMessage($message) : void{
		if($message instanceof TranslationContainer){
			$this->sendTranslation($message->getText(), $message->getParameters());
			return;
		}

		$this->networkSession->onRawChatMessage($this->getLanguage()->translateString($message));
	}

	/**
	 * @param string[] $parameters
	 */
	public function sendTranslation(string $message, array $parameters = []) : void{
		if(!$this->server->isLanguageForced()){
			foreach($parameters as $i => $p){
				$parameters[$i] = $this->getLanguage()->translateString($p, [], "pocketmine.");
			}
			$this->networkSession->onTranslatedChatMessage($this->getLanguage()->translateString($message, $parameters, "pocketmine."), $parameters);
		}else{
			$this->sendMessage($this->getLanguage()->translateString($message, $parameters));
		}
	}

	/**
	 * @param string[] $args
	 */
	public function sendJukeboxPopup(string $key, array $args) : void{
		if($this->networkSession !== null){
			$this->networkSession->onJukeboxPopup($key, $args);
		}
	}

	/**
	 * Sends a popup message to the player
	 *
	 * TODO: add translation type popups
	 */
	public function sendPopup(string $message) : void{
		$this->networkSession->onPopup($message);
	}

	public function sendTip(string $message) : void{
		$this->networkSession->onTip($message);
	}

	/**
	 * Sends a Form to the player, or queue to send it if a form is already open.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function sendForm(Form $form) : void{
		$id = $this->formIdCounter++;
		if($this->networkSession->onFormSent($id, $form)){
			$this->forms[$id] = $form;
		}
	}

	/**
	 * @param mixed $responseData
	 */
	public function onFormSubmit(int $formId, $responseData) : bool{
		if(!isset($this->forms[$formId])){
			$this->logger->debug("Got unexpected response for form $formId");
			return false;
		}

		try{
			$this->forms[$formId]->handleResponse($this, $responseData);
		}catch(FormValidationException $e){
			$this->logger->critical("Failed to validate form " . get_class($this->forms[$formId]) . ": " . $e->getMessage());
			$this->logger->logException($e);
		}finally{
			unset($this->forms[$formId]);
		}

		return true;
	}

	/**
	 * Transfers a player to another server.
	 *
	 * @param string $address The IP address or hostname of the destination server
	 * @param int    $port The destination port, defaults to 19132
	 * @param string $message Message to show in the console when closing the player
	 *
	 * @return bool if transfer was successful.
	 */
	public function transfer(string $address, int $port = 19132, string $message = "transfer") : bool{
		$ev = new PlayerTransferEvent($this, $address, $port, $message);
		$ev->call();
		if(!$ev->isCancelled()){
			$this->networkSession->transfer($ev->getAddress(), $ev->getPort(), $ev->getMessage());
			return true;
		}

		return false;
	}

	/**
	 * Kicks a player from the server
	 *
	 * @param TranslationContainer|string|null $quitMessage
	 */
	public function kick(string $reason = "", $quitMessage = null) : bool{
		$ev = new PlayerKickEvent($this, $reason, $quitMessage ?? $this->getLeaveMessage());
		$ev->call();
		if(!$ev->isCancelled()){
			$reason = $ev->getReason();
			if($reason === ""){
				$reason = "disconnectionScreen.noReason";
			}
			$this->disconnect($reason, $ev->getQuitMessage());

			return true;
		}

		return false;
	}

	/**
	 * Removes the player from the server. This cannot be cancelled.
	 * This is used for remote disconnects and for uninterruptible disconnects (for example, when the server shuts down).
	 *
	 * Note for plugin developers: Prefer kick() instead of this method.
	 * That way other plugins can have a say in whether the player is removed or not.
	 *
	 * Note for internals developers: Do not call this from network sessions. It will cause a feedback loop.
	 *
	 * @param string                           $reason Shown to the player, usually this will appear on their disconnect screen.
	 * @param TranslationContainer|string|null $quitMessage Message to broadcast to online players (null will use default)
	 */
	public function disconnect(string $reason, $quitMessage = null) : void{
		if(!$this->isConnected()){
			return;
		}

		$this->networkSession->onPlayerDestroyed($reason);
		$this->onPostDisconnect($reason, $quitMessage);
	}

	/**
	 * @internal
	 * This method executes post-disconnect actions and cleanups.
	 *
	 * @param string                           $reason Shown to the player, usually this will appear on their disconnect screen.
	 * @param TranslationContainer|string|null $quitMessage Message to broadcast to online players (null will use default)
	 */
	public function onPostDisconnect(string $reason, $quitMessage) : void{
		if($this->isConnected()){
			throw new \InvalidStateException("Player is still connected");
		}

		//prevent the player receiving their own disconnect message
		$this->server->unsubscribeFromAllBroadcastChannels($this);

		$ev = new PlayerQuitEvent($this, $quitMessage ?? $this->getLeaveMessage(), $reason);
		$ev->call();
		if(($quitMessage = $ev->getQuitMessage()) != ""){
			$this->server->broadcastMessage($quitMessage);
		}
		$this->save();

		$this->spawned = false;

		$this->stopSleep();
		$this->blockBreakHandler = null;
		$this->despawnFromAll();

		$this->server->removeOnlinePlayer($this);

		foreach($this->server->getOnlinePlayers() as $player){
			if(!$player->canSee($this)){
				$player->showPlayer($this);
			}
		}
		$this->hiddenPlayers = [];

		if($this->location->isValid()){
			foreach($this->usedChunks as $index => $status){
				World::getXZ($index, $chunkX, $chunkZ);
				$this->unloadChunk($chunkX, $chunkZ);
			}
		}
		$this->usedChunks = [];
		$this->loadQueue = [];

		$this->removeCurrentWindow();
		$this->removePermanentInventories();

		$this->perm->destroyCycles();

		$this->flagForDespawn();
	}

	protected function onDispose() : void{
		$this->disconnect("Player destroyed");
		$this->cursorInventory->removeAllViewers();
		$this->craftingGrid->removeAllViewers();
		parent::onDispose();
	}

	protected function destroyCycles() : void{
		$this->networkSession = null;
		$this->cursorInventory = null;
		$this->craftingGrid = null;
		$this->spawnPosition = null;
		$this->perm = null;
		$this->blockBreakHandler = null;
		parent::destroyCycles();
	}

	/**
	 * @return mixed[]
	 */
	public function __debugInfo() : array{
		return [];
	}

	public function __destruct(){
		parent::__destruct();
		$this->logger->debug("Destroyed by garbage collector");
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	public function setCanSaveWithChunk(bool $value) : void{
		throw new \BadMethodCallException("Players can't be saved with chunks");
	}

	/**
	 * Handles player data saving
	 *
	 * @throws \InvalidStateException if the player is closed
	 */
	public function save() : void{
		if($this->closed){
			throw new \InvalidStateException("Tried to save closed player");
		}

		$nbt = $this->saveNBT();

		if($this->location->isValid()){
			$nbt->setString("Level", $this->getWorld()->getFolderName());
		}

		if($this->hasValidCustomSpawn()){
			$spawn = $this->getSpawn();
			$nbt->setString("SpawnLevel", $spawn->getWorld()->getFolderName());
			$nbt->setInt("SpawnX", $spawn->getFloorX());
			$nbt->setInt("SpawnY", $spawn->getFloorY());
			$nbt->setInt("SpawnZ", $spawn->getFloorZ());
		}

		if(!$this->isAlive()){
			$spawn = $this->getSpawn();
			//hack for respawn after quit
			$nbt->setTag("Pos", new ListTag([
				new DoubleTag($spawn->getFloorX()),
				new DoubleTag($spawn->getFloorY()),
				new DoubleTag($spawn->getFloorZ())
			]));
		}

		$nbt->setInt("playerGameType", $this->gamemode->getMagicNumber());
		$nbt->setLong("firstPlayed", $this->firstPlayed);
		$nbt->setLong("lastPlayed", (int) floor(microtime(true) * 1000));

		$this->server->saveOfflinePlayerData($this->username, $nbt);
	}

	protected function onDeath() : void{
		//Crafting grid must always be evacuated even if keep-inventory is true. This dumps the contents into the
		//main inventory and drops the rest on the ground.
		$this->doCloseInventory();

		$ev = new PlayerDeathEvent($this, $this->getDrops(), $this->getXpDropAmount(), null);
		$ev->call();

		if(!$ev->getKeepInventory()){
			foreach($ev->getDrops() as $item){
				$this->getWorld()->dropItem($this->location, $item);
			}

			if($this->inventory !== null){
				$this->inventory->setHeldItemIndex(0);
				$this->inventory->clearAll();
			}
			if($this->armorInventory !== null){
				$this->armorInventory->clearAll();
			}
		}

		$this->getWorld()->dropExperience($this->location, $ev->getXpDropAmount());
		$this->xpManager->setXpAndProgress(0, 0.0);

		if($ev->getDeathMessage() != ""){
			$this->server->broadcastMessage($ev->getDeathMessage());
		}

		$this->startDeathAnimation();

		$this->networkSession->onServerDeath();
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		parent::onDeathUpdate($tickDiff);
		return false; //never flag players for despawn
	}

	public function respawn() : void{
		if($this->server->isHardcore()){
			if($this->kick("You have been banned because you died in hardcore mode")){ //this allows plugins to prevent the ban by cancelling PlayerKickEvent
				$this->server->getNameBans()->addBan($this->getName(), "Died in hardcore mode");
			}
			return;
		}

		$ev = new PlayerRespawnEvent($this, $this->getSpawn());
		$ev->call();

		$realSpawn = Position::fromObject($ev->getRespawnPosition()->add(0.5, 0, 0.5), $ev->getRespawnPosition()->getWorld());
		$this->teleport($realSpawn);

		$this->setSprinting(false);
		$this->setSneaking(false);

		$this->extinguish();
		$this->setAirSupplyTicks($this->getMaxAirSupplyTicks());
		$this->deadTicks = 0;
		$this->noDamageTicks = 60;

		$this->effectManager->clear();
		$this->setHealth($this->getMaxHealth());

		foreach($this->attributeMap->getAll() as $attr){
			$attr->resetToDefault();
		}

		$this->spawnToAll();
		$this->scheduleUpdate();

		$this->networkSession->onServerRespawn();
	}

	protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
		parent::applyPostDamageEffects($source);

		$this->hungerManager->exhaust(0.3, PlayerExhaustEvent::CAUSE_DAMAGE);
	}

	public function attack(EntityDamageEvent $source) : void{
		if(!$this->isAlive()){
			return;
		}

		if($this->isCreative()
			and $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
			and $source->getCause() !== EntityDamageEvent::CAUSE_VOID
		){
			$source->cancel();
		}elseif($this->allowFlight and $source->getCause() === EntityDamageEvent::CAUSE_FALL){
			$source->cancel();
		}

		parent::attack($source);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::ACTION, $this->startAction > -1);

		$properties->setPlayerFlag(PlayerMetadataFlags::SLEEP, $this->sleeping !== null);
		$properties->setBlockPos(EntityMetadataProperties::PLAYER_BED_POSITION, $this->sleeping ?? new Vector3(0, 0, 0));
	}

	public function sendData(?array $targets, ?array $data = null) : void{
		if($targets === null){
			$targets = $this->getViewers();
			$targets[] = $this;
		}
		parent::sendData($targets, $data);
	}

	public function broadcastAnimation(Animation $animation, ?array $targets = null) : void{
		if($this->spawned and $targets === null){
			$targets = $this->getViewers();
			$targets[] = $this;
		}
		parent::broadcastAnimation($animation, $targets);
	}

	/**
	 * TODO: remove this
	 */
	public function sendPosition(Vector3 $pos, ?float $yaw = null, ?float $pitch = null, int $mode = MovePlayerPacket::MODE_NORMAL) : void{
		$this->networkSession->syncMovement($pos, $yaw, $pitch, $mode);

		$this->forceMoveSync = $pos->asVector3();
		$this->ySize = 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if(parent::teleport($pos, $yaw, $pitch)){

			$this->removeCurrentWindow();

			$this->sendPosition($this->location, $this->location->yaw, $this->location->pitch, MovePlayerPacket::MODE_TELEPORT);
			$this->broadcastMovement(true);

			$this->spawnToAll();

			$this->resetFallDistance();
			$this->nextChunkOrderRun = 0;
			if($this->spawnChunkLoadCount !== -1){
				$this->spawnChunkLoadCount = 0;
			}
			$this->stopSleep();
			$this->blockBreakHandler = null;

			//TODO: workaround for player last pos not getting updated
			//Entity::updateMovement() normally handles this, but it's overridden with an empty function in Player
			$this->resetLastMovements();

			return true;
		}

		return false;
	}

	protected function addDefaultWindows() : void{
		$this->cursorInventory = new PlayerCursorInventory($this);
		$this->craftingGrid = new CraftingGrid($this, CraftingGrid::SIZE_SMALL);

		$this->addPermanentInventories($this->inventory, $this->armorInventory, $this->cursorInventory);

		//TODO: more windows
	}

	public function getCursorInventory() : PlayerCursorInventory{
		return $this->cursorInventory;
	}

	public function getCraftingGrid() : CraftingGrid{
		return $this->craftingGrid;
	}

	public function setCraftingGrid(CraftingGrid $grid) : void{
		$this->craftingGrid = $grid;
	}

	/**
	 * @internal Called to clean up crafting grid and cursor inventory when it is detected that the player closed their
	 * inventory.
	 */
	public function doCloseInventory() : void{
		/** @var Inventory[] $inventories */
		$inventories = [$this->craftingGrid, $this->cursorInventory];
		foreach($inventories as $inventory){
			$contents = $inventory->getContents();
			if(count($contents) > 0){
				$drops = $this->inventory->addItem(...$contents);
				foreach($drops as $drop){
					$this->dropItem($drop);
				}

				$inventory->clearAll();
			}
		}

		if($this->craftingGrid->getGridWidth() > CraftingGrid::SIZE_SMALL){
			$this->craftingGrid = new CraftingGrid($this, CraftingGrid::SIZE_SMALL);
		}
	}

	/**
	 * Returns the inventory the player is currently viewing. This might be a chest, furnace, or any other container.
	 */
	public function getCurrentWindow() : ?Inventory{
		return $this->currentWindow;
	}

	/**
	 * Opens an inventory window to the player. Returns if it was successful.
	 */
	public function setCurrentWindow(Inventory $inventory) : bool{
		if($inventory === $this->currentWindow){
			return true;
		}
		$ev = new InventoryOpenEvent($inventory, $this);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		//TODO: client side race condition here makes the opening work incorrectly
		$this->removeCurrentWindow();

		$this->logger->debug("Opening inventory " . get_class($inventory) . "#" . spl_object_id($inventory));
		$this->networkSession->getInvManager()->onCurrentWindowChange($inventory);
		$inventory->onOpen($this);
		$this->currentWindow = $inventory;
		return true;
	}

	public function removeCurrentWindow() : void{
		if($this->currentWindow !== null){
			(new InventoryCloseEvent($this->currentWindow, $this))->call();

			$this->logger->debug("Closing inventory " . get_class($this->currentWindow) . "#" . spl_object_id($this->currentWindow));
			$this->currentWindow->onClose($this);
			if($this->isConnected()){
				$this->networkSession->getInvManager()->onCurrentWindowRemove();
			}
			$this->currentWindow = null;
		}
	}

	protected function addPermanentInventories(Inventory ...$inventories) : void{
		foreach($inventories as $inventory){
			$inventory->onOpen($this);
			$this->permanentWindows[spl_object_id($inventory)] = $inventory;
		}
	}

	protected function removePermanentInventories() : void{
		foreach($this->permanentWindows as $inventory){
			$inventory->onClose($this);
		}
		$this->permanentWindows = [];
	}

	use ChunkListenerNoOpTrait {
		onChunkChanged as private;
		onChunkUnloaded as private;
	}

	public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$status = $this->usedChunks[$hash = World::chunkHash($chunkX, $chunkZ)] ?? null;
		if($status !== null && !$status->equals(UsedChunkStatus::NEEDED())){
			$this->usedChunks[$hash] = UsedChunkStatus::NEEDED();
			$this->nextChunkOrderRun = 0;
		}
	}

	public function onChunkUnloaded(int $chunkX, int $chunkZ, Chunk $chunk) : void{
		if($this->isUsingChunk($chunkX, $chunkZ)){
			$this->logger->debug("Detected forced unload of chunk " . $chunkX . " " . $chunkZ);
			$this->unloadChunk($chunkX, $chunkZ);
		}
	}
}
