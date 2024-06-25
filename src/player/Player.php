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

use pocketmine\block\BaseSign;
use pocketmine\block\Bed;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\UnknownBlock;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\crafting\CraftingGrid;
use pocketmine\data\java\GameModeIdMap;
use pocketmine\entity\animation\Animation;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\animation\CriticalHitAnimation;
use pocketmine\entity\Attribute;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
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
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDisplayNameChangeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerEmoteEvent;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMissSwingEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPostChunkSendEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleGlideEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerToggleSwimEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\event\player\PlayerViewDistanceChangeEvent;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerCraftingInventory;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionBuilder;
use pocketmine\inventory\transaction\TransactionCancelledException;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\ConsumableItem;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\item\Releasable;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Language;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PlayerMetadataFlags;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissibleDelegateTrait;
use pocketmine\player\chat\StandardChatFormatter;
use pocketmine\Server;
use pocketmine\ServerProperties;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use pocketmine\world\ChunkListener;
use pocketmine\world\ChunkListenerNoOpTrait;
use pocketmine\world\ChunkLoader;
use pocketmine\world\ChunkTicker;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\sound\EntityAttackNoDamageSound;
use pocketmine\world\sound\EntityAttackSound;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\ItemBreakSound;
use pocketmine\world\sound\Sound;
use pocketmine\world\World;
use pocketmine\YmlServerProperties;
use Ramsey\Uuid\UuidInterface;
use function abs;
use function array_filter;
use function array_shift;
use function assert;
use function count;
use function explode;
use function floor;
use function get_class;
use function is_int;
use function max;
use function mb_strlen;
use function microtime;
use function min;
use function preg_match;
use function spl_object_id;
use function sqrt;
use function str_starts_with;
use function strlen;
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

	/** Max length of a chat message (UTF-8 codepoints, not bytes) */
	private const MAX_CHAT_CHAR_LENGTH = 512;
	/**
	 * Max length of a chat message in bytes. This is a theoretical maximum (if every character was 4 bytes).
	 * Since mb_strlen() is O(n), it gets very slow with large messages. Checking byte length with strlen() is O(1) and
	 * is a useful heuristic to filter out oversized messages.
	 */
	private const MAX_CHAT_BYTE_LENGTH = self::MAX_CHAT_CHAR_LENGTH * 4;
	private const MAX_REACH_DISTANCE_CREATIVE = 13;
	private const MAX_REACH_DISTANCE_SURVIVAL = 7;
	private const MAX_REACH_DISTANCE_ENTITY_INTERACTION = 8;

	public const TAG_FIRST_PLAYED = "firstPlayed"; //TAG_Long
	public const TAG_LAST_PLAYED = "lastPlayed"; //TAG_Long
	private const TAG_GAME_MODE = "playerGameType"; //TAG_Int
	private const TAG_SPAWN_WORLD = "SpawnLevel"; //TAG_String
	private const TAG_SPAWN_X = "SpawnX"; //TAG_Int
	private const TAG_SPAWN_Y = "SpawnY"; //TAG_Int
	private const TAG_SPAWN_Z = "SpawnZ"; //TAG_Int
	public const TAG_LEVEL = "Level"; //TAG_String
	public const TAG_LAST_KNOWN_XUID = "LastKnownXUID"; //TAG_String

	/**
	 * Validates the given username.
	 */
	public static function isValidUserName(?string $name) : bool{
		if($name === null){
			return false;
		}

		$lname = strtolower($name);
		$len = strlen($name);
		return $lname !== "rcon" && $lname !== "console" && $len >= 1 && $len <= 16 && preg_match("/[^A-Za-z0-9_ ]/", $name) === 0;
	}

	protected ?NetworkSession $networkSession;

	public bool $spawned = false;

	protected string $username;
	protected string $displayName;
	protected string $xuid = "";
	protected bool $authenticated;
	protected PlayerInfo $playerInfo;

	protected ?Inventory $currentWindow = null;
	/** @var Inventory[] */
	protected array $permanentWindows = [];
	protected PlayerCursorInventory $cursorInventory;
	protected PlayerCraftingInventory $craftingGrid;
	protected CreativeInventory $creativeInventory;

	protected int $messageCounter = 2;

	protected int $firstPlayed;
	protected int $lastPlayed;
	protected GameMode $gamemode;

	/**
	 * @var UsedChunkStatus[] chunkHash => status
	 * @phpstan-var array<int, UsedChunkStatus>
	 */
	protected array $usedChunks = [];
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	private array $activeChunkGenerationRequests = [];
	/**
	 * @var true[] chunkHash => dummy
	 * @phpstan-var array<int, true>
	 */
	protected array $loadQueue = [];
	protected int $nextChunkOrderRun = 5;

	/** @var true[] */
	private array $tickingChunks = [];

	protected int $viewDistance = -1;
	protected int $spawnThreshold;
	protected int $spawnChunkLoadCount = 0;
	protected int $chunksPerTick;
	protected ChunkSelector $chunkSelector;
	protected ChunkLoader $chunkLoader;
	protected ChunkTicker $chunkTicker;

	/** @var bool[] map: raw UUID (string) => bool */
	protected array $hiddenPlayers = [];

	protected float $moveRateLimit = 10 * self::MOVES_PER_TICK;
	protected ?float $lastMovementProcess = null;

	protected int $inAirTicks = 0;

	protected float $stepHeight = 0.6;

	protected ?Vector3 $sleeping = null;
	private ?Position $spawnPosition = null;

	private bool $respawnLocked = false;

	//TODO: Abilities
	protected bool $autoJump = true;
	protected bool $allowFlight = false;
	protected bool $blockCollision = true;
	protected bool $flying = false;

	/** @phpstan-var positive-int|null  */
	protected ?int $lineHeight = null;
	protected string $locale = "en_US";

	protected int $startAction = -1;
	/** @var int[] ID => ticks map */
	protected array $usedItemsCooldown = [];

	private int $lastEmoteTick = 0;

	protected int $formIdCounter = 0;
	/** @var Form[] */
	protected array $forms = [];

	protected \Logger $logger;

	protected ?SurvivalBlockBreakHandler $blockBreakHandler = null;

	public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag){
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

		$this->creativeInventory = CreativeInventory::getInstance();

		$rootPermissions = [DefaultPermissions::ROOT_USER => true];
		if($this->server->isOp($this->username)){
			$rootPermissions[DefaultPermissions::ROOT_OPERATOR] = true;
		}
		$this->perm = new PermissibleBase($rootPermissions);
		$this->chunksPerTick = $this->server->getConfigGroup()->getPropertyInt(YmlServerProperties::CHUNK_SENDING_PER_TICK, 4);
		$this->spawnThreshold = (int) (($this->server->getConfigGroup()->getPropertyInt(YmlServerProperties::CHUNK_SENDING_SPAWN_RADIUS, 4) ** 2) * M_PI);
		$this->chunkSelector = new ChunkSelector();

		$this->chunkLoader = new class implements ChunkLoader{};
		$this->chunkTicker = new ChunkTicker();
		$world = $spawnLocation->getWorld();
		//load the spawn chunk so we can see the terrain
		$xSpawnChunk = $spawnLocation->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$zSpawnChunk = $spawnLocation->getFloorZ() >> Chunk::COORD_BIT_SIZE;
		$world->registerChunkLoader($this->chunkLoader, $xSpawnChunk, $zSpawnChunk, true);
		$world->registerChunkListener($this, $xSpawnChunk, $zSpawnChunk);
		$this->usedChunks[World::chunkHash($xSpawnChunk, $zSpawnChunk)] = UsedChunkStatus::NEEDED;

		parent::__construct($spawnLocation, $this->playerInfo->getSkin(), $namedtag);
	}

	protected function initHumanData(CompoundTag $nbt) : void{
		$this->setNameTag($this->username);
	}

	private function callDummyItemHeldEvent() : void{
		$slot = $this->inventory->getHeldItemIndex();

		$event = new PlayerItemHeldEvent($this, $this->inventory->getItem($slot), $slot);
		$event->call();
		//TODO: this event is actually cancellable, but cancelling it here has no meaningful result, so we
		//just ignore it. We fire this only because the content of the held slot changed, not because the
		//held slot index changed. We can't prevent that from here, and nor would it be sensible to.
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->addDefaultWindows();

		$this->inventory->getListeners()->add(new CallbackInventoryListener(
			function(Inventory $unused, int $slot) : void{
				if($slot === $this->inventory->getHeldItemIndex()){
					$this->setUsingItem(false);

					$this->callDummyItemHeldEvent();
				}
			},
			function() : void{
				$this->setUsingItem(false);
				$this->callDummyItemHeldEvent();
			}
		));

		$this->firstPlayed = $nbt->getLong(self::TAG_FIRST_PLAYED, $now = (int) (microtime(true) * 1000));
		$this->lastPlayed = $nbt->getLong(self::TAG_LAST_PLAYED, $now);

		if(!$this->server->getForceGamemode() && ($gameModeTag = $nbt->getTag(self::TAG_GAME_MODE)) instanceof IntTag){
			$this->internalSetGameMode(GameModeIdMap::getInstance()->fromId($gameModeTag->getValue()) ?? GameMode::SURVIVAL); //TODO: bad hack here to avoid crashes on corrupted data
		}else{
			$this->internalSetGameMode($this->server->getGamemode());
		}

		$this->keepMovement = true;

		$this->setNameTagVisible();
		$this->setNameTagAlwaysVisible();
		$this->setCanClimb();

		if(($world = $this->server->getWorldManager()->getWorldByName($nbt->getString(self::TAG_SPAWN_WORLD, ""))) instanceof World){
			$this->spawnPosition = new Position($nbt->getInt(self::TAG_SPAWN_X), $nbt->getInt(self::TAG_SPAWN_Y), $nbt->getInt(self::TAG_SPAWN_Z), $world);
		}
	}

	public function getLeaveMessage() : Translatable|string{
		if($this->spawned){
			return KnownTranslationFactory::multiplayer_player_left($this->getDisplayName())->prefix(TextFormat::YELLOW);
		}

		return "";
	}

	public function isAuthenticated() : bool{
		return $this->authenticated;
	}

	/**
	 * Returns an object containing information about the player, such as their username, skin, and misc extra
	 * client-specific data.
	 */
	public function getPlayerInfo() : PlayerInfo{ return $this->playerInfo; }

	/**
	 * If the player is logged into Xbox Live, returns their Xbox user ID (XUID) as a string. Returns an empty string if
	 * the player is not logged into Xbox Live.
	 */
	public function getXuid() : string{
		return $this->xuid;
	}

	/**
	 * Returns the player's UUID. This should be the preferred method to identify a player.
	 * It does not change if the player changes their username.
	 *
	 * All players will have a UUID, regardless of whether they are logged into Xbox Live or not. However, note that
	 * non-XBL players can fake their UUIDs.
	 */
	public function getUniqueId() : UuidInterface{
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

	/**
	 * Sets whether the player is allowed to toggle flight mode.
	 *
	 * If set to false, the player will be locked in its current flight mode (flying/not flying), and attempts by the
	 * player to enter or exit flight mode will be prevented.
	 *
	 * Note: Setting this to false DOES NOT change whether the player is currently flying. Use
	 * {@link Player::setFlying()} for that purpose.
	 */
	public function setAllowFlight(bool $value) : void{
		if($this->allowFlight !== $value){
			$this->allowFlight = $value;
			$this->getNetworkSession()->syncAbilities($this);
		}
	}

	/**
	 * Returns whether the player is allowed to toggle its flight state.
	 *
	 * If false, the player is locked in its current flight mode (flying/not flying), and attempts by the player to
	 * enter or exit flight mode will be prevented.
	 */
	public function getAllowFlight() : bool{
		return $this->allowFlight;
	}

	/**
	 * Sets whether the player's movement may be obstructed by blocks with collision boxes.
	 * If set to false, the player can move through any block unobstructed.
	 *
	 * Note: Enabling flight mode in conjunction with this is recommended. A non-flying player will simply fall through
	 * the ground into the void.
	 * @see Player::setFlying()
	 */
	public function setHasBlockCollision(bool $value) : void{
		if($this->blockCollision !== $value){
			$this->blockCollision = $value;
			$this->getNetworkSession()->syncAbilities($this);
		}
	}

	/**
	 * Returns whether blocks may obstruct the player's movement.
	 * If false, the player can move through any block unobstructed.
	 */
	public function hasBlockCollision() : bool{
		return $this->blockCollision;
	}

	public function setFlying(bool $value) : void{
		if($this->flying !== $value){
			$this->flying = $value;
			$this->resetFallDistance();
			$this->getNetworkSession()->syncAbilities($this);
		}
	}

	public function isFlying() : bool{
		return $this->flying;
	}

	public function setAutoJump(bool $value) : void{
		if($this->autoJump !== $value){
			$this->autoJump = $value;
			$this->getNetworkSession()->syncAdventureSettings();
		}
	}

	public function hasAutoJump() : bool{
		return $this->autoJump;
	}

	public function spawnTo(Player $player) : void{
		if($this->isAlive() && $player->isAlive() && $player->canSee($this) && !$this->isSpectator()){
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
		if($height !== null && $height < 1){
			throw new \InvalidArgumentException("Line height must be at least 1");
		}
		$this->lineHeight = $height;
	}

	public function canSee(Player $player) : bool{
		return !isset($this->hiddenPlayers[$player->getUniqueId()->getBytes()]);
	}

	public function hidePlayer(Player $player) : void{
		if($player === $this){
			return;
		}
		$this->hiddenPlayers[$player->getUniqueId()->getBytes()] = true;
		$player->despawnFrom($this);
	}

	public function showPlayer(Player $player) : void{
		if($player === $this){
			return;
		}
		unset($this->hiddenPlayers[$player->getUniqueId()->getBytes()]);
		if($player->isOnline()){
			$player->spawnTo($this);
		}
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function canBeCollidedWith() : bool{
		return !$this->isSpectator() && parent::canBeCollidedWith();
	}

	public function resetFallDistance() : void{
		parent::resetFallDistance();
		$this->inAirTicks = 0;
	}

	public function getViewDistance() : int{
		return $this->viewDistance;
	}

	public function setViewDistance(int $distance) : void{
		$newViewDistance = $this->server->getAllowedViewDistance($distance);

		if($newViewDistance !== $this->viewDistance){
			$ev = new PlayerViewDistanceChangeEvent($this, $this->viewDistance, $newViewDistance);
			$ev->call();
		}

		$this->viewDistance = $newViewDistance;

		$this->spawnThreshold = (int) (min($this->viewDistance, $this->server->getConfigGroup()->getPropertyInt(YmlServerProperties::CHUNK_SENDING_SPAWN_RADIUS, 4)) ** 2 * M_PI);

		$this->nextChunkOrderRun = 0;

		$this->getNetworkSession()->syncViewAreaRadius($this->viewDistance);

		$this->logger->debug("Setting view distance to " . $this->viewDistance . " (requested " . $distance . ")");
	}

	public function isOnline() : bool{
		return $this->isConnected();
	}

	public function isConnected() : bool{
		return $this->networkSession !== null && $this->networkSession->isConnected();
	}

	public function getNetworkSession() : NetworkSession{
		if($this->networkSession === null){
			throw new \LogicException("Player is not connected");
		}
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

	public function canBeRenamed() : bool{
		return false;
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
		$this->networkPropertiesDirty = true;
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
		return $this->usedItemsCooldown[$item->getStateId()] ?? 0;
	}

	/**
	 * Returns whether the player has a cooldown period left before it can use the given item again.
	 */
	public function hasItemCooldown(Item $item) : bool{
		$this->checkItemCooldowns();
		return isset($this->usedItemsCooldown[$item->getStateId()]);
	}

	/**
	 * Resets the player's cooldown time for the given item back to the maximum.
	 */
	public function resetItemCooldown(Item $item, ?int $ticks = null) : void{
		$ticks = $ticks ?? $item->getCooldownTicks();
		if($ticks > 0){
			$this->usedItemsCooldown[$item->getStateId()] = $this->server->getTick() + $ticks;
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
				$this->getNetworkSession()->onEnterWorld();
			}

			return true;
		}

		return false;
	}

	protected function unloadChunk(int $x, int $z, ?World $world = null) : void{
		$world = $world ?? $this->getWorld();
		$index = World::chunkHash($x, $z);
		if(isset($this->usedChunks[$index])){
			foreach($world->getChunkEntities($x, $z) as $entity){
				if($entity !== $this){
					$entity->despawnFrom($this);
				}
			}
			$this->getNetworkSession()->stopUsingChunk($x, $z);
			unset($this->usedChunks[$index]);
			unset($this->activeChunkGenerationRequests[$index]);
		}
		$world->unregisterChunkLoader($this->chunkLoader, $x, $z);
		$world->unregisterChunkListener($this, $x, $z);
		unset($this->loadQueue[$index]);
		$world->unregisterTickingChunk($this->chunkTicker, $x, $z);
		unset($this->tickingChunks[$index]);
	}

	protected function spawnEntitiesOnAllChunks() : void{
		foreach($this->usedChunks as $chunkHash => $status){
			if($status === UsedChunkStatus::SENT){
				World::getXZ($chunkHash, $chunkX, $chunkZ);
				$this->spawnEntitiesOnChunk($chunkX, $chunkZ);
			}
		}
	}

	protected function spawnEntitiesOnChunk(int $chunkX, int $chunkZ) : void{
		foreach($this->getWorld()->getChunkEntities($chunkX, $chunkZ) as $entity){
			if($entity !== $this && !$entity->isFlaggedForDespawn()){
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

		Timings::$playerChunkSend->startTiming();

		$count = 0;
		$world = $this->getWorld();

		$limit = $this->chunksPerTick - count($this->activeChunkGenerationRequests);
		foreach($this->loadQueue as $index => $distance){
			if($count >= $limit){
				break;
			}

			$X = null;
			$Z = null;
			World::getXZ($index, $X, $Z);
			assert(is_int($X) && is_int($Z));

			++$count;

			$this->usedChunks[$index] = UsedChunkStatus::REQUESTED_GENERATION;
			$this->activeChunkGenerationRequests[$index] = true;
			unset($this->loadQueue[$index]);
			$world->registerChunkLoader($this->chunkLoader, $X, $Z, true);
			$world->registerChunkListener($this, $X, $Z);
			if(isset($this->tickingChunks[$index])){
				$world->registerTickingChunk($this->chunkTicker, $X, $Z);
			}

			$world->requestChunkPopulation($X, $Z, $this->chunkLoader)->onCompletion(
				function() use ($X, $Z, $index, $world) : void{
					if(!$this->isConnected() || !isset($this->usedChunks[$index]) || $world !== $this->getWorld()){
						return;
					}
					if($this->usedChunks[$index] !== UsedChunkStatus::REQUESTED_GENERATION){
						//We may have previously requested this, decided we didn't want it, and then decided we did want
						//it again, all before the generation request got executed. In that case, the promise would have
						//multiple callbacks for this player. In that case, only the first one matters.
						return;
					}
					unset($this->activeChunkGenerationRequests[$index]);
					$this->usedChunks[$index] = UsedChunkStatus::REQUESTED_SENDING;

					$this->getNetworkSession()->startUsingChunk($X, $Z, function() use ($X, $Z, $index) : void{
						$this->usedChunks[$index] = UsedChunkStatus::SENT;
						if($this->spawnChunkLoadCount === -1){
							$this->spawnEntitiesOnChunk($X, $Z);
						}elseif($this->spawnChunkLoadCount++ === $this->spawnThreshold){
							$this->spawnChunkLoadCount = -1;

							$this->spawnEntitiesOnAllChunks();

							$this->getNetworkSession()->notifyTerrainReady();
						}
						(new PlayerPostChunkSendEvent($this, $X, $Z))->call();
					});
				},
				static function() : void{
					//NOOP: we'll re-request this if it fails anyway
				}
			);
		}

		Timings::$playerChunkSend->stopTiming();
	}

	private function recheckBroadcastPermissions() : void{
		foreach([
			DefaultPermissionNames::BROADCAST_ADMIN => Server::BROADCAST_CHANNEL_ADMINISTRATIVE,
			DefaultPermissionNames::BROADCAST_USER => Server::BROADCAST_CHANNEL_USERS
		] as $permission => $channel){
			if($this->hasPermission($permission)){
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
			KnownTranslationFactory::multiplayer_player_joined($this->getDisplayName())->prefix(TextFormat::YELLOW)
		);
		$ev->call();
		if($ev->getJoinMessage() !== ""){
			$this->server->broadcastMessage($ev->getJoinMessage());
		}

		$this->noDamageTicks = 60;

		$this->spawnToAll();

		if($this->getHealth() <= 0){
			$this->logger->debug("Quit while dead, forcing respawn");
			$this->actuallyRespawn();
		}
	}

	/**
	 * @param true[] $oldTickingChunks
	 * @param true[] $newTickingChunks
	 *
	 * @phpstan-param array<int, true> $oldTickingChunks
	 * @phpstan-param array<int, true> $newTickingChunks
	 */
	private function updateTickingChunkRegistrations(array $oldTickingChunks, array $newTickingChunks) : void{
		$world = $this->getWorld();
		foreach($oldTickingChunks as $hash => $_){
			if(!isset($newTickingChunks[$hash]) && !isset($this->loadQueue[$hash])){
				//we are (probably) still using this chunk, but it's no longer within ticking range
				World::getXZ($hash, $tickingChunkX, $tickingChunkZ);
				$world->unregisterTickingChunk($this->chunkTicker, $tickingChunkX, $tickingChunkZ);
			}
		}
		foreach($newTickingChunks as $hash => $_){
			if(!isset($oldTickingChunks[$hash]) && !isset($this->loadQueue[$hash])){
				//we were already using this chunk, but it is now within ticking range
				World::getXZ($hash, $tickingChunkX, $tickingChunkZ);
				$world->registerTickingChunk($this->chunkTicker, $tickingChunkX, $tickingChunkZ);
			}
		}
	}

	/**
	 * Calculates which new chunks this player needs to use, and which currently-used chunks it needs to stop using.
	 * This is based on factors including the player's current render radius and current position.
	 */
	protected function orderChunks() : void{
		if(!$this->isConnected() || $this->viewDistance === -1){
			return;
		}

		Timings::$playerChunkOrder->startTiming();

		$newOrder = [];
		$tickingChunks = [];
		$unloadChunks = $this->usedChunks;

		$world = $this->getWorld();
		$tickingChunkRadius = $world->getChunkTickRadius();

		foreach($this->chunkSelector->selectChunks(
			$this->server->getAllowedViewDistance($this->viewDistance),
			$this->location->getFloorX() >> Chunk::COORD_BIT_SIZE,
			$this->location->getFloorZ() >> Chunk::COORD_BIT_SIZE
		) as $radius => $hash){
			if(!isset($this->usedChunks[$hash]) || $this->usedChunks[$hash] === UsedChunkStatus::NEEDED){
				$newOrder[$hash] = true;
			}
			if($radius < $tickingChunkRadius){
				$tickingChunks[$hash] = true;
			}
			unset($unloadChunks[$hash]);
		}

		foreach($unloadChunks as $index => $status){
			World::getXZ($index, $X, $Z);
			$this->unloadChunk($X, $Z);
		}

		$this->loadQueue = $newOrder;

		$this->updateTickingChunkRegistrations($this->tickingChunks, $tickingChunks);
		$this->tickingChunks = $tickingChunks;

		if(count($this->loadQueue) > 0 || count($unloadChunks) > 0){
			$this->getNetworkSession()->syncViewAreaCenterPoint($this->location, $this->viewDistance);
		}

		Timings::$playerChunkOrder->stopTiming();
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
	 * Returns a usage status of the given chunk, or null if the player is not using the given chunk.
	 */
	public function getUsedChunkStatus(int $chunkX, int $chunkZ) : ?UsedChunkStatus{
		return $this->usedChunks[World::chunkHash($chunkX, $chunkZ)] ?? null;
	}

	/**
	 * Returns whether the target chunk has been sent to this player.
	 */
	public function hasReceivedChunk(int $chunkX, int $chunkZ) : bool{
		$status = $this->usedChunks[World::chunkHash($chunkX, $chunkZ)] ?? null;
		return $status === UsedChunkStatus::SENT;
	}

	/**
	 * Ticks the chunk-requesting mechanism.
	 */
	public function doChunkRequests() : void{
		if($this->nextChunkOrderRun !== PHP_INT_MAX && $this->nextChunkOrderRun-- <= 0){
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

			return $world->getSpawnLocation();
		}
	}

	public function hasValidCustomSpawn() : bool{
		return $this->spawnPosition !== null && $this->spawnPosition->isValid();
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
		$this->getNetworkSession()->syncPlayerSpawnPoint($this->getSpawn());
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
		$this->networkPropertiesDirty = true;

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
			$this->networkPropertiesDirty = true;

			$this->getWorld()->setSleepTicks(0);

			$this->getNetworkSession()->sendDataPacket(AnimatePacket::create($this->getId(), AnimatePacket::ACTION_STOP_SLEEP));
		}
	}

	public function getGamemode() : GameMode{
		return $this->gamemode;
	}

	protected function internalSetGameMode(GameMode $gameMode) : void{
		$this->gamemode = $gameMode;

		$this->allowFlight = $this->gamemode === GameMode::CREATIVE;
		$this->hungerManager->setEnabled($this->isSurvival());

		if($this->isSpectator()){
			$this->setFlying(true);
			$this->setHasBlockCollision(false);
			$this->setSilent();
			$this->onGround = false;

			//TODO: HACK! this syncs the onground flag with the client so that flying works properly
			//this is a yucky hack but we don't have any other options :(
			$this->sendPosition($this->location, null, null, MovePlayerPacket::MODE_TELEPORT);
		}else{
			if($this->isSurvival()){
				$this->setFlying(false);
			}
			$this->setHasBlockCollision(true);
			$this->setSilent(false);
			$this->checkGroundState(0, 0, 0, 0, 0, 0);
		}
	}

	/**
	 * Sets the provided gamemode.
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

		$this->internalSetGameMode($gm);

		if($this->isSpectator()){
			$this->despawnFromAll();
		}else{
			$this->spawnToAll();
		}

		$this->getNetworkSession()->syncGameMode($this->gamemode);
		return true;
	}

	/**
	 * NOTE: Because Survival and Adventure Mode share some similar behaviour, this method will also return true if the player is
	 * in Adventure Mode. Supply the $literal parameter as true to force a literal Survival Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 */
	public function isSurvival(bool $literal = false) : bool{
		return $this->gamemode === GameMode::SURVIVAL || (!$literal && $this->gamemode === GameMode::ADVENTURE);
	}

	/**
	 * NOTE: Because Creative and Spectator Mode share some similar behaviour, this method will also return true if the player is
	 * in Spectator Mode. Supply the $literal parameter as true to force a literal Creative Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 */
	public function isCreative(bool $literal = false) : bool{
		return $this->gamemode === GameMode::CREATIVE || (!$literal && $this->gamemode === GameMode::SPECTATOR);
	}

	/**
	 * NOTE: Because Adventure and Spectator Mode share some similar behaviour, this method will also return true if the player is
	 * in Spectator Mode. Supply the $literal parameter as true to force a literal Adventure Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 */
	public function isAdventure(bool $literal = false) : bool{
		return $this->gamemode === GameMode::ADVENTURE || (!$literal && $this->gamemode === GameMode::SPECTATOR);
	}

	public function isSpectator() : bool{
		return $this->gamemode === GameMode::SPECTATOR;
	}

	/**
	 * TODO: make this a dynamic ability instead of being hardcoded
	 */
	public function hasFiniteResources() : bool{
		return $this->gamemode !== GameMode::CREATIVE;
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

	protected function checkGroundState(float $wantedX, float $wantedY, float $wantedZ, float $dx, float $dy, float $dz) : void{
		if($this->gamemode === GameMode::SPECTATOR){
			$this->onGround = false;
		}else{
			$bb = clone $this->boundingBox;
			$bb->minY = $this->location->y - 0.2;
			$bb->maxY = $this->location->y + 0.2;

			//we're already at the new position at this point; check if there are blocks we might have landed on between
			//the old and new positions (running down stairs necessitates this)
			$bb = $bb->addCoord(-$dx, -$dy, -$dz);

			$this->onGround = $this->isCollided = count($this->getWorld()->getCollisionBlocks($bb, true)) > 0;
		}
	}

	public function canBeMovedByCurrents() : bool{
		return false; //currently has no server-side movement
	}

	protected function checkNearEntities() : void{
		foreach($this->getWorld()->getNearbyEntities($this->boundingBox->expandedCopy(1, 0.5, 1), $this) as $entity){
			$entity->scheduleUpdate();

			if(!$entity->isAlive() || $entity->isFlaggedForDespawn()){
				continue;
			}

			$entity->onCollideWithPlayer($this);
		}
	}

	public function getInAirTicks() : int{
		return $this->inAirTicks;
	}

	/**
	 * Attempts to move the player to the given coordinates. Unless you have some particularly specialized logic, you
	 * probably want to use teleport() instead of this.
	 *
	 * This is used for processing movements sent by the player over network.
	 *
	 * @param Vector3 $newPos Coordinates of the player's feet, centered horizontally at the base of their bounding box.
	 */
	public function handleMovement(Vector3 $newPos) : void{
		Timings::$playerMove->startTiming();
		try{
			$this->actuallyHandleMovement($newPos);
		}finally{
			Timings::$playerMove->stopTiming();
		}
	}

	private function actuallyHandleMovement(Vector3 $newPos) : void{
		$this->moveRateLimit--;
		if($this->moveRateLimit < 0){
			return;
		}

		$oldPos = $this->location;
		$distanceSquared = $newPos->distanceSquared($oldPos);

		$revert = false;

		if($distanceSquared > 225){ //15 blocks
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
			$this->logger->debug("Moved too fast (" . sqrt($distanceSquared) . " blocks in 1 movement), reverting movement");
			$this->logger->debug("Old position: " . $oldPos->asVector3() . ", new position: " . $newPos);
			$revert = true;
		}elseif(!$this->getWorld()->isInLoadedTerrain($newPos)){
			$revert = true;
			$this->nextChunkOrderRun = 0;
		}

		if(!$revert && $distanceSquared != 0){
			$dx = $newPos->x - $oldPos->x;
			$dy = $newPos->y - $oldPos->y;
			$dz = $newPos->z - $oldPos->z;

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

		if($delta > 0.0001 || $deltaAngle > 1.0){
			if(PlayerMoveEvent::hasHandlers()){
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
			}

			$this->lastLocation = $to;
			$this->broadcastMovement();

			$horizontalDistanceTravelled = sqrt((($from->x - $to->x) ** 2) + (($from->z - $to->z) ** 2));
			if($horizontalDistanceTravelled > 0){
				//TODO: check for swimming
				if($this->isSprinting()){
					$this->hungerManager->exhaust(0.01 * $horizontalDistanceTravelled, PlayerExhaustEvent::CAUSE_SPRINTING);
				}else{
					$this->hungerManager->exhaust(0.0, PlayerExhaustEvent::CAUSE_WALKING);
				}

				if($this->nextChunkOrderRun > 20){
					$this->nextChunkOrderRun = 20;
				}
			}
		}

		if($exceededRateLimit){ //client and server positions will be out of sync if this happens
			$this->logger->debug("Exceeded movement rate limit, forcing to last accepted position");
			$this->sendPosition($this->location, $this->location->getYaw(), $this->location->getPitch(), MovePlayerPacket::MODE_RESET);
		}
	}

	protected function revertMovement(Location $from) : void{
		$this->setPosition($from);
		$this->sendPosition($from, $from->yaw, $from->pitch, MovePlayerPacket::MODE_RESET);
	}

	protected function calculateFallDamage(float $fallDistance) : float{
		return $this->flying ? 0 : parent::calculateFallDamage($fallDistance);
	}

	public function jump() : void{
		(new PlayerJumpEvent($this))->call();
		parent::jump();
	}

	public function setMotion(Vector3 $motion) : bool{
		if(parent::setMotion($motion)){
			$this->broadcastMotion();
			$this->getNetworkSession()->sendDataPacket(SetActorMotionPacket::create($this->id, $motion, tick: 0));

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

		if($this->justCreated){
			$this->onFirstUpdate($currentTick);
		}

		if(!$this->isAlive() && $this->spawned){
			$this->onDeathUpdate($tickDiff);
			return true;
		}

		$this->timings->startTiming();

		if($this->spawned){
			Timings::$playerMove->startTiming();
			$this->processMostRecentMovements();
			$this->motion = Vector3::zero(); //TODO: HACK! (Fixes player knockback being messed up)
			if($this->onGround){
				$this->inAirTicks = 0;
			}else{
				$this->inAirTicks += $tickDiff;
			}
			Timings::$playerMove->stopTiming();

			Timings::$entityBaseTick->startTiming();
			$this->entityBaseTick($tickDiff);
			Timings::$entityBaseTick->stopTiming();

			if($this->isCreative() && $this->fireTicks > 1){
				$this->fireTicks = 1;
			}

			if(!$this->isSpectator() && $this->isAlive()){
				Timings::$playerCheckNearEntities->startTiming();
				$this->checkNearEntities();
				Timings::$playerCheckNearEntities->stopTiming();
			}

			if($this->blockBreakHandler !== null && !$this->blockBreakHandler->update()){
				$this->blockBreakHandler = null;
			}
		}

		$this->timings->stopTiming();

		return true;
	}

	public function canBreathe() : bool{
		return $this->isCreative() || parent::canBreathe();
	}

	/**
	 * Returns whether the player can interact with the specified position. This checks distance and direction.
	 *
	 * @param float $maxDiff defaults to half of the 3D diagonal width of a block
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
		$this->removeCurrentWindow();

		if($this->messageCounter <= 0){
			//the check below would take care of this (0 * (maxlen + 1) = 0), but it's better be explicit
			return false;
		}

		//Fast length check, to make sure we don't get hung trying to explode MBs of string ...
		$maxTotalLength = $this->messageCounter * (self::MAX_CHAT_BYTE_LENGTH + 1);
		if(strlen($message) > $maxTotalLength){
			return false;
		}

		$message = TextFormat::clean($message, false);
		foreach(explode("\n", $message, $this->messageCounter + 1) as $messagePart){
			if(trim($messagePart) !== "" && strlen($messagePart) <= self::MAX_CHAT_BYTE_LENGTH && mb_strlen($messagePart, 'UTF-8') <= self::MAX_CHAT_CHAR_LENGTH && $this->messageCounter-- > 0){
				if(str_starts_with($messagePart, './')){
					$messagePart = substr($messagePart, 1);
				}

				if(str_starts_with($messagePart, "/")){
					Timings::$playerCommand->startTiming();
					$this->server->dispatchCommand($this, substr($messagePart, 1));
					Timings::$playerCommand->stopTiming();
				}else{
					$ev = new PlayerChatEvent($this, $messagePart, $this->server->getBroadcastChannelSubscribers(Server::BROADCAST_CHANNEL_USERS), new StandardChatFormatter());
					$ev->call();
					if(!$ev->isCancelled()){
						$this->server->broadcastMessage($ev->getFormatter()->format($ev->getPlayer()->getDisplayName(), $ev->getMessage()), $ev->getRecipients());
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

		$this->inventory->setHeldItemIndex($hotbarSlot);
		$this->setUsingItem(false);

		return true;
	}

	/**
	 * @param Item[] $extraReturnedItems
	 */
	private function returnItemsFromAction(Item $oldHeldItem, Item $newHeldItem, array $extraReturnedItems) : void{
		$heldItemChanged = false;

		if(!$newHeldItem->equalsExact($oldHeldItem) && $oldHeldItem->equalsExact($this->inventory->getItemInHand())){
			//determine if the item was changed in some meaningful way, or just damaged/changed count
			//if it was really changed we always need to set it, whether we have finite resources or not
			$newReplica = clone $oldHeldItem;
			$newReplica->setCount($newHeldItem->getCount());
			if($newReplica instanceof Durable && $newHeldItem instanceof Durable){
				$newReplica->setDamage($newHeldItem->getDamage());
			}
			$damagedOrDeducted = $newReplica->equalsExact($newHeldItem);

			if(!$damagedOrDeducted || $this->hasFiniteResources()){
				if($newHeldItem instanceof Durable && $newHeldItem->isBroken()){
					$this->broadcastSound(new ItemBreakSound());
				}
				$this->inventory->setItemInHand($newHeldItem);
				$heldItemChanged = true;
			}
		}

		if(!$heldItemChanged){
			$newHeldItem = $oldHeldItem;
		}

		if($heldItemChanged && count($extraReturnedItems) > 0 && $newHeldItem->isNull()){
			$this->inventory->setItemInHand(array_shift($extraReturnedItems));
		}
		foreach($this->inventory->addItem(...$extraReturnedItems) as $drop){
			//TODO: we can't generate a transaction for this since the items aren't coming from an inventory :(
			$ev = new PlayerDropItemEvent($this, $drop);
			if($this->isSpectator()){
				$ev->cancel();
			}
			$ev->call();
			if(!$ev->isCancelled()){
				$this->dropItem($drop);
			}
		}
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
		if($this->hasItemCooldown($item) || $this->isSpectator()){
			$ev->cancel();
		}

		$ev->call();

		if($ev->isCancelled()){
			return false;
		}

		$returnedItems = [];
		$result = $item->onClickAir($this, $directionVector, $returnedItems);
		if($result === ItemUseResult::FAIL){
			return false;
		}

		$this->resetItemCooldown($item);
		$this->returnItemsFromAction($oldItem, $item, $returnedItems);

		$this->setUsingItem($item instanceof Releasable && $item->canStartUsingItem($this));

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

			if($ev->isCancelled() || !$this->consumeObject($slot)){
				return false;
			}

			$this->setUsingItem(false);
			$this->resetItemCooldown($slot);

			$slot->pop();
			$this->returnItemsFromAction($oldItem, $slot, [$slot->getResidue()]);

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
			if(!$this->isUsingItem() || $this->hasItemCooldown($item)){
				return false;
			}

			$oldItem = clone $item;

			$returnedItems = [];
			$result = $item->onReleaseUsing($this, $returnedItems);
			if($result === ItemUseResult::SUCCESS){
				$this->resetItemCooldown($item);
				$this->returnItemsFromAction($oldItem, $item, $returnedItems);
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
		if($existingSlot === -1 && $this->hasFiniteResources()){
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
		if($block->hasTypeTag(BlockTypeTags::FIRE)){
			$this->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());
			$this->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new FireExtinguishSound());
			return true;
		}

		if(!$this->isCreative() && !$block->getBreakInfo()->breaksInstantly()){
			$this->blockBreakHandler = new SurvivalBlockBreakHandler($this, $pos, $target, $face, 16);
		}

		return true;
	}

	public function continueBreakBlock(Vector3 $pos, int $face) : void{
		if($this->blockBreakHandler !== null && $this->blockBreakHandler->getBlockPos()->distanceSquared($pos) < 0.0001){
			$this->blockBreakHandler->setTargetedFace($face);
		}
	}

	public function stopBreakBlock(Vector3 $pos) : void{
		if($this->blockBreakHandler !== null && $this->blockBreakHandler->getBlockPos()->distanceSquared($pos) < 0.0001){
			$this->blockBreakHandler = null;
		}
	}

	/**
	 * Breaks the block at the given position using the currently-held item.
	 *
	 * @return bool if the block was successfully broken, false if a rollback needs to take place.
	 */
	public function breakBlock(Vector3 $pos) : bool{
		$this->removeCurrentWindow();

		if($this->canInteract($pos->add(0.5, 0.5, 0.5), $this->isCreative() ? self::MAX_REACH_DISTANCE_CREATIVE : self::MAX_REACH_DISTANCE_SURVIVAL)){
			$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
			$this->stopBreakBlock($pos);
			$item = $this->inventory->getItemInHand();
			$oldItem = clone $item;
			$returnedItems = [];
			if($this->getWorld()->useBreakOn($pos, $item, $this, true, $returnedItems)){
				$this->returnItemsFromAction($oldItem, $item, $returnedItems);
				$this->hungerManager->exhaust(0.005, PlayerExhaustEvent::CAUSE_MINING);
				return true;
			}
		}else{
			$this->logger->debug("Cancelled block break at $pos due to not currently being interactable");
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

		if($this->canInteract($pos->add(0.5, 0.5, 0.5), $this->isCreative() ? self::MAX_REACH_DISTANCE_CREATIVE : self::MAX_REACH_DISTANCE_SURVIVAL)){
			$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
			$item = $this->inventory->getItemInHand(); //this is a copy of the real item
			$oldItem = clone $item;
			$returnedItems = [];
			if($this->getWorld()->useItemOn($pos, $item, $face, $clickOffset, $this, true, $returnedItems)){
				$this->returnItemsFromAction($oldItem, $item, $returnedItems);
				return true;
			}
		}else{
			$this->logger->debug("Cancelled interaction of block at $pos due to not currently being interactable");
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
		if($entity instanceof ItemEntity || $entity instanceof Arrow){
			$this->logger->debug("Attempted to attack non-attackable entity " . get_class($entity));
			return false;
		}

		$heldItem = $this->inventory->getItemInHand();
		$oldItem = clone $heldItem;

		$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $heldItem->getAttackPoints());
		if(!$this->canInteract($entity->getLocation(), self::MAX_REACH_DISTANCE_ENTITY_INTERACTION)){
			$this->logger->debug("Cancelled attack of entity " . $entity->getId() . " due to not currently being interactable");
			$ev->cancel();
		}elseif($this->isSpectator() || ($entity instanceof Player && !$this->server->getConfigGroup()->getConfigBool(ServerProperties::PVP))){
			$ev->cancel();
		}

		$meleeEnchantmentDamage = 0;
		/** @var EnchantmentInstance[] $meleeEnchantments */
		$meleeEnchantments = [];
		foreach($heldItem->getEnchantments() as $enchantment){
			$type = $enchantment->getType();
			if($type instanceof MeleeWeaponEnchantment && $type->isApplicableTo($entity)){
				$meleeEnchantmentDamage += $type->getDamageBonus($enchantment->getLevel());
				$meleeEnchantments[] = $enchantment;
			}
		}
		$ev->setModifier($meleeEnchantmentDamage, EntityDamageEvent::MODIFIER_WEAPON_ENCHANTMENTS);

		if(!$this->isSprinting() && !$this->isFlying() && $this->fallDistance > 0 && !$this->effectManager->has(VanillaEffects::BLINDNESS()) && !$this->isUnderwater()){
			$ev->setModifier($ev->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
		}

		$entity->attack($ev);
		$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());

		$soundPos = $entity->getPosition()->add(0, $entity->size->getHeight() / 2, 0);
		if($ev->isCancelled()){
			$this->getWorld()->addSound($soundPos, new EntityAttackNoDamageSound());
			return false;
		}
		$this->getWorld()->addSound($soundPos, new EntityAttackSound());

		if($ev->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0 && $entity instanceof Living){
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
			$returnedItems = [];
			$heldItem->onAttackEntity($entity, $returnedItems);
			$this->returnItemsFromAction($oldItem, $heldItem, $returnedItems);

			$this->hungerManager->exhaust(0.1, PlayerExhaustEvent::CAUSE_ATTACK);
		}

		return true;
	}

	/**
	 * Performs actions associated with the attack action (left-click) without a target entity.
	 * Under normal circumstances, this will just play the no-damage attack sound and the arm-swing animation.
	 */
	public function missSwing() : void{
		$ev = new PlayerMissSwingEvent($this);
		$ev->call();
		if(!$ev->isCancelled()){
			$this->broadcastSound(new EntityAttackNoDamageSound());
			$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
		}
	}

	/**
	 * Interacts with the given entity using the currently-held item.
	 */
	public function interactEntity(Entity $entity, Vector3 $clickPos) : bool{
		$ev = new PlayerEntityInteractEvent($this, $entity, $clickPos);

		if(!$this->canInteract($entity->getLocation(), self::MAX_REACH_DISTANCE_ENTITY_INTERACTION)){
			$this->logger->debug("Cancelled interaction with entity " . $entity->getId() . " due to not currently being interactable");
			$ev->cancel();
		}

		$ev->call();

		$item = $this->inventory->getItemInHand();
		$oldItem = clone $item;
		if(!$ev->isCancelled()){
			if($item->onInteractEntity($this, $entity, $clickPos)){
				if($this->hasFiniteResources() && !$item->equalsExact($oldItem) && $oldItem->equalsExact($this->inventory->getItemInHand())){
					if($item instanceof Durable && $item->isBroken()){
						$this->broadcastSound(new ItemBreakSound());
					}
					$this->inventory->setItemInHand($item);
				}
			}
			return $entity->onInteract($this, $clickPos);
		}
		return false;
	}

	public function toggleSprint(bool $sprint) : bool{
		if($sprint === $this->sprinting){
			return true;
		}
		$ev = new PlayerToggleSprintEvent($this, $sprint);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->setSprinting($sprint);
		return true;
	}

	public function toggleSneak(bool $sneak) : bool{
		if($sneak === $this->sneaking){
			return true;
		}
		$ev = new PlayerToggleSneakEvent($this, $sneak);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->setSneaking($sneak);
		return true;
	}

	public function toggleFlight(bool $fly) : bool{
		if($fly === $this->flying){
			return true;
		}
		$ev = new PlayerToggleFlightEvent($this, $fly);
		if(!$this->allowFlight){
			$ev->cancel();
		}
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->setFlying($fly);
		return true;
	}

	public function toggleGlide(bool $glide) : bool{
		if($glide === $this->gliding){
			return true;
		}
		$ev = new PlayerToggleGlideEvent($this, $glide);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->setGliding($glide);
		return true;
	}

	public function toggleSwim(bool $swim) : bool{
		if($swim === $this->swimming){
			return true;
		}
		$ev = new PlayerToggleSwimEvent($this, $swim);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->setSwimming($swim);
		return true;
	}

	public function emote(string $emoteId) : void{
		$currentTick = $this->server->getTick();
		if($currentTick - $this->lastEmoteTick > 5){
			$this->lastEmoteTick = $currentTick;
			$event = new PlayerEmoteEvent($this, $emoteId);
			$event->call();
			if(!$event->isCancelled()){
				$emoteId = $event->getEmoteId();
				parent::emote($emoteId);
			}
		}
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
	 * @param int $fadeIn  Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int $stay    Duration in ticks to stay on screen for
	 * @param int $fadeOut Duration in ticks for fade-out.
	 */
	public function sendTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1) : void{
		$this->setTitleDuration($fadeIn, $stay, $fadeOut);
		if($subtitle !== ""){
			$this->sendSubTitle($subtitle);
		}
		$this->getNetworkSession()->onTitle($title);
	}

	/**
	 * Sets the subtitle message, without sending a title.
	 */
	public function sendSubTitle(string $subtitle) : void{
		$this->getNetworkSession()->onSubTitle($subtitle);
	}

	/**
	 * Adds small text to the user's screen.
	 */
	public function sendActionBarMessage(string $message) : void{
		$this->getNetworkSession()->onActionBar($message);
	}

	/**
	 * Removes the title from the client's screen.
	 */
	public function removeTitles() : void{
		$this->getNetworkSession()->onClearTitle();
	}

	/**
	 * Resets the title duration settings to defaults and removes any existing titles.
	 */
	public function resetTitles() : void{
		$this->getNetworkSession()->onResetTitleOptions();
	}

	/**
	 * Sets the title duration.
	 *
	 * @param int $fadeIn  Title fade-in time in ticks.
	 * @param int $stay    Title stay time in ticks.
	 * @param int $fadeOut Title fade-out time in ticks.
	 */
	public function setTitleDuration(int $fadeIn, int $stay, int $fadeOut) : void{
		if($fadeIn >= 0 && $stay >= 0 && $fadeOut >= 0){
			$this->getNetworkSession()->onTitleDuration($fadeIn, $stay, $fadeOut);
		}
	}

	/**
	 * Sends a direct chat message to a player
	 */
	public function sendMessage(Translatable|string $message) : void{
		$this->getNetworkSession()->onChatMessage($message);
	}

	public function sendJukeboxPopup(Translatable|string $message) : void{
		$this->getNetworkSession()->onJukeboxPopup($message);
	}

	/**
	 * Sends a popup message to the player
	 *
	 * TODO: add translation type popups
	 */
	public function sendPopup(string $message) : void{
		$this->getNetworkSession()->onPopup($message);
	}

	public function sendTip(string $message) : void{
		$this->getNetworkSession()->onTip($message);
	}

	/**
	 * Sends a toast message to the player, or queue to send it if a toast message is already shown.
	 */
	public function sendToastNotification(string $title, string $body) : void{
		$this->getNetworkSession()->onToastNotification($title, $body);
	}

	/**
	 * Sends a Form to the player, or queue to send it if a form is already open.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function sendForm(Form $form) : void{
		$id = $this->formIdCounter++;
		if($this->getNetworkSession()->onFormSent($id, $form)){
			$this->forms[$id] = $form;
		}
	}

	public function onFormSubmit(int $formId, mixed $responseData) : bool{
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
	 * @param string                   $address The IP address or hostname of the destination server
	 * @param int                      $port    The destination port, defaults to 19132
	 * @param Translatable|string|null $message Message to show in the console when closing the player, null will use the default message
	 *
	 * @return bool if transfer was successful.
	 */
	public function transfer(string $address, int $port = 19132, Translatable|string|null $message = null) : bool{
		$ev = new PlayerTransferEvent($this, $address, $port, $message ?? KnownTranslationFactory::pocketmine_disconnect_transfer());
		$ev->call();
		if(!$ev->isCancelled()){
			$this->getNetworkSession()->transfer($ev->getAddress(), $ev->getPort(), $ev->getMessage());
			return true;
		}

		return false;
	}

	/**
	 * Kicks a player from the server
	 *
	 * @param Translatable|string      $reason                  Shown in the server log - this should be a short one-line message
	 * @param Translatable|string|null $quitMessage             Message to broadcast to online players (null will use default)
	 * @param Translatable|string|null $disconnectScreenMessage Shown on the player's disconnection screen (null will use the reason)
	 */
	public function kick(Translatable|string $reason = "", Translatable|string|null $quitMessage = null, Translatable|string|null $disconnectScreenMessage = null) : bool{
		$ev = new PlayerKickEvent($this, $reason, $quitMessage ?? $this->getLeaveMessage(), $disconnectScreenMessage);
		$ev->call();
		if(!$ev->isCancelled()){
			$reason = $ev->getDisconnectReason();
			if($reason === ""){
				$reason = KnownTranslationFactory::disconnectionScreen_noReason();
			}
			$disconnectScreenMessage = $ev->getDisconnectScreenMessage() ?? $reason;
			if($disconnectScreenMessage === ""){
				$disconnectScreenMessage = KnownTranslationFactory::disconnectionScreen_noReason();
			}
			$this->disconnect($reason, $ev->getQuitMessage(), $disconnectScreenMessage);

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
	 * @param Translatable|string      $reason                  Shown in the server log - this should be a short one-line message
	 * @param Translatable|string|null $quitMessage             Message to broadcast to online players (null will use default)
	 * @param Translatable|string|null $disconnectScreenMessage Shown on the player's disconnection screen (null will use the reason)
	 */
	public function disconnect(Translatable|string $reason, Translatable|string|null $quitMessage = null, Translatable|string|null $disconnectScreenMessage = null) : void{
		if(!$this->isConnected()){
			return;
		}

		$this->getNetworkSession()->onPlayerDestroyed($reason, $disconnectScreenMessage ?? $reason);
		$this->onPostDisconnect($reason, $quitMessage);
	}

	/**
	 * @internal
	 * This method executes post-disconnect actions and cleanups.
	 *
	 * @param Translatable|string      $reason      Shown in the server log - this should be a short one-line message
	 * @param Translatable|string|null $quitMessage Message to broadcast to online players (null will use default)
	 */
	public function onPostDisconnect(Translatable|string $reason, Translatable|string|null $quitMessage) : void{
		if($this->isConnected()){
			throw new \LogicException("Player is still connected");
		}

		//prevent the player receiving their own disconnect message
		$this->server->unsubscribeFromAllBroadcastChannels($this);

		$this->removeCurrentWindow();

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
		if(count($this->usedChunks) !== 0){
			throw new AssumptionFailedError("Previous loop should have cleared this array");
		}
		$this->loadQueue = [];

		$this->removeCurrentWindow();
		$this->removePermanentInventories();

		$this->perm->getPermissionRecalculationCallbacks()->clear();

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
		unset($this->cursorInventory);
		unset($this->craftingGrid);
		$this->spawnPosition = null;
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

	public function getSaveData() : CompoundTag{
		$nbt = $this->saveNBT();

		$nbt->setString(self::TAG_LAST_KNOWN_XUID, $this->xuid);

		if($this->location->isValid()){
			$nbt->setString(self::TAG_LEVEL, $this->getWorld()->getFolderName());
		}

		if($this->hasValidCustomSpawn()){
			$spawn = $this->getSpawn();
			$nbt->setString(self::TAG_SPAWN_WORLD, $spawn->getWorld()->getFolderName());
			$nbt->setInt(self::TAG_SPAWN_X, $spawn->getFloorX());
			$nbt->setInt(self::TAG_SPAWN_Y, $spawn->getFloorY());
			$nbt->setInt(self::TAG_SPAWN_Z, $spawn->getFloorZ());
		}

		$nbt->setInt(self::TAG_GAME_MODE, GameModeIdMap::getInstance()->toId($this->gamemode));
		$nbt->setLong(self::TAG_FIRST_PLAYED, $this->firstPlayed);
		$nbt->setLong(self::TAG_LAST_PLAYED, (int) floor(microtime(true) * 1000));

		return $nbt;
	}

	/**
	 * Handles player data saving
	 */
	public function save() : void{
		$this->server->saveOfflinePlayerData($this->username, $this->getSaveData());
	}

	protected function onDeath() : void{
		//Crafting grid must always be evacuated even if keep-inventory is true. This dumps the contents into the
		//main inventory and drops the rest on the ground.
		$this->removeCurrentWindow();

		$ev = new PlayerDeathEvent($this, $this->getDrops(), $this->getXpDropAmount(), null);
		$ev->call();

		if(!$ev->getKeepInventory()){
			foreach($ev->getDrops() as $item){
				$this->getWorld()->dropItem($this->location, $item);
			}

			$clearInventory = fn(Inventory $inventory) => $inventory->setContents(array_filter($inventory->getContents(), fn(Item $item) => $item->keepOnDeath()));
			$this->inventory->setHeldItemIndex(0);
			$clearInventory($this->inventory);
			$clearInventory($this->armorInventory);
			$clearInventory($this->offHandInventory);
		}

		if(!$ev->getKeepXp()){
			$this->getWorld()->dropExperience($this->location, $ev->getXpDropAmount());
			$this->xpManager->setXpAndProgress(0, 0.0);
		}

		if($ev->getDeathMessage() != ""){
			$this->server->broadcastMessage($ev->getDeathMessage());
		}

		$this->startDeathAnimation();

		$this->getNetworkSession()->onServerDeath($ev->getDeathScreenMessage());
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		parent::onDeathUpdate($tickDiff);
		return false; //never flag players for despawn
	}

	public function respawn() : void{
		if($this->server->isHardcore()){
			if($this->kick(KnownTranslationFactory::pocketmine_disconnect_ban(KnownTranslationFactory::pocketmine_disconnect_ban_hardcore()))){ //this allows plugins to prevent the ban by cancelling PlayerKickEvent
				$this->server->getNameBans()->addBan($this->getName(), "Died in hardcore mode");
			}
			return;
		}

		$this->actuallyRespawn();
	}

	protected function actuallyRespawn() : void{
		if($this->respawnLocked){
			return;
		}
		$this->respawnLocked = true;

		$this->logger->debug("Waiting for safe respawn position to be located");
		$spawn = $this->getSpawn();
		$spawn->getWorld()->requestSafeSpawn($spawn)->onCompletion(
			function(Position $safeSpawn) : void{
				if(!$this->isConnected()){
					return;
				}
				$this->logger->debug("Respawn position located, completing respawn");
				$ev = new PlayerRespawnEvent($this, $safeSpawn);
				$ev->call();

				$realSpawn = Position::fromObject($ev->getRespawnPosition()->add(0.5, 0, 0.5), $ev->getRespawnPosition()->getWorld());
				$this->teleport($realSpawn);

				$this->setSprinting(false);
				$this->setSneaking(false);
				$this->setFlying(false);

				$this->extinguish();
				$this->setAirSupplyTicks($this->getMaxAirSupplyTicks());
				$this->deadTicks = 0;
				$this->noDamageTicks = 60;

				$this->effectManager->clear();
				$this->setHealth($this->getMaxHealth());

				foreach($this->attributeMap->getAll() as $attr){
					if($attr->getId() === Attribute::EXPERIENCE || $attr->getId() === Attribute::EXPERIENCE_LEVEL){ //we have already reset both of those if needed when the player died
						continue;
					}
					$attr->resetToDefault();
				}

				$this->spawnToAll();
				$this->scheduleUpdate();

				$this->getNetworkSession()->onServerRespawn();
				$this->respawnLocked = false;
			},
			function() : void{
				if($this->isConnected()){
					$this->getNetworkSession()->disconnectWithError(KnownTranslationFactory::pocketmine_disconnect_error_respawn());
				}
			}
		);
	}

	protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
		parent::applyPostDamageEffects($source);

		$this->hungerManager->exhaust(0.1, PlayerExhaustEvent::CAUSE_DAMAGE);
	}

	public function attack(EntityDamageEvent $source) : void{
		if(!$this->isAlive()){
			return;
		}

		if($this->isCreative()
			&& $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
		){
			$source->cancel();
		}elseif($this->allowFlight && $source->getCause() === EntityDamageEvent::CAUSE_FALL){
			$source->cancel();
		}

		parent::attack($source);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::ACTION, $this->startAction > -1);
		$properties->setGenericFlag(EntityMetadataFlags::HAS_COLLISION, $this->hasBlockCollision());

		$properties->setPlayerFlag(PlayerMetadataFlags::SLEEP, $this->sleeping !== null);
		$properties->setBlockPos(EntityMetadataProperties::PLAYER_BED_POSITION, $this->sleeping !== null ? BlockPosition::fromVector3($this->sleeping) : new BlockPosition(0, 0, 0));
	}

	public function sendData(?array $targets, ?array $data = null) : void{
		if($targets === null){
			$targets = $this->getViewers();
			$targets[] = $this;
		}
		parent::sendData($targets, $data);
	}

	public function broadcastAnimation(Animation $animation, ?array $targets = null) : void{
		if($this->spawned && $targets === null){
			$targets = $this->getViewers();
			$targets[] = $this;
		}
		parent::broadcastAnimation($animation, $targets);
	}

	public function broadcastSound(Sound $sound, ?array $targets = null) : void{
		if($this->spawned && $targets === null){
			$targets = $this->getViewers();
			$targets[] = $this;
		}
		parent::broadcastSound($sound, $targets);
	}

	/**
	 * TODO: remove this
	 */
	protected function sendPosition(Vector3 $pos, ?float $yaw = null, ?float $pitch = null, int $mode = MovePlayerPacket::MODE_NORMAL) : void{
		$this->getNetworkSession()->syncMovement($pos, $yaw, $pitch, $mode);

		$this->ySize = 0;
	}

	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if(parent::teleport($pos, $yaw, $pitch)){

			$this->removeCurrentWindow();
			$this->stopSleep();

			$this->sendPosition($this->location, $this->location->yaw, $this->location->pitch, MovePlayerPacket::MODE_TELEPORT);
			$this->broadcastMovement(true);

			$this->spawnToAll();

			$this->resetFallDistance();
			$this->nextChunkOrderRun = 0;
			if($this->spawnChunkLoadCount !== -1){
				$this->spawnChunkLoadCount = 0;
			}
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
		$this->craftingGrid = new PlayerCraftingInventory($this);

		$this->addPermanentInventories($this->inventory, $this->armorInventory, $this->cursorInventory, $this->offHandInventory, $this->craftingGrid);

		//TODO: more windows
	}

	public function getCursorInventory() : PlayerCursorInventory{
		return $this->cursorInventory;
	}

	public function getCraftingGrid() : CraftingGrid{
		return $this->craftingGrid;
	}

	/**
	 * Returns the creative inventory shown to the player.
	 * Unless changed by a plugin, this is usually the same for all players.
	 */
	public function getCreativeInventory() : CreativeInventory{
		return $this->creativeInventory;
	}

	/**
	 * To set a custom creative inventory, you need to make a clone of a CreativeInventory instance.
	 */
	public function setCreativeInventory(CreativeInventory $inventory) : void{
		$this->creativeInventory = $inventory;
		if($this->spawned && $this->isConnected()){
			$this->getNetworkSession()->getInvManager()?->syncCreative();
		}
	}

	/**
	 * @internal Called to clean up crafting grid and cursor inventory when it is detected that the player closed their
	 * inventory.
	 */
	private function doCloseInventory() : void{
		$inventories = [$this->craftingGrid, $this->cursorInventory];
		if($this->currentWindow instanceof TemporaryInventory){
			$inventories[] = $this->currentWindow;
		}

		$builder = new TransactionBuilder();
		foreach($inventories as $inventory){
			$contents = $inventory->getContents();

			if(count($contents) > 0){
				$drops = $builder->getInventory($this->inventory)->addItem(...$contents);
				foreach($drops as $drop){
					$builder->addAction(new DropItemAction($drop));
				}

				$builder->getInventory($inventory)->clearAll();
			}
		}

		$actions = $builder->generateActions();
		if(count($actions) !== 0){
			$transaction = new InventoryTransaction($this, $actions);
			try{
				$transaction->execute();
				$this->logger->debug("Successfully evacuated items from temporary inventories");
			}catch(TransactionCancelledException){
				$this->logger->debug("Plugin cancelled transaction evacuating items from temporary inventories; items will be destroyed");
				foreach($inventories as $inventory){
					$inventory->clearAll();
				}
			}catch(TransactionValidationException $e){
				throw new AssumptionFailedError("This server-generated transaction should never be invalid", 0, $e);
			}
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

		$this->removeCurrentWindow();

		if(($inventoryManager = $this->getNetworkSession()->getInvManager()) === null){
			throw new \InvalidArgumentException("Player cannot open inventories in this state");
		}
		$this->logger->debug("Opening inventory " . get_class($inventory) . "#" . spl_object_id($inventory));
		$inventoryManager->onCurrentWindowChange($inventory);
		$inventory->onOpen($this);
		$this->currentWindow = $inventory;
		return true;
	}

	public function removeCurrentWindow() : void{
		$this->doCloseInventory();
		if($this->currentWindow !== null){
			$currentWindow = $this->currentWindow;
			$this->logger->debug("Closing inventory " . get_class($this->currentWindow) . "#" . spl_object_id($this->currentWindow));
			$this->currentWindow->onClose($this);
			if(($inventoryManager = $this->getNetworkSession()->getInvManager()) !== null){
				$inventoryManager->onCurrentWindowRemove();
			}
			$this->currentWindow = null;
			(new InventoryCloseEvent($currentWindow, $this))->call();
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

	/**
	 * Opens the player's sign editor GUI for the sign at the given position.
	 * TODO: add support for editing the rear side of the sign (not currently supported due to technical limitations)
	 */
	public function openSignEditor(Vector3 $position) : void{
		$block = $this->getWorld()->getBlock($position);
		if($block instanceof BaseSign){
			$this->getWorld()->setBlock($position, $block->setEditorEntityRuntimeId($this->getId()));
			$this->getNetworkSession()->onOpenSignEditor($position, true);
		}else{
			throw new \InvalidArgumentException("Block at this position is not a sign");
		}
	}

	use ChunkListenerNoOpTrait {
		onChunkChanged as private;
		onChunkUnloaded as private;
	}

	public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$status = $this->usedChunks[$hash = World::chunkHash($chunkX, $chunkZ)] ?? null;
		if($status === UsedChunkStatus::SENT){
			$this->usedChunks[$hash] = UsedChunkStatus::NEEDED;
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
