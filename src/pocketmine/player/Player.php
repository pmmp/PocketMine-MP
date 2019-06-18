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
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\UnknownBlock;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerEditBookEvent;
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
use pocketmine\inventory\CraftingGrid;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\item\Consumable;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\item\WritableBook;
use pocketmine\item\WrittenBook;
use pocketmine\lang\TextContainer;
use pocketmine\lang\TranslationContainer;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\PlayerMetadataFlags;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissibleDelegateTrait;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;
use pocketmine\world\ChunkListener;
use pocketmine\world\ChunkLoader;
use pocketmine\world\format\Chunk;
use pocketmine\world\particle\PunchBlockParticle;
use pocketmine\world\Position;
use pocketmine\world\World;
use function abs;
use function array_search;
use function assert;
use function ceil;
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
class Player extends Human implements CommandSender, ChunkLoader, ChunkListener, IPlayer{
	use PermissibleDelegateTrait {
		recalculatePermissions as private delegateRecalculatePermissions;
	}

	/**
	 * Checks a supplied username and checks it is valid.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function isValidUserName(?string $name) : bool{
		if($name === null){
			return false;
		}

		$lname = strtolower($name);
		$len = strlen($name);
		return $lname !== "rcon" and $lname !== "console" and $len >= 1 and $len <= 16 and preg_match("/[^A-Za-z0-9_ ]/", $name) === 0;
	}

	/** @var NetworkSession */
	protected $networkSession;

	/** @var bool */
	public $spawned = false;

	/** @var string */
	protected $username;
	/** @var string */
	protected $iusername;
	/** @var string */
	protected $displayName;
	/** @var int */
	protected $randomClientId;
	/** @var string */
	protected $xuid = "";
	/** @var bool */
	protected $authenticated;
	/** @var PlayerInfo */
	protected $playerInfo;

	/** @var int */
	protected $lastInventoryNetworkId = 2;
	/** @var Inventory[] network ID => inventory */
	protected $networkIdToInventoryMap = [];
	/** @var Inventory|null */
	protected $currentWindow = null;
	/** @var Inventory[] */
	protected $permanentWindows = [];
	/** @var PlayerCursorInventory */
	protected $cursorInventory;
	/** @var CraftingGrid */
	protected $craftingGrid = null;

	/** @var int */
	protected $messageCounter = 2;
	/** @var bool */
	protected $removeFormat = true;

	/** @var bool[] name of achievement => bool */
	protected $achievements = [];
	/** @var int */
	protected $firstPlayed;
	/** @var int */
	protected $lastPlayed;
	/** @var GameMode */
	protected $gamemode;

	/** @var bool[] chunkHash => bool (true = sent, false = needs sending) */
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

	/** @var bool[] map: raw UUID (string) => bool */
	protected $hiddenPlayers = [];

	/** @var Vector3|null */
	protected $newPosition;
	/** @var bool */
	protected $isTeleporting = false;
	/** @var int */
	protected $inAirTicks = 0;
	/** @var float */
	protected $stepHeight = 0.6;
	/** @var bool */
	protected $allowMovementCheats = false;

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

	/**
	 * @param Server         $server
	 * @param NetworkSession $session
	 * @param PlayerInfo     $playerInfo
	 * @param bool           $authenticated
	 */
	public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated){
		$username = TextFormat::clean($playerInfo->getUsername());
		$this->logger = new \PrefixedLogger($server->getLogger(), "Player: $username");

		$this->server = $server;
		$this->networkSession = $session;
		$this->playerInfo = $playerInfo;
		$this->authenticated = $authenticated;
		$this->skin = $this->playerInfo->getSkin();

		$this->username = $username;
		$this->displayName = $this->username;
		$this->iusername = strtolower($this->username);
		$this->locale = $this->playerInfo->getLocale();
		$this->randomClientId = $this->playerInfo->getClientId();

		$this->uuid = $this->playerInfo->getUuid();
		$this->rawUUID = $this->uuid->toBinary();
		$this->xuid = $authenticated ? $this->playerInfo->getXuid() : "";

		$this->perm = new PermissibleBase($this);
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-sending.per-tick", 4);
		$this->spawnThreshold = (int) (($this->server->getProperty("chunk-sending.spawn-radius", 4) ** 2) * M_PI);

		$this->allowMovementCheats = (bool) $this->server->getProperty("player.anti-cheat.allow-movement-cheats", false);

		$namedtag = $this->server->getOfflinePlayerData($this->username); //TODO: make this async

		$spawnReset = false;

		if($namedtag !== null and ($world = $this->server->getWorldManager()->getWorldByName($namedtag->getString("Level", "", true))) !== null){
			/** @var float[] $pos */
			$pos = $namedtag->getListTag("Pos")->getAllValues();
			$spawn = new Vector3($pos[0], $pos[1], $pos[2]);
		}else{
			$world = $this->server->getWorldManager()->getDefaultWorld(); //TODO: default world might be null
			$spawn = $world->getSafeSpawn();
			$spawnReset = true;
		}

		//load the spawn chunk so we can see the terrain
		$world->registerChunkLoader($this, $spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4, true);
		$world->registerChunkListener($this, $spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4);
		$this->usedChunks[World::chunkHash($spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4)] = false;

		if($namedtag === null){
			$namedtag = EntityFactory::createBaseNBT($spawn);

			$namedtag->setByte("OnGround", 1); //TODO: this hack is needed for new players in-air ticks - they don't get detected as on-ground until they move
			//TODO: old code had a TODO for SpawnForced

		}elseif($spawnReset){
			$namedtag->setTag("Pos", new ListTag([
				new DoubleTag($spawn->x),
				new DoubleTag($spawn->y),
				new DoubleTag($spawn->z)
			]));
		}

		parent::__construct($world, $namedtag);

		$ev = new PlayerLoginEvent($this, "Plugin reason");
		$ev->call();
		if($ev->isCancelled() or !$this->isConnected()){
			$this->disconnect($ev->getKickMessage());

			return;
		}

		$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn", [
			TextFormat::AQUA . $this->username . TextFormat::WHITE,
			$this->networkSession->getIp(),
			$this->networkSession->getPort(),
			$this->id,
			$this->world->getDisplayName(),
			round($this->x, 4),
			round($this->y, 4),
			round($this->z, 4)
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

		if($this->server->getForceGamemode() or !$nbt->hasTag("playerGameType", IntTag::class)){
			$this->gamemode = $this->server->getGamemode();
		}else{
			$this->gamemode = GameMode::fromMagicNumber($nbt->getInt("playerGameType") & 0x03); //TODO: bad hack here to avoid crashes on corrupted data
		}

		$this->allowFlight = $this->isCreative();
		$this->keepMovement = $this->isSpectator() || $this->allowMovementCheats();
		if($this->isOp()){
			$this->setRemoveFormat(false);
		}

		$this->setNameTagVisible();
		$this->setNameTagAlwaysVisible();
		$this->setCanClimb();

		$this->achievements = [];
		$achievements = $nbt->getCompoundTag("Achievements");
		if($achievements !== null){
			/** @var ByteTag $tag */
			foreach($achievements as $name => $tag){
				$this->achievements[$name] = $tag->getValue() !== 0;
			}
		}

		if(!$this->hasValidSpawnPosition()){
			if(($world = $this->server->getWorldManager()->getWorldByName($nbt->getString("SpawnLevel", ""))) instanceof World){
				$this->spawnPosition = new Position($nbt->getInt("SpawnX"), $nbt->getInt("SpawnY"), $nbt->getInt("SpawnZ"), $world);
			}else{
				$this->spawnPosition = $this->world->getSafeSpawn();
			}
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

	/**
	 * This might disappear in the future. Please use getUniqueId() instead.
	 * @deprecated
	 *
	 * @return int
	 */
	public function getClientId(){
		return $this->randomClientId;
	}

	public function isBanned() : bool{
		return $this->server->getNameBans()->isBanned($this->username);
	}

	public function setBanned(bool $banned) : void{
		if($banned){
			$this->server->getNameBans()->addBan($this->getName(), null, null, null);
			$this->kick("You have been banned");
		}else{
			$this->server->getNameBans()->remove($this->getName());
		}
	}

	public function isWhitelisted() : bool{
		return $this->server->isWhitelisted($this->username);
	}

	public function setWhitelisted(bool $value) : void{
		if($value){
			$this->server->addWhitelist($this->username);
		}else{
			$this->server->removeWhitelist($this->username);
		}
	}

	public function isAuthenticated() : bool{
		return $this->authenticated;
	}

	/**
	 * If the player is logged into Xbox Live, returns their Xbox user ID (XUID) as a string. Returns an empty string if
	 * the player is not logged into Xbox Live.
	 *
	 * @return string
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
	 *
	 * @return UUID
	 */
	public function getUniqueId() : UUID{
		return parent::getUniqueId();
	}

	public function getPlayer() : ?Player{
		return $this;
	}

	/**
	 * TODO: not sure this should be nullable
	 * @return int|null
	 */
	public function getFirstPlayed() : ?int{
		return $this->firstPlayed;
	}

	/**
	 * TODO: not sure this should be nullable
	 * @return int|null
	 */
	public function getLastPlayed() : ?int{
		return $this->lastPlayed;
	}

	public function hasPlayedBefore() : bool{
		return $this->lastPlayed - $this->firstPlayed > 1; // microtime(true) - microtime(true) may have less than one millisecond difference
	}

	public function setAllowFlight(bool $value){
		$this->allowFlight = $value;
		$this->networkSession->syncAdventureSettings($this);
	}

	public function getAllowFlight() : bool{
		return $this->allowFlight;
	}

	public function setFlying(bool $value){
		if($this->flying !== $value){
			$this->flying = $value;
			$this->resetFallDistance();
			$this->networkSession->syncAdventureSettings($this);
		}
	}

	public function isFlying() : bool{
		return $this->flying;
	}

	public function setAutoJump(bool $value){
		$this->autoJump = $value;
		$this->networkSession->syncAdventureSettings($this);
	}

	public function hasAutoJump() : bool{
		return $this->autoJump;
	}

	public function allowMovementCheats() : bool{
		return $this->allowMovementCheats;
	}

	public function setAllowMovementCheats(bool $value = true){
		$this->allowMovementCheats = $value;
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player) : void{
		if($this->isAlive() and $player->isAlive() and $player->getWorld() === $this->world and $player->canSee($this) and !$this->isSpectator()){
			parent::spawnTo($player);
		}
	}

	/**
	 * @return Server
	 */
	public function getServer() : Server{
		return $this->server;
	}

	/**
	 * @return bool
	 */
	public function getRemoveFormat() : bool{
		return $this->removeFormat;
	}

	/**
	 * @param bool $remove
	 */
	public function setRemoveFormat(bool $remove = true){
		$this->removeFormat = $remove;
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

	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function canSee(Player $player) : bool{
		return !isset($this->hiddenPlayers[$player->getRawUniqueId()]);
	}

	/**
	 * @param Player $player
	 */
	public function hidePlayer(Player $player){
		if($player === $this){
			return;
		}
		$this->hiddenPlayers[$player->getRawUniqueId()] = true;
		$player->despawnFrom($this);
	}

	/**
	 * @param Player $player
	 */
	public function showPlayer(Player $player){
		if($player === $this){
			return;
		}
		unset($this->hiddenPlayers[$player->getRawUniqueId()]);
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

	public function setViewDistance(int $distance){
		$this->viewDistance = $this->server->getAllowedViewDistance($distance);

		$this->spawnThreshold = (int) (min($this->viewDistance, $this->server->getProperty("chunk-sending.spawn-radius", 4)) ** 2 * M_PI);

		$this->nextChunkOrderRun = 0;

		$this->networkSession->syncViewAreaRadius($this->viewDistance);

		$this->logger->debug("Setting view distance to " . $this->viewDistance . " (requested " . $distance . ")");
	}

	/**
	 * @return bool
	 */
	public function isOnline() : bool{
		return $this->isConnected();
	}

	/**
	 * @return bool
	 */
	public function isOp() : bool{
		return $this->server->isOp($this->getName());
	}

	/**
	 * @param bool $value
	 */
	public function setOp(bool $value) : void{
		if($value === $this->isOp()){
			return;
		}

		if($value){
			$this->server->addOp($this->getName());
		}else{
			$this->server->removeOp($this->getName());
		}

		$this->networkSession->syncAdventureSettings($this);
	}

	public function recalculatePermissions() : void{
		$permManager = PermissionManager::getInstance();
		$permManager->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		$permManager->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

		if($this->perm === null){
			return;
		}

		$this->delegateRecalculatePermissions();

		if($this->spawned){
			if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
				$permManager->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			}
			if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
				$permManager->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
			}

			$this->networkSession->syncAvailableCommands();
		}
	}

	/**
	 * @return bool
	 */
	public function isConnected() : bool{
		return $this->networkSession !== null and $this->networkSession->isConnected();
	}

	/**
	 * @return NetworkSession
	 */
	public function getNetworkSession() : NetworkSession{
		return $this->networkSession;
	}

	/**
	 * Gets the username
	 * @return string
	 */
	public function getName() : string{
		return $this->username;
	}

	/**
	 * @return string
	 */
	public function getLowerCaseName() : string{
		return $this->iusername;
	}

	/**
	 * Gets the "friendly" name to display of this player to use in the chat.
	 *
	 * @return string
	 */
	public function getDisplayName() : string{
		return $this->displayName;
	}

	/**
	 * @param string $name
	 */
	public function setDisplayName(string $name){
		$this->displayName = $name;
	}

	/**
	 * Returns the player's locale, e.g. en_US.
	 * @return string
	 */
	public function getLocale() : string{
		return $this->locale;
	}

	/**
	 * Called when a player changes their skin.
	 * Plugin developers should not use this, use setSkin() and sendSkin() instead.
	 *
	 * @param Skin   $skin
	 * @param string $newSkinName
	 * @param string $oldSkinName
	 *
	 * @return bool
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
	 * @return bool
	 */
	public function isUsingItem() : bool{
		return $this->getGenericFlag(EntityMetadataFlags::ACTION) and $this->startAction > -1;
	}

	public function setUsingItem(bool $value){
		$this->startAction = $value ? $this->server->getTick() : -1;
		$this->setGenericFlag(EntityMetadataFlags::ACTION, $value);
	}

	/**
	 * Returns how long the player has been using their currently-held item for. Used for determining arrow shoot force
	 * for bows.
	 *
	 * @return int
	 */
	public function getItemUseDuration() : int{
		return $this->startAction === -1 ? -1 : ($this->server->getTick() - $this->startAction);
	}

	/**
	 * Returns whether the player has a cooldown period left before it can use the given item again.
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function hasItemCooldown(Item $item) : bool{
		$this->checkItemCooldowns();
		return isset($this->usedItemsCooldown[$item->getId()]);
	}

	/**
	 * Resets the player's cooldown time for the given item back to the maximum.
	 *
	 * @param Item $item
	 */
	public function resetItemCooldown(Item $item) : void{
		$ticks = $item->getCooldownTicks();
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

	protected function switchWorld(World $targetWorld) : bool{
		$oldWorld = $this->world;
		if(parent::switchWorld($targetWorld)){
			if($oldWorld !== null){
				foreach($this->usedChunks as $index => $d){
					World::getXZ($index, $X, $Z);
					$this->unloadChunk($X, $Z, $oldWorld);
				}
			}

			$this->usedChunks = [];
			$this->loadQueue = [];
			$this->world->sendTime($this);
			$this->world->sendDifficulty($this);

			return true;
		}

		return false;
	}

	protected function unloadChunk(int $x, int $z, ?World $world = null){
		$world = $world ?? $this->world;
		$index = World::chunkHash($x, $z);
		if(isset($this->usedChunks[$index])){
			foreach($this->getWorld()->getChunkEntities($x, $z) as $entity){
				if($entity !== $this){
					$entity->despawnFrom($this);
				}
			}
			$this->networkSession->stopUsingChunk($x, $z);
			unset($this->usedChunks[$index]);
		}
		$world->unregisterChunkLoader($this, $x, $z);
		$world->unregisterChunkListener($this, $x, $z);
		unset($this->loadQueue[$index]);
	}

	protected function spawnEntitiesOnChunk(int $chunkX, int $chunkZ) : void{
		foreach($this->world->getChunkEntities($chunkX, $chunkZ) as $entity){
			if($entity !== $this and !$entity->isFlaggedForDespawn()){
				$entity->spawnTo($this);
			}
		}
	}

	protected function requestChunks(){
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

			$this->usedChunks[$index] = false;
			$this->world->registerChunkLoader($this, $X, $Z, true);
			$this->world->registerChunkListener($this, $X, $Z);

			if(!$this->world->populateChunk($X, $Z)){
				continue;
			}

			unset($this->loadQueue[$index]);
			$this->usedChunks[$index] = true;

			$this->networkSession->startUsingChunk($X, $Z, function(int $chunkX, int $chunkZ) : void{
				if($this->spawned){
					$this->spawnEntitiesOnChunk($chunkX, $chunkZ);
				}elseif($this->spawnChunkLoadCount++ === $this->spawnThreshold){
					$this->spawned = true;

					foreach($this->usedChunks as $chunkHash => $hasSent){
						if(!$hasSent){
							continue;
						}
						World::getXZ($chunkHash, $_x, $_z);
						$this->spawnEntitiesOnChunk($_x, $_z);
					}

					$this->networkSession->onTerrainReady();
				}
			});
		}

		Timings::$playerChunkSendTimer->stopTiming();
	}

	public function doFirstSpawn(){
		if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		}
		if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
		}

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

		if($this->server->getUpdater()->hasUpdate() and $this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE) and $this->server->getProperty("auto-updater.on-update.warn-ops", true)){
			$this->server->getUpdater()->showPlayerUpdate($this);
		}

		if($this->getHealth() <= 0){
			$this->respawn();
		}
	}

	protected function selectChunks() : \Generator{
		$radius = $this->server->getAllowedViewDistance($this->viewDistance);
		$radiusSquared = $radius ** 2;

		$centerX = $this->getFloorX() >> 4;
		$centerZ = $this->getFloorZ() >> 4;

		for($x = 0; $x < $radius; ++$x){
			for($z = 0; $z <= $x; ++$z){
				if(($x ** 2 + $z ** 2) > $radiusSquared){
					break; //skip to next band
				}

				//If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

				/* Top right quadrant */
				yield World::chunkHash($centerX + $x, $centerZ + $z);
				/* Top left quadrant */
				yield World::chunkHash($centerX - $x - 1, $centerZ + $z);
				/* Bottom right quadrant */
				yield World::chunkHash($centerX + $x, $centerZ - $z - 1);
				/* Bottom left quadrant */
				yield World::chunkHash($centerX - $x - 1, $centerZ - $z - 1);

				if($x !== $z){
					/* Top right quadrant mirror */
					yield World::chunkHash($centerX + $z, $centerZ + $x);
					/* Top left quadrant mirror */
					yield World::chunkHash($centerX - $z - 1, $centerZ + $x);
					/* Bottom right quadrant mirror */
					yield World::chunkHash($centerX + $z, $centerZ - $x - 1);
					/* Bottom left quadrant mirror */
					yield World::chunkHash($centerX - $z - 1, $centerZ - $x - 1);
				}
			}
		}
	}

	protected function orderChunks() : void{
		if(!$this->isConnected() or $this->viewDistance === -1){
			return;
		}

		Timings::$playerChunkOrderTimer->startTiming();

		$newOrder = [];
		$unloadChunks = $this->usedChunks;

		foreach($this->selectChunks() as $hash){
			if(!isset($this->usedChunks[$hash]) or $this->usedChunks[$hash] === false){
				$newOrder[$hash] = true;
			}
			unset($unloadChunks[$hash]);
		}

		foreach($unloadChunks as $index => $bool){
			World::getXZ($index, $X, $Z);
			$this->unloadChunk($X, $Z);
		}

		$this->loadQueue = $newOrder;
		if(!empty($this->loadQueue) or !empty($unloadChunks)){
			$this->networkSession->syncViewAreaCenterPoint($this, $this->viewDistance);
		}

		Timings::$playerChunkOrderTimer->stopTiming();
	}

	public function doChunkRequests(){
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
		if($this->hasValidSpawnPosition()){
			return $this->spawnPosition;
		}else{
			$world = $this->server->getWorldManager()->getDefaultWorld();

			return $world->getSafeSpawn();
		}
	}

	/**
	 * @return bool
	 */
	public function hasValidSpawnPosition() : bool{
		return $this->spawnPosition !== null and $this->spawnPosition->isValid();
	}

	/**
	 * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a
	 * Position object
	 *
	 * @param Vector3|Position $pos
	 */
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Position)){
			$world = $this->world;
		}else{
			$world = $pos->getWorld();
		}
		$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $world);
		$this->networkSession->syncPlayerSpawnPoint($this->spawnPosition);
	}

	/**
	 * @return bool
	 */
	public function isSleeping() : bool{
		return $this->sleeping !== null;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return bool
	 */
	public function sleepOn(Vector3 $pos) : bool{
		$pos = $pos->floor();
		$b = $this->world->getBlock($pos);

		$ev = new PlayerBedEnterEvent($this, $b);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		if($b instanceof Bed){
			$b->setOccupied();
		}

		$this->sleeping = clone $pos;

		$this->propertyManager->setBlockPos(EntityMetadataProperties::PLAYER_BED_POSITION, $pos);
		$this->setPlayerFlag(PlayerMetadataFlags::SLEEP, true);

		$this->setSpawn($pos);

		$this->world->setSleepTicks(60);

		return true;
	}

	public function stopSleep(){
		if($this->sleeping instanceof Vector3){
			$b = $this->world->getBlock($this->sleeping);
			if($b instanceof Bed){
				$b->setOccupied(false);
			}
			(new PlayerBedLeaveEvent($this, $b))->call();

			$this->sleeping = null;
			$this->propertyManager->setBlockPos(EntityMetadataProperties::PLAYER_BED_POSITION, null);
			$this->setPlayerFlag(PlayerMetadataFlags::SLEEP, false);

			$this->world->setSleepTicks(0);

			$this->broadcastAnimation([$this], AnimatePacket::ACTION_STOP_SLEEP);
		}
	}

	/**
	 * @param string $achievementId
	 *
	 * @return bool
	 */
	public function hasAchievement(string $achievementId) : bool{
		if(!isset(Achievement::$list[$achievementId])){
			return false;
		}

		return $this->achievements[$achievementId] ?? false;
	}

	/**
	 * @param string $achievementId
	 *
	 * @return bool
	 */
	public function awardAchievement(string $achievementId) : bool{
		if(isset(Achievement::$list[$achievementId]) and !$this->hasAchievement($achievementId)){
			foreach(Achievement::$list[$achievementId]["requires"] as $requirementId){
				if(!$this->hasAchievement($requirementId)){
					return false;
				}
			}
			$ev = new PlayerAchievementAwardedEvent($this, $achievementId);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->achievements[$achievementId] = true;
				Achievement::broadcast($this, $achievementId);

				return true;
			}else{
				return false;
			}
		}

		return false;
	}

	/**
	 * @param string $achievementId
	 */
	public function removeAchievement(string $achievementId){
		if($this->hasAchievement($achievementId)){
			$this->achievements[$achievementId] = false;
		}
	}

	/**
	 * @return GameMode
	 */
	public function getGamemode() : GameMode{
		return $this->gamemode;
	}

	/**
	 * Sets the gamemode, and if needed, kicks the Player.
	 *
	 * @param GameMode $gm
	 *
	 * @return bool
	 */
	public function setGamemode(GameMode $gm) : bool{
		if($this->gamemode === $gm){
			return false;
		}

		$ev = new PlayerGameModeChangeEvent($this, $gm);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		$this->gamemode = $gm;

		$this->allowFlight = $this->isCreative();
		if($this->isSpectator()){
			$this->setFlying(true);
			$this->keepMovement = true;
			$this->despawnFromAll();
		}else{
			$this->keepMovement = $this->allowMovementCheats;
			if($this->isSurvival()){
				$this->setFlying(false);
			}
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
	 *
	 * @return bool
	 */
	public function isSurvival(bool $literal = false) : bool{
		return $this->gamemode->equals(GameMode::SURVIVAL()) or (!$literal and $this->gamemode->equals(GameMode::ADVENTURE()));
	}

	/**
	 * NOTE: Because Creative and Spectator Mode share some similar behaviour, this method will also return true if the player is
	 * in Spectator Mode. Supply the $literal parameter as true to force a literal Creative Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 *
	 * @return bool
	 */
	public function isCreative(bool $literal = false) : bool{
		return $this->gamemode->equals(GameMode::CREATIVE()) or (!$literal and $this->gamemode->equals(GameMode::SPECTATOR()));
	}

	/**
	 * NOTE: Because Adventure and Spectator Mode share some similar behaviour, this method will also return true if the player is
	 * in Spectator Mode. Supply the $literal parameter as true to force a literal Adventure Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 *
	 * @return bool
	 */
	public function isAdventure(bool $literal = false) : bool{
		return $this->gamemode->equals(GameMode::ADVENTURE()) or (!$literal and $this->gamemode->equals(GameMode::SPECTATOR()));
	}

	/**
	 * @return bool
	 */
	public function isSpectator() : bool{
		return $this->gamemode->equals(GameMode::SPECTATOR());
	}

	/**
	 * TODO: make this a dynamic ability instead of being hardcoded
	 *
	 * @return bool
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
		$bb = clone $this->boundingBox;
		$bb->minY = $this->y - 0.2;
		$bb->maxY = $this->y + 0.2;

		$this->onGround = $this->isCollided = count($this->world->getCollisionBlocks($bb, true)) > 0;
	}

	public function canBeMovedByCurrents() : bool{
		return false; //currently has no server-side movement
	}

	protected function checkNearEntities(){
		foreach($this->world->getNearbyEntities($this->boundingBox->expandedCopy(1, 0.5, 1), $this) as $entity){
			$entity->scheduleUpdate();

			if(!$entity->isAlive() or $entity->isFlaggedForDespawn()){
				continue;
			}

			$entity->onCollideWithPlayer($this);
		}
	}

	/**
	 * Returns the location that the player wants to be in at the end of this tick. Note that this may not be their
	 * actual result position at the end due to plugin interference or a range of other things.
	 *
	 * @return Vector3
	 */
	public function getNextPosition() : Vector3{
		return $this->newPosition !== null ? clone $this->newPosition : $this->asVector3();
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
		if($this->isTeleporting and $newPos->distanceSquared($this) > 1){  //Tolerate up to 1 block to avoid problems with client-sided physics when spawning in blocks
			$this->sendPosition($this, null, null, MovePlayerPacket::MODE_RESET);
			$this->logger->debug("Got outdated pre-teleport movement, received " . $newPos . ", expected " . $this->asVector3());
			//Still getting movements from before teleport, ignore them
			return false;
		}

		// Once we get a movement within a reasonable distance, treat it as a teleport ACK and remove position lock
		if($this->isTeleporting){
			$this->isTeleporting = false;
		}

		$this->newPosition = $newPos;
		return true;
	}

	public function getInAirTicks() : int{
		return $this->inAirTicks;
	}

	protected function processMovement(int $tickDiff){
		if($this->newPosition === null or $this->isSleeping()){
			return;
		}

		assert($this->x !== null and $this->y !== null and $this->z !== null);
		assert($this->newPosition->x !== null and $this->newPosition->y !== null and $this->newPosition->z !== null);

		$newPos = $this->newPosition;
		$distanceSquared = $newPos->distanceSquared($this);

		$revert = false;

		if(($distanceSquared / ($tickDiff ** 2)) > 100){
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
			$this->logger->warning("Moved too fast, reverting movement");
			$this->logger->debug("Old position: " . $this->asVector3() . ", new position: " . $this->newPosition);
			$revert = true;
		}elseif(!$this->world->isInLoadedTerrain($newPos) or !$this->world->isChunkGenerated($newPos->getFloorX() >> 4, $newPos->getFloorZ() >> 4)){
			$revert = true;
			$this->nextChunkOrderRun = 0;
		}

		if(!$revert and $distanceSquared != 0){
			$dx = $newPos->x - $this->x;
			$dy = $newPos->y - $this->y;
			$dz = $newPos->z - $this->z;

			$this->move($dx, $dy, $dz);

			$diff = $this->distanceSquared($newPos) / $tickDiff ** 2;

			if($this->isSurvival() and !$revert and $diff > 0.0625){
				$ev = new PlayerIllegalMoveEvent($this, $newPos, $this->lastLocation->asVector3());
				$ev->setCancelled($this->allowMovementCheats);

				$ev->call();

				if(!$ev->isCancelled()){
					$revert = true;
					$this->logger->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidMove", [$this->getName()]));
					$this->logger->debug("Old position: " . $this->asVector3() . ", new position: " . $this->newPosition);
				}
			}

			if($diff > 0 and !$revert){
				$this->setPosition($newPos);
			}
		}

		$from = clone $this->lastLocation;
		$to = $this->asLocation();

		$delta = $to->distanceSquared($from);
		$deltaAngle = abs($this->lastLocation->yaw - $to->yaw) + abs($this->lastLocation->pitch - $to->pitch);

		if(!$revert and ($delta > 0.0001 or $deltaAngle > 1.0)){
			$this->lastLocation = clone $to; //avoid PlayerMoveEvent modifying this

			$ev = new PlayerMoveEvent($this, $from, $to);

			$ev->call();

			if(!($revert = $ev->isCancelled())){ //Yes, this is intended
				if($to->distanceSquared($ev->getTo()) > 0.01){ //If plugins modify the destination
					$this->teleport($ev->getTo());
				}else{
					$this->broadcastMovement();

					$distance = sqrt((($from->x - $to->x) ** 2) + (($from->z - $to->z) ** 2));
					//TODO: check swimming (adds 0.015 exhaustion in MCPE)
					if($this->isSprinting()){
						$this->exhaust(0.1 * $distance, PlayerExhaustEvent::CAUSE_SPRINTING);
					}else{
						$this->exhaust(0.01 * $distance, PlayerExhaustEvent::CAUSE_WALKING);
					}
				}
			}
		}

		if($revert){
			$this->lastLocation = $from;

			$this->setPosition($from);
			$this->sendPosition($from, $from->yaw, $from->pitch, MovePlayerPacket::MODE_RESET);
		}else{
			if($distanceSquared != 0 and $this->nextChunkOrderRun > 20){
				$this->nextChunkOrderRun = 20;
			}
		}

		$this->newPosition = null;
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

		//TODO: move this to network session ticking (this is specifically related to net sync)
		$this->networkSession->syncAttributes($this);

		if(!$this->isAlive() and $this->spawned){
			$this->onDeathUpdate($tickDiff);
			return true;
		}

		$this->timings->startTiming();

		if($this->spawned){
			$this->processMovement($tickDiff);
			$this->motion->x = $this->motion->y = $this->motion->z = 0; //TODO: HACK! (Fixes player knockback being messed up)
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
		}

		$this->timings->stopTiming();

		return true;
	}

	protected function doFoodTick(int $tickDiff = 1) : void{
		if($this->isSurvival()){
			parent::doFoodTick($tickDiff);
		}
	}

	public function exhaust(float $amount, int $cause = PlayerExhaustEvent::CAUSE_CUSTOM) : float{
		if($this->isSurvival()){
			return parent::exhaust($amount, $cause);
		}

		return 0.0;
	}

	public function isHungry() : bool{
		return $this->isSurvival() and parent::isHungry();
	}

	public function canBreathe() : bool{
		return $this->isCreative() or parent::canBreathe();
	}

	protected function onEffectAdded(EffectInstance $effect, bool $replacesOldEffect) : void{
	    $this->networkSession->onEntityEffectAdded($this, $effect, $replacesOldEffect);
	}

	protected function onEffectRemoved(EffectInstance $effect) : void{
	    $this->networkSession->onEntityEffectRemoved($this, $effect);
	}

	/**
	 * Returns whether the player can interact with the specified position. This checks distance and direction.
	 *
	 * @param Vector3 $pos
	 * @param float   $maxDistance
	 * @param float   $maxDiff defaults to half of the 3D diagonal width of a block
	 *
	 * @return bool
	 */
	public function canInteract(Vector3 $pos, float $maxDistance, float $maxDiff = M_SQRT3 / 2) : bool{
		$eyePos = $this->getPosition()->add(0, $this->getEyeHeight(), 0);
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
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function chat(string $message) : bool{
		$this->doCloseInventory();

		$message = TextFormat::clean($message, $this->removeFormat);
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
					$ev = new PlayerChatEvent($this, $ev->getMessage());
					$ev->call();
					if(!$ev->isCancelled()){
						$this->server->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(), [$ev->getPlayer()->getDisplayName(), $ev->getMessage()]), $ev->getRecipients());
					}
				}
			}
		}

		return true;
	}

	public function equipItem(int $hotbarSlot) : bool{
		if(!$this->inventory->isHotbarSlot($hotbarSlot)){ //TODO: exception here?
			return false;
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

		$ev = new PlayerItemUseEvent($this, $item, $directionVector);
		if($this->hasItemCooldown($item)){
			$ev->setCancelled();
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
		if($this->hasFiniteResources()){
			$this->inventory->setItemInHand($item);
		}

		//TODO: check if item has a release action - if it doesn't, this shouldn't be set
		$this->setUsingItem(true);

		return true;
	}

	/**
	 * Consumes the currently-held item.
	 *
	 * @return bool if the consumption succeeded.
	 */
	public function consumeHeldItem() : bool{
		$slot = $this->inventory->getItemInHand();
		if($slot instanceof Consumable){
			$ev = new PlayerItemConsumeEvent($this, $slot);
			if($this->hasItemCooldown($slot)){
				$ev->setCancelled();
			}
			$ev->call();

			if($ev->isCancelled() or !$this->consumeObject($slot)){
				return false;
			}

			$this->resetItemCooldown($slot);

			if($this->hasFiniteResources()){
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

			$result = $item->onReleaseUsing($this);
			if($result->equals(ItemUseResult::SUCCESS())){
				$this->resetItemCooldown($item);
				$this->inventory->setItemInHand($item);
				return true;
			}

			return false;
		}finally{
			$this->setUsingItem(false);
		}
	}

	public function pickBlock(Vector3 $pos, bool $addTileNBT) : bool{
		$block = $this->world->getBlock($pos);
		if($block instanceof UnknownBlock){
			return true;
		}

		$item = $block->getPickedItem($addTileNBT);

		$ev = new PlayerBlockPickEvent($this, $block, $item);
		$ev->call();

		if(!$ev->isCancelled()){
			$existing = $this->inventory->first($item);
			if($existing !== -1){
				if($existing < $this->inventory->getHotbarSize()){
					$this->inventory->setHeldItemIndex($existing);
				}else{
					$this->inventory->swap($this->inventory->getHeldItemIndex(), $existing);
				}
			}elseif(!$this->hasFiniteResources()){ //TODO: plugins won't know this isn't going to execute
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
	 * @param Vector3 $pos
	 * @param int     $face
	 *
	 * @return bool if an action took place successfully
	 */
	public function attackBlock(Vector3 $pos, int $face) : bool{
		if($pos->distanceSquared($this) > 10000){
			return false; //TODO: maybe this should throw an exception instead?
		}

		$target = $this->world->getBlock($pos);

		$ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, null, $face, PlayerInteractEvent::LEFT_CLICK_BLOCK);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		if($target->onAttack($this->inventory->getItemInHand(), $face, $this)){
			return true;
		}

		$block = $target->getSide($face);
		if($block->getId() === BlockLegacyIds::FIRE){
			$this->world->setBlock($block, BlockFactory::get(BlockLegacyIds::AIR));
			return true;
		}

		if(!$this->isCreative()){
			//TODO: improve this to take stuff like swimming, ladders, enchanted tools into account, fix wrong tool break time calculations for bad tools (pmmp/PocketMine-MP#211)
			$breakTime = ceil($target->getBreakInfo()->getBreakTime($this->inventory->getItemInHand()) * 20);
			if($breakTime > 0){
				$this->world->broadcastLevelEvent($pos, LevelEventPacket::EVENT_BLOCK_START_BREAK, (int) (65535 / $breakTime));
			}
		}

		return true;
	}

	public function continueBreakBlock(Vector3 $pos, int $face) : void{
		$block = $this->world->getBlock($pos);
		$this->world->addParticle($pos, new PunchBlockParticle($block, $face));

		//TODO: destroy-progress level event
	}

	public function stopBreakBlock(Vector3 $pos) : void{
		$this->world->broadcastLevelEvent($pos, LevelEventPacket::EVENT_BLOCK_STOP_BREAK);
	}

	/**
	 * Breaks the block at the given position using the currently-held item.
	 *
	 * @param Vector3 $pos
	 *
	 * @return bool if the block was successfully broken, false if a rollback needs to take place.
	 */
	public function breakBlock(Vector3 $pos) : bool{
		$this->doCloseInventory();

		if($this->canInteract($pos->add(0.5, 0.5, 0.5), $this->isCreative() ? 13 : 7) and !$this->isSpectator()){
			$item = $this->inventory->getItemInHand();
			$oldItem = clone $item;
			if($this->world->useBreakOn($pos, $item, $this, true)){
				if($this->hasFiniteResources() and !$item->equalsExact($oldItem)){
					$this->inventory->setItemInHand($item);
				}
				$this->exhaust(0.025, PlayerExhaustEvent::CAUSE_MINING);
				return true;
			}
		}

		return false;
	}

	/**
	 * Touches the block at the given position with the currently-held item.
	 *
	 * @param Vector3 $pos
	 * @param int     $face
	 * @param Vector3 $clickOffset
	 *
	 * @return bool if it did something
	 */
	public function interactBlock(Vector3 $pos, int $face, Vector3 $clickOffset) : bool{
		$this->setUsingItem(false);

		if($this->canInteract($pos->add(0.5, 0.5, 0.5), 13) and !$this->isSpectator()){
			$item = $this->inventory->getItemInHand(); //this is a copy of the real item
			$oldItem = clone $item;
			if($this->world->useItemOn($pos, $item, $face, $clickOffset, $this, true)){
				if($this->hasFiniteResources() and !$item->equalsExact($oldItem)){
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
	 * @param Entity $entity
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

		$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $heldItem->getAttackPoints());
		if(!$this->canInteract($entity, 8) or ($entity instanceof Player and !$this->server->getConfigBool("pvp"))){
			$ev->setCancelled();
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

		if(!$this->isSprinting() and !$this->isFlying() and $this->fallDistance > 0 and !$this->hasEffect(Effect::BLINDNESS()) and !$this->isUnderwater()){
			$ev->setModifier($ev->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
		}

		$entity->attack($ev);

		if($ev->isCancelled()){
			return false;
		}

		if($ev->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0){
			$entity->broadcastAnimation(null, AnimatePacket::ACTION_CRITICAL_HIT);
		}

		foreach($meleeEnchantments as $enchantment){
			$type = $enchantment->getType();
			assert($type instanceof MeleeWeaponEnchantment);
			$type->onPostAttack($this, $entity, $enchantment->getLevel());
		}

		if($this->isAlive()){
			//reactive damage like thorns might cause us to be killed by attacking another mob, which
			//would mean we'd already have dropped the inventory by the time we reached here
			if($heldItem->onAttackEntity($entity) and $this->hasFiniteResources()){ //always fire the hook, even if we are survival
				$this->inventory->setItemInHand($heldItem);
			}

			$this->exhaust(0.3, PlayerExhaustEvent::CAUSE_ATTACK);
		}

		return true;
	}

	/**
	 * Interacts with the given entity using the currently-held item.
	 *
	 * @param Entity  $entity
	 * @param Vector3 $clickPos
	 *
	 * @return bool
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
		$ev->setCancelled(!$this->allowFlight);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		//don't use setFlying() here, to avoid feedback loops - TODO: get rid of this hack
		$this->flying = $fly;
		$this->resetFallDistance();
		return true;
	}

	public function animate(int $action) : bool{
		$ev = new PlayerAnimationEvent($this, $action);
		$ev->call();
		if($ev->isCancelled()){
			return true;
		}

		$this->broadcastAnimation($this->getViewers(), $ev->getAnimationType());
		return true;
	}

	/**
	 * Drops an item on the ground in front of the player.
	 *
	 * @param Item $item
	 */
	public function dropItem(Item $item) : void{
		$this->world->dropItem($this->add(0, 1.3, 0), $item, $this->getDirectionVector()->multiply(0.4), 40);
	}

	public function handleBookEdit(BookEditPacket $packet) : bool{
		/** @var WritableBook $oldBook */
		$oldBook = $this->inventory->getItem($packet->inventorySlot);
		if(!($oldBook instanceof WritableBook)){
			return false;
		}

		$newBook = clone $oldBook;
		$modifiedPages = [];

		switch($packet->type){
			case BookEditPacket::TYPE_REPLACE_PAGE:
				$newBook->setPageText($packet->pageNumber, $packet->text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_ADD_PAGE:
				$newBook->insertPage($packet->pageNumber, $packet->text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_DELETE_PAGE:
				$newBook->deletePage($packet->pageNumber);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_SWAP_PAGES:
				$newBook->swapPages($packet->pageNumber, $packet->secondaryPageNumber);
				$modifiedPages = [$packet->pageNumber, $packet->secondaryPageNumber];
				break;
			case BookEditPacket::TYPE_SIGN_BOOK:
				/** @var WrittenBook $newBook */
				$newBook = Item::get(Item::WRITTEN_BOOK, 0, 1, $newBook->getNamedTag());
				$newBook->setAuthor($packet->author);
				$newBook->setTitle($packet->title);
				$newBook->setGeneration(WrittenBook::GENERATION_ORIGINAL);
				break;
			default:
				return false;
		}

		$event = new PlayerEditBookEvent($this, $oldBook, $newBook, $packet->type, $modifiedPages);
		$event->call();
		if($event->isCancelled()){
			return true;
		}

		$this->getInventory()->setItem($packet->inventorySlot, $event->getNewBook());

		return true;
	}

	/**
	 * @param ClientboundPacket $packet
	 * @param bool              $immediate
	 *
	 * @return bool
	 */
	public function sendDataPacket(ClientboundPacket $packet, bool $immediate = false) : bool{
		if(!$this->isConnected()){
			return false;
		}

		return $this->networkSession->sendDataPacket($packet, $immediate);
	}

	/**
	 * @deprecated This is a proxy for sendDataPacket() and will be removed in the next major release.
	 * @see Player::sendDataPacket()
	 *
	 * @param ClientboundPacket $packet
	 *
	 * @return bool
	 */
	public function dataPacket(ClientboundPacket $packet) : bool{
		return $this->sendDataPacket($packet, false);
	}

	/**
	 * Adds a title text to the user's screen, with an optional subtitle.
	 *
	 * @param string $title
	 * @param string $subtitle
	 * @param int    $fadeIn Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int    $stay Duration in ticks to stay on screen for
	 * @param int    $fadeOut Duration in ticks for fade-out.
	 */
	public function sendTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1) : void{
		$this->setTitleDuration($fadeIn, $stay, $fadeOut);
		if($subtitle !== ""){
			$this->sendSubTitle($subtitle);
		}
		$this->sendDataPacket(SetTitlePacket::title($title));
	}

	/**
	 * Sets the subtitle message, without sending a title.
	 *
	 * @param string $subtitle
	 */
	public function sendSubTitle(string $subtitle) : void{
		$this->sendDataPacket(SetTitlePacket::subtitle($subtitle));
	}

	/**
	 * Adds small text to the user's screen.
	 *
	 * @param string $message
	 */
	public function sendActionBarMessage(string $message) : void{
		$this->sendDataPacket(SetTitlePacket::actionBarMessage($message));
	}

	/**
	 * Removes the title from the client's screen.
	 */
	public function removeTitles(){
		$this->sendDataPacket(SetTitlePacket::clearTitle());
	}

	/**
	 * Resets the title duration settings to defaults and removes any existing titles.
	 */
	public function resetTitles(){
		$this->sendDataPacket(SetTitlePacket::resetTitleOptions());
	}

	/**
	 * Sets the title duration.
	 *
	 * @param int $fadeIn Title fade-in time in ticks.
	 * @param int $stay Title stay time in ticks.
	 * @param int $fadeOut Title fade-out time in ticks.
	 */
	public function setTitleDuration(int $fadeIn, int $stay, int $fadeOut){
		if($fadeIn >= 0 and $stay >= 0 and $fadeOut >= 0){
			$this->sendDataPacket(SetTitlePacket::setAnimationTimes($fadeIn, $stay, $fadeOut));
		}
	}

	/**
	 * Sends a direct chat message to a player
	 *
	 * @param TextContainer|string $message
	 */
	public function sendMessage($message) : void{
		if($message instanceof TextContainer){
			if($message instanceof TranslationContainer){
				$this->sendTranslation($message->getText(), $message->getParameters());
				return;
			}
			$message = $message->getText();
		}

		$this->networkSession->onRawChatMessage($this->server->getLanguage()->translateString($message));
	}

	/**
	 * @param string   $message
	 * @param string[] $parameters
	 */
	public function sendTranslation(string $message, array $parameters = []){
		if(!$this->server->isLanguageForced()){
			foreach($parameters as $i => $p){
				$parameters[$i] = $this->server->getLanguage()->translateString($p, $parameters, "pocketmine.");
			}
			$this->networkSession->onTranslatedChatMessage($this->server->getLanguage()->translateString($message, $parameters, "pocketmine."), $parameters);
		}else{
			$this->sendMessage($this->server->getLanguage()->translateString($message, $parameters));
		}
	}

	/**
	 * Sends a popup message to the player
	 *
	 * TODO: add translation type popups
	 *
	 * @param string $message
	 * @param string $subtitle @deprecated
	 */
	public function sendPopup(string $message, string $subtitle = ""){
		$this->networkSession->onPopup($message);
	}

	public function sendTip(string $message){
		$this->networkSession->onTip($message);
	}

	/**
	 * Sends a Form to the player, or queue to send it if a form is already open.
	 *
	 * @param Form $form
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
	 * @param int   $formId
	 * @param mixed $responseData
	 *
	 * @return bool
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
	 * @param string               $reason
	 * @param bool                 $isAdmin
	 * @param TextContainer|string $quitMessage
	 *
	 * @return bool
	 */
	public function kick(string $reason = "", bool $isAdmin = true, $quitMessage = null) : bool{
		$ev = new PlayerKickEvent($this, $reason, $quitMessage ?? $this->getLeaveMessage());
		$ev->call();
		if(!$ev->isCancelled()){
			$reason = $ev->getReason();
			$message = $reason;
			if($isAdmin){
				if(!$this->isBanned()){
					$message = "Kicked by admin." . ($reason !== "" ? " Reason: " . $reason : "");
				}
			}else{
				if($reason === ""){
					$message = "disconnectionScreen.noReason";
				}
			}
			$this->disconnect($message, $ev->getQuitMessage());

			return true;
		}

		return false;
	}

	/**
	 * Removes the player from the server. This cannot be cancelled.
	 * This is used for remote disconnects and for uninterruptible disconnects (for example, when the server shuts down).
	 *
	 * Note for plugin developers: Prefer kick() with the isAdmin flag set to kick without the "Kicked by admin" part
	 * instead of this method. This way other plugins can have a say in whether the player is removed or not.
	 *
	 * @param string               $reason Shown to the player, usually this will appear on their disconnect screen.
	 * @param TextContainer|string $quitMessage Message to broadcast to online players (null will use default)
	 * @param bool                 $notify
	 */
	public function disconnect(string $reason, $quitMessage = null, bool $notify = true) : void{
		if(!$this->isConnected()){
			return;
		}

		$this->networkSession->onPlayerDestroyed($reason, $notify);

		//prevent the player receiving their own disconnect message
		PermissionManager::getInstance()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		PermissionManager::getInstance()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

		$ev = new PlayerQuitEvent($this, $quitMessage ?? $this->getLeaveMessage(), $reason);
		$ev->call();
		if(!empty($ev->getQuitMessage())){
			$this->server->broadcastMessage($ev->getQuitMessage());
		}
		$this->save();

		$this->spawned = false;

		$this->stopSleep();
		$this->despawnFromAll();

		$this->server->removeOnlinePlayer($this);

		foreach($this->server->getOnlinePlayers() as $player){
			if(!$player->canSee($this)){
				$player->showPlayer($this);
			}
		}
		$this->hiddenPlayers = [];

		if($this->isValid()){
			foreach($this->usedChunks as $index => $d){
				World::getXZ($index, $chunkX, $chunkZ);
				$this->unloadChunk($chunkX, $chunkZ);
			}
		}
		$this->usedChunks = [];
		$this->loadQueue = [];

		$this->removeCurrentWindow();
		foreach($this->permanentWindows as $objectId => $inventory){
			$this->closeInventoryInternal($inventory, true);
		}
		assert(empty($this->networkIdToInventoryMap));

		$this->perm->clearPermissions();

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
		parent::destroyCycles();
	}

	public function __debugInfo(){
		return [];
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
	public function save(){
		if($this->closed){
			throw new \InvalidStateException("Tried to save closed player");
		}

		$nbt = $this->saveNBT();

		if($this->isValid()){
			$nbt->setString("Level", $this->world->getFolderName());
		}

		if($this->hasValidSpawnPosition()){
			$nbt->setString("SpawnLevel", $this->spawnPosition->getWorld()->getFolderName());
			$nbt->setInt("SpawnX", $this->spawnPosition->getFloorX());
			$nbt->setInt("SpawnY", $this->spawnPosition->getFloorY());
			$nbt->setInt("SpawnZ", $this->spawnPosition->getFloorZ());

			if(!$this->isAlive()){
				//hack for respawn after quit
				$nbt->setTag("Pos", new ListTag([
					new DoubleTag($this->spawnPosition->x),
					new DoubleTag($this->spawnPosition->y),
					new DoubleTag($this->spawnPosition->z)
				]));
			}
		}

		$achievements = new CompoundTag();
		foreach($this->achievements as $achievement => $status){
			$achievements->setByte($achievement, $status ? 1 : 0);
		}
		$nbt->setTag("Achievements", $achievements);

		$nbt->setInt("playerGameType", $this->gamemode->getMagicNumber());
		$nbt->setLong("firstPlayed", $this->firstPlayed);
		$nbt->setLong("lastPlayed", (int) floor(microtime(true) * 1000));

		$this->server->saveOfflinePlayerData($this->username, $nbt);
	}

	protected function onDeath() : void{
		if(!$this->spawned){ //TODO: drop this hack
			return;
		}
		//Crafting grid must always be evacuated even if keep-inventory is true. This dumps the contents into the
		//main inventory and drops the rest on the ground.
		$this->doCloseInventory();

		$ev = new PlayerDeathEvent($this, $this->getDrops(), $this->getXpDropAmount(), null);
		$ev->call();

		if(!$ev->getKeepInventory()){
			foreach($ev->getDrops() as $item){
				$this->world->dropItem($this, $item);
			}

			if($this->inventory !== null){
				$this->inventory->setHeldItemIndex(0);
				$this->inventory->clearAll();
			}
			if($this->armorInventory !== null){
				$this->armorInventory->clearAll();
			}
		}

		$this->world->dropExperience($this, $ev->getXpDropAmount());
		$this->setXpAndProgress(0, 0);

		if($ev->getDeathMessage() != ""){
			$this->server->broadcastMessage($ev->getDeathMessage());
		}

		$this->startDeathAnimation();

		$this->networkSession->onDeath();
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		parent::onDeathUpdate($tickDiff);
		return false; //never flag players for despawn
	}

	public function respawn() : void{
		if($this->server->isHardcore()){
			$this->setBanned(true);
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

		$this->removeAllEffects();
		$this->setHealth($this->getMaxHealth());

		foreach($this->attributeMap->getAll() as $attr){
			$attr->resetToDefault();
		}

		$this->spawnToAll();
		$this->scheduleUpdate();

		$this->networkSession->onRespawn();
	}

	protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
		parent::applyPostDamageEffects($source);

		$this->exhaust(0.3, PlayerExhaustEvent::CAUSE_DAMAGE);
	}

	public function attack(EntityDamageEvent $source) : void{
		if(!$this->isAlive()){
			return;
		}

		if($this->isCreative()
			and $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
			and $source->getCause() !== EntityDamageEvent::CAUSE_VOID
		){
			$source->setCancelled();
		}elseif($this->allowFlight and $source->getCause() === EntityDamageEvent::CAUSE_FALL){
			$source->setCancelled();
		}

		parent::attack($source);
	}

	public function broadcastEntityEvent(int $eventId, ?int $eventData = null, ?array $players = null) : void{
		if($this->spawned and $players === null){
			$players = $this->getViewers();
			$players[] = $this;
		}
		parent::broadcastEntityEvent($eventId, $eventData, $players);
	}

	public function broadcastAnimation(?array $players, int $animationId) : void{
		if($this->spawned and $players === null){
			$players = $this->getViewers();
			$players[] = $this;
		}
		parent::broadcastAnimation($players, $animationId);
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		$result = parent::getOffsetPosition($vector3);
		$result->y += 0.001; //Hack for MCPE falling underground for no good reason (TODO: find out why it's doing this)
		return $result;
	}

	/**
	 * TODO: remove this
	 *
	 * @param Vector3    $pos
	 * @param float|null $yaw
	 * @param float|null $pitch
	 * @param int        $mode
	 */
	public function sendPosition(Vector3 $pos, ?float $yaw = null, ?float $pitch = null, int $mode = MovePlayerPacket::MODE_NORMAL){
		$this->networkSession->syncMovement($pos, $yaw, $pitch, $mode);

		//TODO: get rid of this
		$this->newPosition = null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if(parent::teleport($pos, $yaw, $pitch)){

			$this->removeCurrentWindow();

			$this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_TELEPORT);
			$this->broadcastMovement(true);

			$this->spawnToAll();

			$this->resetFallDistance();
			$this->nextChunkOrderRun = 0;
			$this->newPosition = null;
			$this->stopSleep();

			$this->isTeleporting = true;

			//TODO: workaround for player last pos not getting updated
			//Entity::updateMovement() normally handles this, but it's overridden with an empty function in Player
			$this->resetLastMovements();

			return true;
		}

		return false;
	}

	protected function addDefaultWindows(){
		$this->openInventoryInternal($this->getInventory(), ContainerIds::INVENTORY, true);

		$this->openInventoryInternal($this->getArmorInventory(), ContainerIds::ARMOR, true);

		$this->cursorInventory = new PlayerCursorInventory($this);
		$this->openInventoryInternal($this->cursorInventory, ContainerIds::CURSOR, true);

		$this->craftingGrid = new CraftingGrid($this, CraftingGrid::SIZE_SMALL);

		//TODO: more windows
	}

	public function getCursorInventory() : PlayerCursorInventory{
		return $this->cursorInventory;
	}

	public function getCraftingGrid() : CraftingGrid{
		return $this->craftingGrid;
	}

	/**
	 * @param CraftingGrid $grid
	 */
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
	 * @internal Called by the network session when a player closes a window.
	 *
	 * @param int $windowId
	 *
	 * @return bool
	 */
	public function doCloseWindow(int $windowId) : bool{
		$this->doCloseInventory();

		if($windowId === $this->lastInventoryNetworkId and $this->currentWindow !== null){
			$this->removeCurrentWindow();
			return true;
		}
		if($windowId === 255){
			//Closed a fake window
			return true;
		}

		$this->logger->debug("Attempted to close inventory with network ID $windowId, but current is $this->lastInventoryNetworkId");
		return false;
	}

	/**
	 * Returns the inventory the player is currently viewing. This might be a chest, furnace, or any other container.
	 *
	 * @return Inventory|null
	 */
	public function getCurrentWindow() : ?Inventory{
		return $this->currentWindow;
	}

	/**
	 * Opens an inventory window to the player. Returns if it was successful.
	 *
	 * @param Inventory $inventory
	 *
	 * @return bool
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

		$networkId = $this->lastInventoryNetworkId = max(ContainerIds::FIRST, ($this->lastInventoryNetworkId + 1) % ContainerIds::LAST);

		$this->openInventoryInternal($inventory, $networkId, false);
		$this->currentWindow = $inventory;
		return true;
	}

	public function removeCurrentWindow() : void{
		if($this->currentWindow !== null){
			(new InventoryCloseEvent($this->craftingGrid, $this))->call();
			$this->closeInventoryInternal($this->currentWindow, false);
		}
	}

	/**
	 * Returns the window ID which the inventory has for this player, or -1 if the window is not open to the player.
	 *
	 * @param Inventory $inventory
	 *
	 * @return int|null
	 */
	public function getWindowId(Inventory $inventory) : ?int{
		return ($id = array_search($inventory, $this->networkIdToInventoryMap, true)) !== false ? $id : null;
	}

	/**
	 * Returns the inventory window open to the player with the specified window ID, or null if no window is open with
	 * that ID.
	 *
	 * @param int $windowId
	 *
	 * @return Inventory|null
	 */
	public function getWindow(int $windowId) : ?Inventory{
		return $this->networkIdToInventoryMap[$windowId] ?? null;
	}

	protected function openInventoryInternal(Inventory $inventory, int $networkId, bool $permanent) : void{
		$this->logger->debug("Opening inventory " . get_class($inventory) . "#" . spl_object_id($inventory) . " with network ID $networkId");
		$this->networkIdToInventoryMap[$networkId] = $inventory;
		$inventory->onOpen($this);
		if($permanent){
			$this->permanentWindows[spl_object_id($inventory)] = $inventory;
		}
	}

	protected function closeInventoryInternal(Inventory $inventory, bool $force) : bool{
		$this->logger->debug("Closing inventory " . get_class($inventory) . "#" . spl_object_id($inventory));
		$objectId = spl_object_id($inventory);
		if($inventory === $this->currentWindow){
			$this->currentWindow = null;
		}elseif(isset($this->permanentWindows[$objectId])){
			if(!$force){
				throw new \InvalidArgumentException("Cannot remove fixed window " . get_class($inventory) . " from " . $this->getName());
			}
			unset($this->permanentWindows[$objectId]);
		}else{
			return false;
		}

		$inventory->onClose($this);
		$networkId = $this->getWindowId($inventory);
		assert($networkId !== null);
		unset($this->networkIdToInventoryMap[$networkId]);
		return true;
	}

	/**
	 * @return Inventory[]
	 */
	public function getAllWindows() : array{
		$windows = $this->permanentWindows;
		if($this->currentWindow !== null){
			$windows[] = $this->currentWindow;
		}
		return $windows;
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue) : void{
		$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
	}

	public function getMetadata(string $metadataKey){
		return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata(string $metadataKey) : bool{
		return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin) : void{
		$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
	}

	public function onChunkChanged(Chunk $chunk) : void{
		if(isset($this->usedChunks[$hash = World::chunkHash($chunk->getX(), $chunk->getZ())])){
			$this->usedChunks[$hash] = false;
			$this->nextChunkOrderRun = 0;
		}
	}

	public function onChunkLoaded(Chunk $chunk) : void{

	}

	public function onChunkPopulated(Chunk $chunk) : void{

	}

	public function onChunkUnloaded(Chunk $chunk) : void{

	}

	public function onBlockChanged(Vector3 $block) : void{

	}
}
