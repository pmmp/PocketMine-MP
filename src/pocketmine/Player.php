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

namespace pocketmine;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Arrow;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\Living;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\TextContainer;
use pocketmine\event\Timings;
use pocketmine\event\TranslationContainer;
use pocketmine\inventory\BaseTransaction;
use pocketmine\inventory\BigShapedRecipe;
use pocketmine\inventory\BigShapelessRecipe;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\inventory\SimpleTransactionGroup;
use pocketmine\item\Item;
use pocketmine\level\ChunkLoader;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\WeakPosition;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\AddHangingEntityPacket;
use pocketmine\network\mcpe\protocol\AddItemEntityPacket;
use pocketmine\network\mcpe\protocol\AddItemPacket;
use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\ClientToServerHandshakePacket;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\CommandStepPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\DropItemPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\network\mcpe\protocol\HurtArmorPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryActionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerFallPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\RemoveBlockPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\ReplaceItemInSlotPacket;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\RespawnPacket;
use pocketmine\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket;
use pocketmine\network\mcpe\protocol\SetCommandsEnabledPacket;
use pocketmine\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\SetHealthPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\UnknownPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\network\mcpe\protocol\UseItemPacket;
use pocketmine\network\SourceInterface;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\tile\ItemFrame;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;


/**
 * Main class that handles networking, recovery, and packet sending to the server part
 */
class Player extends Human implements CommandSender, InventoryHolder, ChunkLoader, IPlayer, NetworkSession{

	const SURVIVAL = 0;
	const CREATIVE = 1;
	const ADVENTURE = 2;
	const SPECTATOR = 3;
	const VIEW = Player::SPECTATOR;

	/**
	 * Checks a supplied username and checks it is valid.
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function isValidUserName(string $name) : bool{
		$lname = strtolower($name);
		$len = strlen($name);
		return $lname !== "rcon" and $lname !== "console" and $len >= 1 and $len <= 16 and preg_match("/[^A-Za-z0-9_]/", $name) === 0;
	}

	/**
	 * Checks the length of a supplied skin bitmap and returns whether the length is valid.
	 * @param string $skin
	 *
	 * @return bool
	 */
	public static function isValidSkin(string $skin) : bool{
		return strlen($skin) === 64 * 64 * 4 or strlen($skin) === 64 * 32 * 4;
	}

	/** @var SourceInterface */
	protected $interface;

	/** @var bool */
	public $playedBefore;
	public $spawned = false;
	public $loggedIn = false;
	public $joined = false;
	public $gamemode;
	public $lastBreak;

	protected $windowCnt = 2;
	/** @var \SplObjectStorage<Inventory> */
	protected $windows;
	/** @var Inventory[] */
	protected $windowIndex = [];

	protected $messageCounter = 2;

	private $clientSecret;

	/** @var Vector3 */
	public $speed = null;

	public $achievements = [];
	/** @var SimpleTransactionGroup */
	protected $currentTransaction = null;
	public $craftingType = 0; //0 = 2x2 crafting, 1 = 3x3 crafting, 2 = stonecutter

	public $creationTime = 0;

	protected $randomClientId;

	/** @var Vector3 */
	protected $forceMovement = null;
	/** @var Vector3 */
	protected $teleportPosition = null;
	protected $connected = true;
	protected $ip;
	protected $removeFormat = true;
	protected $port;
	protected $username;
	protected $iusername;
	protected $displayName;
	protected $startAction = -1;
	/** @var Vector3 */
	protected $sleeping = null;
	protected $clientID = null;

	private $loaderId = null;

	protected $stepHeight = 0.6;

	public $usedChunks = [];
	protected $chunkLoadCount = 0;
	protected $loadQueue = [];
	protected $nextChunkOrderRun = 5;

	/** @var Player[] */
	protected $hiddenPlayers = [];

	/** @var Vector3 */
	protected $newPosition;

	protected $viewDistance = -1;
	protected $chunksPerTick;
	protected $spawnThreshold;
	/** @var null|WeakPosition */
	private $spawnPosition = null;

	protected $inAirTicks = 0;
	protected $startAirTicks = 5;

	//TODO: Abilities
	protected $autoJump = true;
	protected $allowFlight = false;
	protected $flying = false;

	protected $allowMovementCheats = false;
	protected $allowInstaBreak = false;

	private $needACK = [];

	private $batchedPackets = [];

	/** @var PermissibleBase */
	private $perm = null;

	public function getLeaveMessage(){
		return new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.left", [
			$this->getDisplayName()
		]);
	}

	/**
	 * This might disappear in the future.
	 * Please use getUniqueId() instead (IP + clientId + name combo, in the future it'll change to real UUID for online
	 * auth)
	 *
	 * @deprecated
	 *
	 */
	public function getClientId(){
		return $this->randomClientId;
	}

	public function getClientSecret(){
		return $this->clientSecret;
	}

	public function isBanned(){
		return $this->server->getNameBans()->isBanned($this->iusername);
	}

	public function setBanned($value){
		if($value === true){
			$this->server->getNameBans()->addBan($this->getName(), null, null, null);
			$this->kick("You have been banned");
		}else{
			$this->server->getNameBans()->remove($this->getName());
		}
	}

	public function isWhitelisted(){
		return $this->server->isWhitelisted($this->iusername);
	}

	public function setWhitelisted($value){
		if($value === true){
			$this->server->addWhitelist($this->iusername);
		}else{
			$this->server->removeWhitelist($this->iusername);
		}
	}

	public function getPlayer(){
		return $this;
	}

	public function getFirstPlayed(){
		return $this->namedtag instanceof CompoundTag ? $this->namedtag["firstPlayed"] : null;
	}

	public function getLastPlayed(){
		return $this->namedtag instanceof CompoundTag ? $this->namedtag["lastPlayed"] : null;
	}

	public function hasPlayedBefore(){
		return $this->playedBefore;
	}

	public function setAllowFlight($value){
		$this->allowFlight = (bool) $value;
		$this->sendSettings();
	}

	public function getAllowFlight() : bool{
		return $this->allowFlight;
	}

	public function setFlying(bool $value){
		$this->flying = $value;
		$this->sendSettings();
	}

	public function isFlying() : bool{
		return $this->flying;
	}

	public function setAutoJump($value){
		$this->autoJump = $value;
		$this->sendSettings();
	}

	public function hasAutoJump(){
		return $this->autoJump;
	}

	public function allowMovementCheats() : bool{
		return $this->allowMovementCheats;
	}

	public function setAllowMovementCheats(bool $value = false){
		$this->allowMovementCheats = $value;
	}

	public function allowInstaBreak() : bool{
		return $this->allowInstaBreak;
	}

	public function setAllowInstaBreak(bool $value = false){
		$this->allowInstaBreak = $value;
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player){
		if($this->spawned and $player->spawned and $this->isAlive() and $player->isAlive() and $player->getLevel() === $this->level and $player->canSee($this) and !$this->isSpectator()){
			parent::spawnTo($player);
		}
	}

	/**
	 * @return Server
	 */
	public function getServer(){
		return $this->server;
	}

	/**
	 * @return bool
	 */
	public function getRemoveFormat(){
		return $this->removeFormat;
	}

	/**
	 * @param bool $remove
	 */
	public function setRemoveFormat($remove = true){
		$this->removeFormat = (bool) $remove;
	}

	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function canSee(Player $player){
		return !isset($this->hiddenPlayers[$player->getRawUniqueId()]);
	}

	/**
	 * @param Player $player
	 */
	public function hidePlayer(Player $player){
		if($player === $this){
			return;
		}
		$this->hiddenPlayers[$player->getRawUniqueId()] = $player;
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

	public function canCollideWith(Entity $entity){
		return false;
	}

	public function resetFallDistance(){
		parent::resetFallDistance();
		if($this->inAirTicks !== 0){
			$this->startAirTicks = 5;
		}
		$this->inAirTicks = 0;
	}

	public function getViewDistance() : int{
		return $this->viewDistance;
	}

	public function setViewDistance(int $distance){
		$this->viewDistance = $this->server->getAllowedViewDistance($distance);

		$this->spawnThreshold = (int) (min($this->viewDistance, $this->server->getProperty("chunk-sending.spawn-radius", 4)) ** 2 * M_PI);

		$pk = new ChunkRadiusUpdatedPacket();
		$pk->radius = $this->viewDistance;
		$this->dataPacket($pk);
	}

	/**
	 * @return bool
	 */
	public function isOnline(){
		return $this->connected === true and $this->loggedIn === true;
	}

	/**
	 * @return bool
	 */
	public function isOp(){
		return $this->server->isOp($this->getName());
	}

	/**
	 * @param bool $value
	 */
	public function setOp($value){
		if($value === $this->isOp()){
			return;
		}

		if($value === true){
			$this->server->addOp($this->getName());
		}else{
			$this->server->removeOp($this->getName());
		}

		$this->sendSettings();
	}

	/**
	 * @param permission\Permission|string $name
	 *
	 * @return bool
	 */
	public function isPermissionSet($name){
		return $this->perm->isPermissionSet($name);
	}

	/**
	 * @param permission\Permission|string $name
	 *
	 * @return bool
	 *
	 * @throws \InvalidStateException if the player is closed
	 */
	public function hasPermission($name){
		if($this->closed){
			throw new \InvalidStateException("Trying to get permissions of closed player");
		}
		return $this->perm->hasPermission($name);
	}

	/**
	 * @param Plugin $plugin
	 * @param string $name
	 * @param bool   $value
	 *
	 * @return permission\PermissionAttachment
	 */
	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		return $this->perm->addAttachment($plugin, $name, $value);
	}

	/**
	 * @param PermissionAttachment $attachment
	 */
	public function removeAttachment(PermissionAttachment $attachment){
		$this->perm->removeAttachment($attachment);
	}

	public function recalculatePermissions(){
		$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

		if($this->perm === null){
			return;
		}

		$this->perm->recalculatePermissions();

		if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
			$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		}
		if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
			$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
		}

		$this->sendCommandData();
	}

	/**
	 * @return permission\PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions(){
		return $this->perm->getEffectivePermissions();
	}

	public function sendCommandData(){
		$data = [];
		foreach($this->server->getCommandMap()->getCommands() as $command){
			if(count($cmdData = $command->generateCustomCommandData($this)) > 0){
				$data[$command->getName()]["versions"][0] = $cmdData;
			}
		}

		if(count($data) > 0){
			//TODO: structure checking
			$pk = new AvailableCommandsPacket();
			$pk->commands = json_encode($data);
			$this->dataPacket($pk);
		}
	}

	/**
	 * @param SourceInterface $interface
	 * @param null            $clientID
	 * @param string          $ip
	 * @param int             $port
	 */
	public function __construct(SourceInterface $interface, $clientID, $ip, $port){
		$this->interface = $interface;
		$this->windows = new \SplObjectStorage();
		$this->perm = new PermissibleBase($this);
		$this->namedtag = new CompoundTag();
		$this->server = Server::getInstance();
		$this->lastBreak = PHP_INT_MAX;
		$this->ip = $ip;
		$this->port = $port;
		$this->clientID = $clientID;
		$this->loaderId = Level::generateChunkLoaderId($this);
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-sending.per-tick", 4);
		$this->spawnThreshold = (int) (($this->server->getProperty("chunk-sending.spawn-radius", 4) ** 2) * M_PI);
		$this->spawnPosition = null;
		$this->gamemode = $this->server->getGamemode();
		$this->setLevel($this->server->getDefaultLevel());
		$this->newPosition = new Vector3(0, 0, 0);
		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);

		$this->uuid = null;
		$this->rawUUID = null;

		$this->creationTime = microtime(true);

		$this->allowMovementCheats = (bool) $this->server->getProperty("player.anti-cheat.allow-movement-cheats", false);
		$this->allowInstaBreak = (bool) $this->server->getProperty("player.anti-cheat.allow-instabreak", false);
	}

	/**
	 * @param string $achievementId
	 */
	public function removeAchievement($achievementId){
		if($this->hasAchievement($achievementId)){
			$this->achievements[$achievementId] = false;
		}
	}

	/**
	 * @param string $achievementId
	 *
	 * @return bool
	 */
	public function hasAchievement($achievementId){
		if(!isset(Achievement::$list[$achievementId]) or !isset($this->achievements)){
			$this->achievements = [];

			return false;
		}

		return isset($this->achievements[$achievementId]) and $this->achievements[$achievementId] != false;
	}

	/**
	 * @return bool
	 */
	public function isConnected(){
		return $this->connected === true;
	}

	/**
	 * Gets the "friendly" name to display of this player to use in the chat.
	 *
	 * @return string
	 */
	public function getDisplayName(){
		return $this->displayName;
	}

	/**
	 * @param string $name
	 */
	public function setDisplayName($name){
		$this->displayName = $name;
		if($this->spawned){
			$this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $this->getSkinId(), $this->getSkinData());
		}
	}

	public function setSkin($str, $skinId){
		parent::setSkin($str, $skinId);
		if($this->spawned){
			$this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $skinId, $str);
		}
	}

	/**
	 * Gets the player IP address
	 *
	 * @return string
	 */
	public function getAddress(){
		return $this->ip;
	}

	/**
	 * @return int
	 */
	public function getPort(){
		return $this->port;
	}

	public function getNextPosition(){
		return $this->newPosition !== null ? new Position($this->newPosition->x, $this->newPosition->y, $this->newPosition->z, $this->level) : $this->getPosition();
	}

	/**
	 * @return bool
	 */
	public function isSleeping(){
		return $this->sleeping !== null;
	}

	public function getInAirTicks(){
		return $this->inAirTicks;
	}

	protected function switchLevel(Level $targetLevel){
		$oldLevel = $this->level;
		if(parent::switchLevel($targetLevel)){
			foreach($this->usedChunks as $index => $d){
				Level::getXZ($index, $X, $Z);
				$this->unloadChunk($X, $Z, $oldLevel);
			}

			$this->usedChunks = [];
			$pk = new SetTimePacket();
			$pk->time = $this->level->getTime();
			$pk->started = $this->level->stopTime == false;
			$this->dataPacket($pk);
		}
	}

	private function unloadChunk($x, $z, Level $level = null){
		$level = $level === null ? $this->level : $level;
		$index = Level::chunkHash($x, $z);
		if(isset($this->usedChunks[$index])){
			foreach($level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this){
					$entity->despawnFrom($this);
				}
			}

			unset($this->usedChunks[$index]);
		}
		$level->unregisterChunkLoader($this, $x, $z);
		unset($this->loadQueue[$index]);
	}

	/**
	 * @return Position
	 */
	public function getSpawn(){
		if($this->hasValidSpawnPosition()){
			return $this->spawnPosition;
		}else{
			$level = $this->server->getDefaultLevel();

			return $level->getSafeSpawn();
		}
	}

	/**
	 * @return bool
	 */
	public function hasValidSpawnPosition() : bool{
		return $this->spawnPosition instanceof WeakPosition and $this->spawnPosition->isValid();
	}

	public function sendChunk($x, $z, $payload){
		if($this->connected === false){
			return;
		}

		$this->usedChunks[Level::chunkHash($x, $z)] = true;
		$this->chunkLoadCount++;

		if($payload instanceof DataPacket){
			$this->dataPacket($payload);
		}else{
			$pk = new FullChunkDataPacket();
			$pk->chunkX = $x;
			$pk->chunkZ = $z;
			$pk->data = $payload;
			$this->batchDataPacket($pk);
		}

		if($this->spawned){
			foreach($this->level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this and !$entity->closed and $entity->isAlive()){
					$entity->spawnTo($this);
				}
			}
		}
	}

	protected function sendNextChunk(){
		if($this->connected === false){
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
			Level::getXZ($index, $X, $Z);

			++$count;

			$this->usedChunks[$index] = false;
			$this->level->registerChunkLoader($this, $X, $Z, false);

			if(!$this->level->populateChunk($X, $Z)){
				if($this->spawned and $this->teleportPosition === null){
					continue;
				}else{
					break;
				}
			}

			unset($this->loadQueue[$index]);
			$this->level->requestChunk($X, $Z, $this);
		}

		if($this->chunkLoadCount >= $this->spawnThreshold and $this->spawned === false and $this->teleportPosition === null){
			$this->doFirstSpawn();
		}

		Timings::$playerChunkSendTimer->stopTiming();
	}

	protected function doFirstSpawn(){
		$this->spawned = true;

		$this->sendSettings();
		$this->sendPotionEffects($this);
		$this->sendData($this);
		$this->inventory->sendContents($this);
		$this->inventory->sendArmorContents($this);
		$this->inventory->sendHeldItem($this);

		$pk = new SetTimePacket();
		$pk->time = $this->level->getTime();
		$pk->started = $this->level->stopTime == false;
		$this->dataPacket($pk);

		$pos = $this->level->getSafeSpawn($this);

		$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $pos));

		$pos = $ev->getRespawnPosition();

		$pk = new RespawnPacket();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$this->dataPacket($pk);

		$this->sendPlayStatus(PlayStatusPacket::PLAYER_SPAWN);

		if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
			$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		}
		if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
			$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerJoinEvent($this,
			new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.joined", [
				$this->getDisplayName()
			])
		));
		if(strlen(trim($ev->getJoinMessage())) > 0){
			$this->server->broadcastMessage($ev->getJoinMessage());
		}

		$this->noDamageTicks = 60;

		foreach($this->usedChunks as $index => $c){
			Level::getXZ($index, $chunkX, $chunkZ);
			foreach($this->level->getChunkEntities($chunkX, $chunkZ) as $entity){
				if($entity !== $this and !$entity->closed and $entity->isAlive()){
					$entity->spawnTo($this);
				}
			}
		}

		$this->teleport($pos);

		$this->spawnToAll();

		if($this->server->getUpdater()->hasUpdate() and $this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
			$this->server->getUpdater()->showPlayerUpdate($this);
		}

		if($this->getHealth() <= 0){
			$pk = new RespawnPacket();
			$pos = $this->getSpawn();
			$pk->x = $pos->x;
			$pk->y = $pos->y;
			$pk->z = $pos->z;
			$this->dataPacket($pk);
		}

		$this->joined = true;
	}

	protected function orderChunks(){
		if($this->connected === false or $this->viewDistance === -1){
			return false;
		}

		Timings::$playerChunkOrderTimer->startTiming();

		$this->nextChunkOrderRun = 200;

		$radius = $this->server->getAllowedViewDistance($this->viewDistance);
		$radiusSquared = $radius ** 2;

		$newOrder = [];
		$unloadChunks = $this->usedChunks;

		$centerX = $this->x >> 4;
		$centerZ = $this->z >> 4;

		for($x = 0; $x < $radius; ++$x){
			for($z = 0; $z <= $x; ++$z){
				if(($x ** 2 + $z ** 2) > $radiusSquared){
					break; //skip to next band
				}

				//If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

				/* Top right quadrant */
				if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $x, $centerZ + $z)]) or $this->usedChunks[$index] === false){
					$newOrder[$index] = true;
				}
				unset($unloadChunks[$index]);

				/* Top left quadrant */
				if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $x - 1, $centerZ + $z)]) or $this->usedChunks[$index] === false){
					$newOrder[$index] = true;
				}
				unset($unloadChunks[$index]);

				/* Bottom right quadrant */
				if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $x, $centerZ - $z - 1)]) or $this->usedChunks[$index] === false){
					$newOrder[$index] = true;
				}
				unset($unloadChunks[$index]);


				/* Bottom left quadrant */
				if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $x - 1, $centerZ - $z - 1)]) or $this->usedChunks[$index] === false){
					$newOrder[$index] = true;
				}
				unset($unloadChunks[$index]);

				if($x !== $z){
					/* Top right quadrant mirror */
					if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $z, $centerZ + $x)]) or $this->usedChunks[$index] === false){
						$newOrder[$index] = true;
					}
					unset($unloadChunks[$index]);

					/* Top left quadrant mirror */
					if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $z - 1, $centerZ + $x)]) or $this->usedChunks[$index] === false){
						$newOrder[$index] = true;
					}
					unset($unloadChunks[$index]);

					/* Bottom right quadrant mirror */
					if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $z, $centerZ - $x - 1)]) or $this->usedChunks[$index] === false){
						$newOrder[$index] = true;
					}
					unset($unloadChunks[$index]);

					/* Bottom left quadrant mirror */
					if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $z - 1, $centerZ - $x - 1)]) or $this->usedChunks[$index] === false){
						$newOrder[$index] = true;
					}
					unset($unloadChunks[$index]);
				}
			}
		}

		foreach($unloadChunks as $index => $bool){
			Level::getXZ($index, $X, $Z);
			$this->unloadChunk($X, $Z);
		}

		$this->loadQueue = $newOrder;

		Timings::$playerChunkOrderTimer->stopTiming();

		return true;
	}

	/**
	 * Batch a Data packet into the channel list to send at the end of the tick
	 *
	 * @param DataPacket $packet
	 *
	 * @return bool
	 */
	public function batchDataPacket(DataPacket $packet){
		if($this->connected === false){
			return false;
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			$timings->stopTiming();
			return false;
		}

		$this->batchedPackets[] = clone $packet;
		$timings->stopTiming();
		return true;
	}

	/**
	 * Sends an ordered DataPacket to the send buffer
	 *
	 * @param DataPacket $packet
	 * @param bool       $needACK
	 *
	 * @return int|bool
	 */
	public function dataPacket(DataPacket $packet, $needACK = false){
		if(!$this->connected){
			return false;
		}

		//Basic safety restriction. TODO: improve this
		if(!$this->loggedIn and !$packet->canBeSentBeforeLogin()){
			throw new \InvalidArgumentException("Attempted to send " . get_class($packet) . " to " . $this->getName() . " too early");
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();

		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			$timings->stopTiming();
			return false;
		}

		$identifier = $this->interface->putPacket($this, $packet, $needACK, false);

		if($needACK and $identifier !== null){
			$this->needACK[$identifier] = false;

			$timings->stopTiming();
			return $identifier;
		}

		$timings->stopTiming();
		return true;
	}

	/**
	 * @param DataPacket $packet
	 * @param bool       $needACK
	 *
	 * @return bool|int
	 */
	public function directDataPacket(DataPacket $packet, $needACK = false){
		if($this->connected === false){
			return false;
		}

		//Basic safety restriction. TODO: improve this
		if(!$this->loggedIn and !$packet->canBeSentBeforeLogin()){
			throw new \InvalidArgumentException("Attempted to send " . get_class($packet) . " to " . $this->getName() . " too early");
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			$timings->stopTiming();
			return false;
		}

		$identifier = $this->interface->putPacket($this, $packet, $needACK, true);

		if($needACK and $identifier !== null){
			$this->needACK[$identifier] = false;

			$timings->stopTiming();
			return $identifier;
		}

		$timings->stopTiming();
		return true;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return boolean
	 */
	public function sleepOn(Vector3 $pos){
		if(!$this->isOnline()){
			return false;
		}

		foreach($this->level->getNearbyEntities($this->boundingBox->grow(2, 1, 2), $this) as $p){
			if($p instanceof Player){
				if($p->sleeping !== null and $pos->distance($p->sleeping) <= 0.1){
					return false;
				}
			}
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerBedEnterEvent($this, $this->level->getBlock($pos)));
		if($ev->isCancelled()){
			return false;
		}

		$this->sleeping = clone $pos;

		$this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [$pos->x, $pos->y, $pos->z]);
		$this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, true, self::DATA_TYPE_BYTE);

		$this->setSpawn($pos);

		$this->level->sleepTicks = 60;

		return true;
	}

	/**
	 * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a
	 * Position object
	 *
	 * @param Vector3|Position $pos
	 */
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Position)){
			$level = $this->level;
		}else{
			$level = $pos->getLevel();
		}
		$this->spawnPosition = new WeakPosition($pos->x, $pos->y, $pos->z, $level);
		$pk = new SetSpawnPositionPacket();
		$pk->x = (int) $this->spawnPosition->x;
		$pk->y = (int) $this->spawnPosition->y;
		$pk->z = (int) $this->spawnPosition->z;
		$this->dataPacket($pk);
	}

	public function stopSleep(){
		if($this->sleeping instanceof Vector3){
			$this->server->getPluginManager()->callEvent($ev = new PlayerBedLeaveEvent($this, $this->level->getBlock($this->sleeping)));

			$this->sleeping = null;
			$this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [0, 0, 0]);
			$this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, false, self::DATA_TYPE_BYTE);

			$this->level->sleepTicks = 0;

			$pk = new AnimatePacket();
			$pk->eid = $this->id;
			$pk->action = 3; //Wake up
			$this->dataPacket($pk);
		}
	}

	/**
	 * @param string $achievementId
	 *
	 * @return bool
	 */
	public function awardAchievement($achievementId){
		if(isset(Achievement::$list[$achievementId]) and !$this->hasAchievement($achievementId)){
			foreach(Achievement::$list[$achievementId]["requires"] as $requirementId){
				if(!$this->hasAchievement($requirementId)){
					return false;
				}
			}
			$this->server->getPluginManager()->callEvent($ev = new PlayerAchievementAwardedEvent($this, $achievementId));
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
	 * @return int
	 */
	public function getGamemode(){
		return $this->gamemode;
	}

	/**
	 * Sets the gamemode, and if needed, kicks the Player.
	 *
	 * @param int  $gm
	 * @param bool $client if the client made this change in their GUI
	 *
	 * @return bool
	 */
	public function setGamemode(int $gm, bool $client = false){
		if($gm < 0 or $gm > 3 or $this->gamemode === $gm){
			return false;
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerGameModeChangeEvent($this, (int) $gm));
		if($ev->isCancelled()){
			if($client){ //gamemode change by client in the GUI
				$pk = new SetPlayerGameTypePacket();
				$pk->gamemode = $this->gamemode & 0x01;
				$this->dataPacket($pk);
				$this->sendSettings();
			}
			return false;
		}

		$this->gamemode = $gm;

		$this->allowFlight = $this->isCreative();
		if($this->isSpectator()){
			$this->flying = true;
			$this->despawnFromAll();

			// Client automatically turns off flight controls when on the ground.
			// A combination of this hack and a new AdventureSettings flag FINALLY
			// fixes spectator flight controls. Thank @robske110 for this hack.
			$this->teleport($this->temporalVector->setComponents($this->x, $this->y + 0.1, $this->z));
		}else{
			if($this->isSurvival()){
				$this->flying = false;
			}
			$this->spawnToAll();
		}

		$this->resetFallDistance();

		$this->namedtag->playerGameType = new IntTag("playerGameType", $this->gamemode);
		if(!$client){ //Gamemode changed by server, do not send for client changes
			$pk = new SetPlayerGameTypePacket();
			$pk->gamemode = $this->gamemode & 0x01;
			$this->dataPacket($pk);
		}else{
			Command::broadcastCommandMessage($this, new TranslationContainer("commands.gamemode.success.self", [Server::getGamemodeString($gm)]));
		}

		$this->sendSettings();

		$this->inventory->sendContents($this);
		$this->inventory->sendContents($this->getViewers());
		$this->inventory->sendHeldItem($this->hasSpawned);
		if($this->isCreative()){
			$this->inventory->sendCreativeContents();
		}

		return true;
	}

	/**
	 * Sends all the option flags
	 */
	public function sendSettings(){
		$pk = new AdventureSettingsPacket();
		$pk->flags = 0;
		$pk->worldImmutable = $this->isSpectator();
		$pk->autoJump = $this->autoJump;
		$pk->allowFlight = $this->allowFlight;
		$pk->noClip = $this->isSpectator();
		$pk->isFlying = $this->flying;
		$pk->userPermission = ($this->isOp() ? AdventureSettingsPacket::PERMISSION_OPERATOR : AdventureSettingsPacket::PERMISSION_NORMAL);
		$this->dataPacket($pk);
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
		if($literal){
			return $this->gamemode === Player::SURVIVAL;
		}else{
			return ($this->gamemode & 0x01) === 0;
		}
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
		if($literal){
			return $this->gamemode === Player::CREATIVE;
		}else{
			return ($this->gamemode & 0x01) === 1;
		}
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
		if($literal){
			return $this->gamemode === Player::ADVENTURE;
		}else{
			return ($this->gamemode & 0x02) > 0;
		}
	}

	/**
	 * @return bool
	 */
	public function isSpectator() : bool{
		return $this->gamemode === Player::SPECTATOR;
	}

	public function isFireProof() : bool{
		return $this->isCreative();
	}

	public function getDrops(){
		if(!$this->isCreative()){
			return parent::getDrops();
		}

		return [];
	}

	protected function checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz){
		if(!$this->onGround or $movY != 0){
			$bb = clone $this->boundingBox;
			$bb->maxY = $bb->minY + 0.5;
			$bb->minY -= 1;
			if(count($this->level->getCollisionBlocks($bb, true)) > 0){
				$this->onGround = true;
			}else{
				$this->onGround = false;
			}
		}
		$this->isCollided = $this->onGround;
	}

	protected function checkBlockCollision(){
		foreach($this->getBlocksAround() as $block){
			$block->onEntityCollide($this);
		}
	}

	protected function checkNearEntities($tickDiff){
		foreach($this->level->getNearbyEntities($this->boundingBox->grow(1, 0.5, 1), $this) as $entity){
			$entity->scheduleUpdate();

			if(!$entity->isAlive()){
				continue;
			}

			if($entity instanceof Arrow and $entity->hadCollision){
				$item = Item::get(Item::ARROW, 0, 1);
				if($this->isSurvival() and !$this->inventory->canAddItem($item)){
					continue;
				}

				$this->server->getPluginManager()->callEvent($ev = new InventoryPickupArrowEvent($this->inventory, $entity));
				if($ev->isCancelled()){
					continue;
				}

				$pk = new TakeItemEntityPacket();
				$pk->eid = $this->id;
				$pk->target = $entity->getId();
				$this->server->broadcastPacket($entity->getViewers(), $pk);

				$this->inventory->addItem(clone $item);
				$entity->kill();
			}elseif($entity instanceof DroppedItem){
				if($entity->getPickupDelay() <= 0){
					$item = $entity->getItem();

					if($item instanceof Item){
						if($this->isSurvival() and !$this->inventory->canAddItem($item)){
							continue;
						}

						$this->server->getPluginManager()->callEvent($ev = new InventoryPickupItemEvent($this->inventory, $entity));
						if($ev->isCancelled()){
							continue;
						}

						switch($item->getId()){
							case Item::WOOD:
								$this->awardAchievement("mineWood");
								break;
							case Item::DIAMOND:
								$this->awardAchievement("diamond");
								break;
						}

						$pk = new TakeItemEntityPacket();
						$pk->eid = $this->id;
						$pk->target = $entity->getId();
						$this->server->broadcastPacket($entity->getViewers(), $pk);

						$this->inventory->addItem(clone $item);
						$entity->kill();
					}
				}
			}
		}
	}

	protected function processMovement($tickDiff){
		if(!$this->isAlive() or !$this->spawned or $this->newPosition === null or $this->teleportPosition !== null or $this->isSleeping()){
			return;
		}

		$newPos = $this->newPosition;
		$distanceSquared = $newPos->distanceSquared($this);

		$revert = false;

		if(($distanceSquared / ($tickDiff ** 2)) > 100 and !$this->allowMovementCheats){
			$this->server->getLogger()->warning($this->getName() . " moved too fast, reverting movement");
			$revert = true;
		}else{
			if($this->chunk === null or !$this->chunk->isGenerated()){
				$chunk = $this->level->getChunk($newPos->x >> 4, $newPos->z >> 4, false);
				if($chunk === null or !$chunk->isGenerated()){
					$revert = true;
					$this->nextChunkOrderRun = 0;
				}else{
					if($this->chunk !== null){
						$this->chunk->removeEntity($this);
					}
					$this->chunk = $chunk;
				}
			}
		}

		if(!$revert and $distanceSquared != 0){
			$dx = $newPos->x - $this->x;
			$dy = $newPos->y - $this->y;
			$dz = $newPos->z - $this->z;

			$this->move($dx, $dy, $dz);

			$diffX = $this->x - $newPos->x;
			$diffY = $this->y - $newPos->y;
			$diffZ = $this->z - $newPos->z;

			$diff = ($diffX ** 2 + $diffY ** 2 + $diffZ ** 2) / ($tickDiff ** 2);

			if($this->isSurvival() and !$revert and $diff > 0.0625){
				$ev = new PlayerIllegalMoveEvent($this, $newPos);
				$ev->setCancelled($this->allowMovementCheats);

				$this->server->getPluginManager()->callEvent($ev);

				if(!$ev->isCancelled()){
					$revert = true;
					$this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidMove", [$this->getName()]));
				}
			}

			if($diff > 0){
				$this->x = $newPos->x;
				$this->y = $newPos->y;
				$this->z = $newPos->z;
				$radius = $this->width / 2;
				$this->boundingBox->setBounds($this->x - $radius, $this->y, $this->z - $radius, $this->x + $radius, $this->y + $this->height, $this->z + $radius);
			}
		}

		$from = new Location($this->lastX, $this->lastY, $this->lastZ, $this->lastYaw, $this->lastPitch, $this->level);
		$to = $this->getLocation();

		$delta = pow($this->lastX - $to->x, 2) + pow($this->lastY - $to->y, 2) + pow($this->lastZ - $to->z, 2);
		$deltaAngle = abs($this->lastYaw - $to->yaw) + abs($this->lastPitch - $to->pitch);

		if(!$revert and ($delta > (1 / 16) or $deltaAngle > 10)){

			$isFirst = ($this->lastX === null or $this->lastY === null or $this->lastZ === null);

			$this->lastX = $to->x;
			$this->lastY = $to->y;
			$this->lastZ = $to->z;

			$this->lastYaw = $to->yaw;
			$this->lastPitch = $to->pitch;

			if(!$isFirst){
				$ev = new PlayerMoveEvent($this, $from, $to);

				$this->server->getPluginManager()->callEvent($ev);

				if(!($revert = $ev->isCancelled())){ //Yes, this is intended
					if($to->distanceSquared($ev->getTo()) > 0.01){ //If plugins modify the destination
						$this->teleport($ev->getTo());
					}else{
						$this->level->addEntityMovement($this->x >> 4, $this->z >> 4, $this->getId(), $this->x, $this->y + $this->getEyeHeight(), $this->z, $this->yaw, $this->pitch, $this->yaw);

						$distance = $from->distance($to);

						//TODO: check swimming (adds 0.015 exhaustion in MCPE)
						if($this->isSprinting()){
							$this->exhaust(0.1 * $distance, PlayerExhaustEvent::CAUSE_SPRINTING);
						}else{
							$this->exhaust(0.01 * $distance, PlayerExhaustEvent::CAUSE_WALKING);
						}
					}
				}
			}

			$this->speed = ($to->subtract($from))->divide($tickDiff);
		}elseif($distanceSquared == 0){
			$this->speed = new Vector3(0, 0, 0);
		}

		if($revert){

			$this->lastX = $from->x;
			$this->lastY = $from->y;
			$this->lastZ = $from->z;

			$this->lastYaw = $from->yaw;
			$this->lastPitch = $from->pitch;

			$this->sendPosition($from, $from->yaw, $from->pitch, MovePlayerPacket::MODE_RESET);
			$this->forceMovement = new Vector3($from->x, $from->y, $from->z);
		}else{
			$this->forceMovement = null;
			if($distanceSquared != 0 and $this->nextChunkOrderRun > 20){
				$this->nextChunkOrderRun = 20;
			}
		}

		$this->newPosition = null;
	}

	public function setMotion(Vector3 $mot){
		if(parent::setMotion($mot)){
			if($this->chunk !== null){
				$this->level->addEntityMotion($this->chunk->getX(), $this->chunk->getZ(), $this->getId(), $this->motionX, $this->motionY, $this->motionZ);
			}

			if($this->motionY > 0){
				$this->startAirTicks = (-(log($this->gravity / ($this->gravity + $this->drag * $this->motionY))) / $this->drag) * 2 + 5;
			}

			return true;
		}
		return false;
	}

	protected function updateMovement(){

	}

	public function sendAttributes(bool $sendAll = false){
		$entries = $sendAll ? $this->attributeMap->getAll() : $this->attributeMap->needSend();
		if(count($entries) > 0){
			$pk = new UpdateAttributesPacket();
			$pk->entityId = $this->id;
			$pk->entries = $entries;
			$this->dataPacket($pk);
			foreach($entries as $entry){
				$entry->markSynchronized();
			}
		}
	}

	public function onUpdate($currentTick){
		if(!$this->loggedIn){
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;

		if($tickDiff <= 0){
			return true;
		}

		$this->messageCounter = 2;

		$this->lastUpdate = $currentTick;

		$this->sendAttributes();

		if(!$this->isAlive() and $this->spawned){
			++$this->deadTicks;
			if($this->deadTicks >= 10){
				$this->despawnFromAll();
			}
			return true;
		}

		$this->timings->startTiming();

		if($this->spawned){
			$this->processMovement($tickDiff);
			$this->entityBaseTick($tickDiff);

			if(!$this->isSpectator()){
				$this->checkNearEntities($tickDiff);

				if($this->speed !== null){
					if($this->onGround){
						if($this->inAirTicks !== 0){
							$this->startAirTicks = 5;
						}
						$this->inAirTicks = 0;
					}else{
						if(!$this->allowFlight and $this->inAirTicks > 10 and !$this->isSleeping() and !$this->isImmobile()){
							$expectedVelocity = (-$this->gravity) / $this->drag - ((-$this->gravity) / $this->drag) * exp(-$this->drag * ($this->inAirTicks - $this->startAirTicks));
							$diff = ($this->speed->y - $expectedVelocity) ** 2;

							if(!$this->hasEffect(Effect::JUMP) and $diff > 0.6 and $expectedVelocity < $this->speed->y and !$this->server->getAllowFlight()){
								if($this->inAirTicks < 100){
									$this->setMotion(new Vector3(0, $expectedVelocity, 0));
								}elseif($this->kick("Flying is not enabled on this server")){
									$this->timings->stopTiming();

									return false;
								}
							}
						}

						++$this->inAirTicks;
					}
				}
			}
		}

		$this->checkTeleportPosition();

		$this->timings->stopTiming();

		return true;
	}

	public function doFoodTick(int $tickDiff = 1){
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

	public function checkNetwork(){
		if(!$this->isOnline()){
			return;
		}

		if($this->nextChunkOrderRun-- <= 0 or $this->chunk === null){
			$this->orderChunks();
		}

		if(count($this->loadQueue) > 0 or !$this->spawned){
			$this->sendNextChunk();
		}

		if(count($this->batchedPackets) > 0){
			$this->server->batchPackets([$this], $this->batchedPackets, false);
			$this->batchedPackets = [];
		}
	}

	public function canInteract(Vector3 $pos, $maxDistance, $maxDiff = 0.5){
		$eyePos = $this->getPosition()->add(0, $this->getEyeHeight(), 0);
		if($eyePos->distanceSquared($pos) > $maxDistance ** 2){
			return false;
		}

		$dV = $this->getDirectionPlane();
		$dot = $dV->dot(new Vector2($eyePos->x, $eyePos->z));
		$dot1 = $dV->dot(new Vector2($pos->x, $pos->z));
		return ($dot1 - $dot) >= -$maxDiff;
	}



	protected function processLogin(){
		if(!$this->server->isWhitelisted($this->iusername)){
			$this->close($this->getLeaveMessage(), "Server is white-listed");

			return;
		}elseif($this->server->getNameBans()->isBanned($this->iusername) or $this->server->getIPBans()->isBanned($this->getAddress())){
			$this->close($this->getLeaveMessage(), "You are banned");

			return;
		}

		foreach($this->server->getOnlinePlayers() as $p){
			if($p !== $this and $p->iusername === $this->iusername){
				if($p->kick("logged in from another location") === false){
					$this->close($this->getLeaveMessage(), "Logged in from another location");

					return;
				}
			}elseif($p->loggedIn and $this->getUniqueId()->equals($p->getUniqueId())){
				if($p->kick("logged in from another location") === false){
					$this->close($this->getLeaveMessage(), "Logged in from another location");

					return;
				}
			}
		}

		$this->namedtag = $this->server->getOfflinePlayerData($this->username);

		$this->playedBefore = ($this->namedtag["lastPlayed"] - $this->namedtag["firstPlayed"]) > 1; // microtime(true) - microtime(true) may have less than one millisecond difference
		if(!isset($this->namedtag->NameTag)){
			$this->namedtag->NameTag = new StringTag("NameTag", $this->username);
		}else{
			$this->namedtag["NameTag"] = $this->username;
		}
		$this->gamemode = $this->namedtag["playerGameType"] & 0x03;
		if($this->server->getForceGamemode()){
			$this->gamemode = $this->server->getGamemode();
			$this->namedtag->playerGameType = new IntTag("playerGameType", $this->gamemode);
		}

		$this->allowFlight = (bool) ($this->gamemode & 0x01);

		if(($level = $this->server->getLevelByName($this->namedtag["Level"])) === null){
			$this->setLevel($this->server->getDefaultLevel());
			$this->namedtag["Level"] = $this->level->getName();
			$this->namedtag["Pos"][0] = $this->level->getSpawnLocation()->x;
			$this->namedtag["Pos"][1] = $this->level->getSpawnLocation()->y;
			$this->namedtag["Pos"][2] = $this->level->getSpawnLocation()->z;
		}else{
			$this->setLevel($level);
		}

		$this->achievements = [];

		/** @var ByteTag $achievement */
		foreach($this->namedtag->Achievements as $achievement){
			$this->achievements[$achievement->getName()] = $achievement->getValue() > 0 ? true : false;
		}

		$this->namedtag->lastPlayed = new LongTag("lastPlayed", floor(microtime(true) * 1000));
		if($this->server->getAutoSave()){
			$this->server->saveOfflinePlayerData($this->username, $this->namedtag, true);
		}

		$this->sendPlayStatus(PlayStatusPacket::LOGIN_SUCCESS);

		$this->loggedIn = true;

		$pk = new ResourcePacksInfoPacket();
		$manager = $this->server->getResourceManager();
		$pk->resourcePackEntries = $manager->getResourceStack();
		$pk->mustAccept = $manager->resourcePacksRequired();
		$this->dataPacket($pk);
	}

	protected function completeLoginSequence(){
		parent::__construct($this->level, $this->namedtag);
		$this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin reason"));
		if($ev->isCancelled()){
			$this->close($this->getLeaveMessage(), $ev->getKickMessage());

			return;
		}

		if(!$this->hasValidSpawnPosition() and isset($this->namedtag->SpawnLevel) and ($level = $this->server->getLevelByName($this->namedtag["SpawnLevel"])) instanceof Level){
			$this->spawnPosition = new WeakPosition($this->namedtag["SpawnX"], $this->namedtag["SpawnY"], $this->namedtag["SpawnZ"], $level);
		}

		$spawnPosition = $this->getSpawn();

		$pk = new StartGamePacket();
		$pk->entityUniqueId = $this->id;
		$pk->entityRuntimeId = $this->id;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->seed = -1;
		$pk->dimension = 0; //TODO: implement this properly
		$pk->gamemode = $this->gamemode & 0x01;
		$pk->difficulty = $this->server->getDifficulty();
		$pk->spawnX = $spawnPosition->getFloorX();
		$pk->spawnY = $spawnPosition->getFloorY();
		$pk->spawnZ = $spawnPosition->getFloorZ();
		$pk->hasBeenLoadedInCreative = 1;
		$pk->dayCycleStopTime = -1; //TODO: implement this properly
		$pk->eduMode = 0;
		$pk->rainLevel = 0; //TODO: implement these properly
		$pk->lightningLevel = 0;
		$pk->commandsEnabled = 1;
		$pk->levelId = "";
		$pk->worldName = $this->server->getMotd();
		$this->dataPacket($pk);

		$pk = new SetTimePacket();
		$pk->time = $this->level->getTime();
		$pk->started = $this->level->stopTime == false;
		$this->dataPacket($pk);

		$this->sendAttributes(true);
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);

		$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn", [
			TextFormat::AQUA . $this->username . TextFormat::WHITE,
			$this->ip,
			$this->port,
			$this->id,
			$this->level->getName(),
			round($this->x, 4),
			round($this->y, 4),
			round($this->z, 4)
		]));

		if($this->isOp()){
			$this->setRemoveFormat(false);
		}

		$this->sendCommandData();

		if($this->isCreative()){
			$this->inventory->sendCreativeContents();
		}

		$this->forceMovement = $this->teleportPosition = $this->getPosition();

		$this->server->addOnlinePlayer($this);

		$this->server->onPlayerLogin($this);
	}

	public function handleLogin(LoginPacket $packet) : bool{
		if($this->loggedIn){
			return false;
		}

		$this->username = TextFormat::clean($packet->username);
		$this->displayName = $this->username;
		$this->iusername = strtolower($this->username);
		$this->setDataProperty(self::DATA_NAMETAG, self::DATA_TYPE_STRING, $this->username, false);

		if(count($this->server->getOnlinePlayers()) >= $this->server->getMaxPlayers() and $this->kick("disconnectionScreen.serverFull", false)){
			return true;
		}

		if($packet->protocol !== ProtocolInfo::CURRENT_PROTOCOL){
			if($packet->protocol < ProtocolInfo::CURRENT_PROTOCOL){
				$message = "disconnectionScreen.outdatedClient";
				$this->sendPlayStatus(PlayStatusPacket::LOGIN_FAILED_CLIENT, true);
			}else{
				$message = "disconnectionScreen.outdatedServer";
				$this->sendPlayStatus(PlayStatusPacket::LOGIN_FAILED_SERVER, true);
			}
			$this->close("", $message, false);

			return true;
		}

		$this->randomClientId = $packet->clientId;

		$this->uuid = UUID::fromString($packet->clientUUID);
		$this->rawUUID = $this->uuid->toBinary();

		if(!Player::isValidUserName($packet->username)){
			$this->close("", "disconnectionScreen.invalidName");
			return true;
		}

		if(!Player::isValidSkin($packet->skin)){
			$this->close("", "disconnectionScreen.invalidSkin");
			return true;
		}

		$this->setSkin($packet->skin, $packet->skinId);

		$this->server->getPluginManager()->callEvent($ev = new PlayerPreLoginEvent($this, "Plugin reason"));
		if($ev->isCancelled()){
			$this->close("", $ev->getKickMessage());

			return true;
		}

		//TODO: add JWT verification, add encryption

		$this->processLogin();

		return true;
	}

	public function handlePlayStatus(PlayStatusPacket $packet) : bool{
		return false;
	}

	public function sendPlayStatus(int $status, bool $immediate = false){
		$pk = new PlayStatusPacket();
		$pk->status = $status;
		if($immediate){
			$this->directDataPacket($pk);
		}else{
			$this->dataPacket($pk);
		}
	}

	public function handleServerToClientHandshake(ServerToClientHandshakePacket $packet) : bool{
		return false;
	}

	public function handleClientToServerHandshake(ClientToServerHandshakePacket $packet) : bool{
		return false; //TODO
	}

	public function handleDisconnect(DisconnectPacket $packet) : bool{
		return false;
	}

	public function handleResourcePacksInfo(ResourcePacksInfoPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackStack(ResourcePackStackPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool{
		switch($packet->status){
			case ResourcePackClientResponsePacket::STATUS_REFUSED:
				//TODO: add lang strings for this
				$this->close("", "You must accept resource packs to join this server.", true);
				break;
			case ResourcePackClientResponsePacket::STATUS_SEND_PACKS:
				$manager = $this->server->getResourceManager();
				foreach($packet->packIds as $uuid){
					$pack = $manager->getPackById($uuid);
					if(!($pack instanceof ResourcePack)){
						//Client requested a resource pack but we don't have it available on the server
						$this->close("", "disconnectionScreen.resourcePack", true);
						$this->server->getLogger()->debug("Got a resource pack request for unknown pack with UUID " . $uuid . ", available packs: " . implode(", ", $manager->getPackIdList()));
						return false;
					}

					$pk = new ResourcePackDataInfoPacket();
					$pk->packId = $pack->getPackId();
					$pk->maxChunkSize = 1048576; //1MB
					$pk->chunkCount = $pack->getPackSize() / $pk->maxChunkSize;
					$pk->compressedPackSize = $pack->getPackSize();
					$pk->sha256 = $pack->getSha256();
					$this->dataPacket($pk);
				}

				break;
			case ResourcePackClientResponsePacket::STATUS_HAVE_ALL_PACKS:
				$pk = new ResourcePackStackPacket();
				$manager = $this->server->getResourceManager();
				$pk->resourcePackStack = $manager->getResourceStack();
				$pk->mustAccept = $manager->resourcePacksRequired();
				$this->dataPacket($pk);
				break;
			case ResourcePackClientResponsePacket::STATUS_COMPLETED:
				$this->completeLoginSequence();
				break;
			default:
				return false;
		}

		return true;
	}

	public function handleText(TextPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		$this->craftingType = 0;
		if($packet->type === TextPacket::TYPE_CHAT){
			$packet->message = TextFormat::clean($packet->message, $this->removeFormat);
			foreach(explode("\n", $packet->message) as $message){
				if(trim($message) != "" and strlen($message) <= 255 and $this->messageCounter-- > 0){
					if(substr($message, 0, 2) === "./"){ //Command (./ = fast hack for old plugins post 0.16)
						$message = substr($message, 1);
					}

					$ev = new PlayerCommandPreprocessEvent($this, $message);

					if(mb_strlen($ev->getMessage(), "UTF-8") > 320){
						$ev->setCancelled();
					}
					$this->server->getPluginManager()->callEvent($ev);

					if($ev->isCancelled()){
						break;
					}

					if(substr($ev->getMessage(), 0, 1) === "/"){
						Timings::$playerCommandTimer->startTiming();
						$this->server->dispatchCommand($ev->getPlayer(), substr($ev->getMessage(), 1));
						Timings::$playerCommandTimer->stopTiming();
					}else{
						$this->server->getPluginManager()->callEvent($ev = new PlayerChatEvent($this, $ev->getMessage()));
						if(!$ev->isCancelled()){
							$this->server->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(), [$ev->getPlayer()->getDisplayName(), $ev->getMessage()]), $ev->getRecipients());
						}
					}
				}
			}
		}

		return true;
	}

	public function handleSetTime(SetTimePacket $packet) : bool{
		return false;
	}

	public function handleStartGame(StartGamePacket $packet) : bool{
		return false;
	}

	public function handleAddPlayer(AddPlayerPacket $packet) : bool{
		return false;
	}

	public function handleAddEntity(AddEntityPacket $packet) : bool{
		return false;
	}

	public function handleRemoveEntity(RemoveEntityPacket $packet) : bool{
		return false;
	}

	public function handleAddItemEntity(AddItemEntityPacket $packet) : bool{
		return false;
	}

	public function handleAddHangingEntity(AddHangingEntityPacket $packet) : bool{
		return false;
	}

	public function handleTakeItemEntity(TakeItemEntityPacket $packet) : bool{
		return false;
	}

	public function handleMoveEntity(MoveEntityPacket $packet) : bool{
		return false;
	}

	public function handleMovePlayer(MovePlayerPacket $packet) : bool{
		$newPos = new Vector3($packet->x, $packet->y - $this->getEyeHeight(), $packet->z);

		$revert = false;
		if(!$this->isAlive() or $this->spawned !== true){
			$revert = true;
			$this->forceMovement = new Vector3($this->x, $this->y, $this->z);
		}

		if($this->teleportPosition !== null or ($this->forceMovement instanceof Vector3 and ($newPos->distanceSquared($this->forceMovement) > 0.1 or $revert))){
			$this->sendPosition($this->forceMovement, $packet->yaw, $packet->pitch, MovePlayerPacket::MODE_RESET);
		}else{
			$packet->yaw %= 360;
			$packet->pitch %= 360;

			if($packet->yaw < 0){
				$packet->yaw += 360;
			}

			$this->setRotation($packet->yaw, $packet->pitch);
			$this->newPosition = $newPos;
			$this->forceMovement = null;
		}

		return true;
	}

	public function handleRiderJump(RiderJumpPacket $packet) : bool{
		return false;
	}

	public function handleRemoveBlock(RemoveBlockPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		$this->craftingType = 0;

		$vector = new Vector3($packet->x, $packet->y, $packet->z);

		$item = $this->inventory->getItemInHand();
		$oldItem = clone $item;

		if($this->canInteract($vector->add(0.5, 0.5, 0.5), $this->isCreative() ? 13 : 6) and $this->level->useBreakOn($vector, $item, $this, true)){
			if($this->isSurvival()){
				if(!$item->equals($oldItem) or $item->getCount() !== $oldItem->getCount()){
					$this->inventory->setItemInHand($item);
					$this->inventory->sendHeldItem($this->hasSpawned);
				}

				$this->exhaust(0.025, PlayerExhaustEvent::CAUSE_MINING);
			}
			return true;
		}

		$this->inventory->sendContents($this);
		$target = $this->level->getBlock($vector);
		$tile = $this->level->getTile($vector);

		$this->level->sendBlocks([$this], [$target], UpdateBlockPacket::FLAG_ALL_PRIORITY);

		$this->inventory->sendHeldItem($this);

		if($tile instanceof Spawnable){
			$tile->spawnTo($this);
		}

		return true;
	}

	public function handleUpdateBlock(UpdateBlockPacket $packet) : bool{
		return false;
	}

	public function handleAddPainting(AddPaintingPacket $packet) : bool{
		return false;
	}

	public function handleExplode(ExplodePacket $packet) : bool{
		return false;
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool{
		//TODO: add events so plugins can change this
		$this->getLevel()->addChunkPacket($this->chunk->getX(), $this->chunk->getZ(), $packet);
		return true;
	}

	public function handleLevelEvent(LevelEventPacket $packet) : bool{
		return false;
	}

	public function handleBlockEvent(BlockEventPacket $packet) : bool{
		return false;
	}

	public function handleEntityEvent(EntityEventPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}
		$this->craftingType = 0;

		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false); //TODO: check if this should be true

		switch($packet->event){
			case EntityEventPacket::USE_ITEM: //Eating
				$slot = $this->inventory->getItemInHand();

				if($slot->canBeConsumed()){
					$ev = new PlayerItemConsumeEvent($this, $slot);
					if(!$slot->canBeConsumedBy($this)){
						$ev->setCancelled();
					}
					$this->server->getPluginManager()->callEvent($ev);
					if(!$ev->isCancelled()){
						$slot->onConsume($this);
					}else{
						$this->inventory->sendContents($this);
					}
				}
				break;
			default:
				return false;
		}

		return true;
	}

	public function handleMobEffect(MobEffectPacket $packet) : bool{
		return false;
	}

	public function handleUpdateAttributes(UpdateAttributesPacket $packet) : bool{
		return false;
	}

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		if($packet->inventorySlot === 255){
			$packet->inventorySlot = -1; //Cleared slot
		}else{
			$packet->inventorySlot -= 9; //Get real inventory slot
			$item = $this->inventory->getItem($packet->inventorySlot);

			if(!$item->equals($packet->item)){
				$this->server->getLogger()->debug("Tried to equip " . $packet->item . " but have " . $item . " in target slot");
				$this->inventory->sendContents($this);
				return false;
			}
		}

		$this->inventory->equipItem($packet->hotbarSlot, $packet->inventorySlot);

		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);

		return true;
	}

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool{
		return false;
	}

	public function handleInteract(InteractPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		$this->craftingType = 0;

		$target = $this->level->getEntity($packet->target);

		$cancelled = false;
		switch($packet->action){
			case InteractPacket::ACTION_LEFT_CLICK: //Attack
				if($target instanceof Player and $this->server->getConfigBoolean("pvp", true) === false){
					$cancelled = true;
				}

				if($target instanceof Entity and $this->getGamemode() !== Player::VIEW and $this->isAlive() and $target->isAlive()){
					if($target instanceof DroppedItem or $target instanceof Arrow){
						$this->kick("Attempting to attack an invalid entity");
						$this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidEntity", [$this->getName()]));
						break;
					}

					$item = $this->inventory->getItemInHand();
					$damageTable = [
						Item::WOODEN_SWORD => 4,
						Item::GOLD_SWORD => 4,
						Item::STONE_SWORD => 5,
						Item::IRON_SWORD => 6,
						Item::DIAMOND_SWORD => 7,

						Item::WOODEN_AXE => 3,
						Item::GOLD_AXE => 3,
						Item::STONE_AXE => 3,
						Item::IRON_AXE => 5,
						Item::DIAMOND_AXE => 6,

						Item::WOODEN_PICKAXE => 2,
						Item::GOLD_PICKAXE => 2,
						Item::STONE_PICKAXE => 3,
						Item::IRON_PICKAXE => 4,
						Item::DIAMOND_PICKAXE => 5,

						Item::WOODEN_SHOVEL => 1,
						Item::GOLD_SHOVEL => 1,
						Item::STONE_SHOVEL => 2,
						Item::IRON_SHOVEL => 3,
						Item::DIAMOND_SHOVEL => 4,
					];

					$damage = [
						EntityDamageEvent::MODIFIER_BASE => $damageTable[$item->getId()] ?? 1,
					];

					if(!$this->canInteract($target, 8)){
						$cancelled = true;
					}elseif($target instanceof Player){
						if(($target->getGamemode() & 0x01) > 0){
							break;
						}elseif($this->server->getConfigBoolean("pvp") !== true or $this->server->getDifficulty() === 0){
							$cancelled = true;
						}

						$armorValues = [
							Item::LEATHER_CAP => 1,
							Item::LEATHER_TUNIC => 3,
							Item::LEATHER_PANTS => 2,
							Item::LEATHER_BOOTS => 1,
							Item::CHAIN_HELMET => 1,
							Item::CHAIN_CHESTPLATE => 5,
							Item::CHAIN_LEGGINGS => 4,
							Item::CHAIN_BOOTS => 1,
							Item::GOLD_HELMET => 1,
							Item::GOLD_CHESTPLATE => 5,
							Item::GOLD_LEGGINGS => 3,
							Item::GOLD_BOOTS => 1,
							Item::IRON_HELMET => 2,
							Item::IRON_CHESTPLATE => 6,
							Item::IRON_LEGGINGS => 5,
							Item::IRON_BOOTS => 2,
							Item::DIAMOND_HELMET => 3,
							Item::DIAMOND_CHESTPLATE => 8,
							Item::DIAMOND_LEGGINGS => 6,
							Item::DIAMOND_BOOTS => 3,
						];
						$points = 0;
						foreach($target->getInventory()->getArmorContents() as $index => $i){
							if(isset($armorValues[$i->getId()])){
								$points += $armorValues[$i->getId()];
							}
						}

						$damage[EntityDamageEvent::MODIFIER_ARMOR] = -floor($damage[EntityDamageEvent::MODIFIER_BASE] * $points * 0.04);
					}

					$ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
					if($cancelled){
						$ev->setCancelled();
					}

					$target->attack($ev->getFinalDamage(), $ev);

					if($ev->isCancelled()){
						if($item->isTool() and $this->isSurvival()){
							$this->inventory->sendContents($this);
						}
						break;
					}

					if($this->isSurvival()){
						if($item->isTool()){
							if($item->useOn($target) and $item->getDamage() >= $item->getMaxDurability()){
								$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 1));
							}else{
								$this->inventory->setItemInHand($item);
							}
						}

						$this->exhaust(0.3, PlayerExhaustEvent::CAUSE_ATTACK);
					}
				}
				break;
			case InteractPacket::ACTION_RIGHT_CLICK:
			case InteractPacket::ACTION_LEAVE_VEHICLE:
			case InteractPacket::ACTION_MOUSEOVER:
				break; //TODO: handle these
			default:
				$this->server->getLogger()->debug("Unhandled/unknown interaction type " . $packet->action . "received from ". $this->getName());
				return false;
		}

		return true;
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool{
		$tile = $this->getLevel()->getTile($this->temporalVector->setComponents($packet->tileX, $packet->tileY, $packet->tileZ));
		if($tile instanceof Tile){ //TODO: check if the held item matches the target tile
			$nbt = $tile->getCleanedNBT();
			if($nbt instanceof CompoundTag){
				$item = $this->inventory->getItemInHand();
				$item->setCustomBlockData($nbt);
				$item->setLore(["+(DATA)"]);
				$this->inventory->setItemInHand($item);
			}

			return true;
		}
		return false;
	}

	public function handleUseItem(UseItemPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		$blockVector = new Vector3($packet->x, $packet->y, $packet->z);

		$this->craftingType = 0;

		if($packet->face >= 0 and $packet->face <= 5){ //Use Block, place
			$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);

			if(!$this->canInteract($blockVector->add(0.5, 0.5, 0.5), 13) or $this->isSpectator()){
			}elseif($this->isCreative()){
				$item = $this->inventory->getItemInHand();
				if($this->level->useItemOn($blockVector, $item, $packet->face, $packet->fx, $packet->fy, $packet->fz, $this) === true){
					return true;
				}
			}elseif(!$this->inventory->getItemInHand()->equals($packet->item)){
				$this->inventory->sendHeldItem($this);
			}else{
				$item = $this->inventory->getItemInHand();
				$oldItem = clone $item;
				//TODO: Implement adventure mode checks
				if($this->level->useItemOn($blockVector, $item, $packet->face, $packet->fx, $packet->fy, $packet->fz, $this)){
					if(!$item->equals($oldItem) or $item->getCount() !== $oldItem->getCount()){
						$this->inventory->setItemInHand($item);
						$this->inventory->sendHeldItem($this->hasSpawned);
					}
					return true;
				}
			}

			$this->inventory->sendHeldItem($this);

			if($blockVector->distanceSquared($this) > 10000){
				return true;
			}
			$target = $this->level->getBlock($blockVector);
			$block = $target->getSide($packet->face);

			$this->level->sendBlocks([$this], [$target, $block], UpdateBlockPacket::FLAG_ALL_PRIORITY);
			return true;
		}elseif($packet->face === -1){
			$aimPos = new Vector3(
				-sin($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI),
				-sin($this->pitch / 180 * M_PI),
				cos($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI)
			);

			if($this->isCreative()){
				$item = $this->inventory->getItemInHand();
			}elseif(!$this->inventory->getItemInHand()->equals($packet->item)){
				$this->inventory->sendHeldItem($this);
				return true;
			}else{
				$item = $this->inventory->getItemInHand();
			}

			$ev = new PlayerInteractEvent($this, $item, $aimPos, $packet->face, PlayerInteractEvent::RIGHT_CLICK_AIR);

			$this->server->getPluginManager()->callEvent($ev);

			if($ev->isCancelled()){
				$this->inventory->sendHeldItem($this);
				return true;
			}

			if($item->getId() === Item::SNOWBALL){
				$nbt = new CompoundTag("", [
					"Pos" => new ListTag("Pos", [
						new DoubleTag("", $this->x),
						new DoubleTag("", $this->y + $this->getEyeHeight()),
						new DoubleTag("", $this->z)
					]),
					"Motion" => new ListTag("Motion", [
						new DoubleTag("", $aimPos->x),
						new DoubleTag("", $aimPos->y),
						new DoubleTag("", $aimPos->z)
					]),
					"Rotation" => new ListTag("Rotation", [
						new FloatTag("", $this->yaw),
						new FloatTag("", $this->pitch)
					]),
				]);

				$f = 1.5;
				$snowball = Entity::createEntity("Snowball", $this->getLevel(), $nbt, $this);
				$snowball->setMotion($snowball->getMotion()->multiply($f));
				if($this->isSurvival()){
					$item->setCount($item->getCount() - 1);
					$this->inventory->setItemInHand($item->getCount() > 0 ? $item : Item::get(Item::AIR));
				}
				if($snowball instanceof Projectile){
					$this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($snowball));
					if($projectileEv->isCancelled()){
						$snowball->kill();
					}else{
						$snowball->spawnToAll();
						$this->level->addSound(new LaunchSound($this), $this->getViewers());
					}
				}else{
					$snowball->spawnToAll();
				}
			}

			$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, true);
			$this->startAction = $this->server->getTick();
		}

		return true;
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		if($this->spawned === false or (!$this->isAlive() and $packet->action !== PlayerActionPacket::ACTION_RESPAWN and $packet->action !== PlayerActionPacket::ACTION_DIMENSION_CHANGE)){
			return true;
		}

		$packet->eid = $this->id;
		$pos = new Vector3($packet->x, $packet->y, $packet->z);

		switch($packet->action){
			case PlayerActionPacket::ACTION_START_BREAK:
				if($this->lastBreak !== PHP_INT_MAX or $pos->distanceSquared($this) > 10000){
					break;
				}
				$target = $this->level->getBlock($pos);
				$ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, $packet->face, $target->getId() === 0 ? PlayerInteractEvent::LEFT_CLICK_AIR : PlayerInteractEvent::LEFT_CLICK_BLOCK);
				$this->getServer()->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					$this->inventory->sendHeldItem($this);
					break;
				}
				$block = $target->getSide($packet->face);
				if($block->getId() === Block::FIRE){
					$this->level->setBlock($block, new Air());
					break;
				}
				$this->lastBreak = microtime(true);
				break;
			case PlayerActionPacket::ACTION_ABORT_BREAK:
				$this->lastBreak = PHP_INT_MAX;
				break;
			case PlayerActionPacket::ACTION_STOP_BREAK:
				break;
			case PlayerActionPacket::ACTION_RELEASE_ITEM:
				if($this->startAction > -1 and $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION)){
					if($this->inventory->getItemInHand()->getId() === Item::BOW){
						$bow = $this->inventory->getItemInHand();
						if($this->isSurvival() and !$this->inventory->contains(Item::get(Item::ARROW, 0, 1))){
							$this->inventory->sendContents($this);
							break;
						}

						$nbt = new CompoundTag("", [
							"Pos" => new ListTag("Pos", [
								new DoubleTag("", $this->x),
								new DoubleTag("", $this->y + $this->getEyeHeight()),
								new DoubleTag("", $this->z)
							]),
							"Motion" => new ListTag("Motion", [
								new DoubleTag("", -sin($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI)),
								new DoubleTag("", -sin($this->pitch / 180 * M_PI)),
								new DoubleTag("", cos($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI))
							]),
							"Rotation" => new ListTag("Rotation", [
								new FloatTag("", $this->yaw),
								new FloatTag("", $this->pitch)
							]),
							"Fire" => new ShortTag("Fire", $this->isOnFire() ? 45 * 60 : 0)
						]);

						$diff = ($this->server->getTick() - $this->startAction);
						$p = $diff / 20;
						$f = min((($p ** 2) + $p * 2) / 3, 1) * 2;
						$ev = new EntityShootBowEvent($this, $bow, Entity::createEntity("Arrow", $this->getLevel(), $nbt, $this, $f == 2 ? true : false), $f);

						if($f < 0.1 or $diff < 5){
							$ev->setCancelled();
						}

						$this->server->getPluginManager()->callEvent($ev);

						if($ev->isCancelled()){
							$ev->getProjectile()->kill();
							$this->inventory->sendContents($this);
						}else{
							$ev->getProjectile()->setMotion($ev->getProjectile()->getMotion()->multiply($ev->getForce()));
							if($this->isSurvival()){
								$this->inventory->removeItem(Item::get(Item::ARROW, 0, 1));
								$bow->setDamage($bow->getDamage() + 1);
								if($bow->getDamage() >= 385){
									$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 0));
								}else{
									$this->inventory->setItemInHand($bow);
								}
							}
							if($ev->getProjectile() instanceof Projectile){
								$this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($ev->getProjectile()));
								if($projectileEv->isCancelled()){
									$ev->getProjectile()->kill();
								}else{
									$ev->getProjectile()->spawnToAll();
									$this->level->addSound(new LaunchSound($this), $this->getViewers());
								}
							}else{
								$ev->getProjectile()->spawnToAll();
							}
						}
					}
				}elseif($this->inventory->getItemInHand()->getId() === Item::BUCKET and $this->inventory->getItemInHand()->getDamage() === 1){ //Milk!
					$this->server->getPluginManager()->callEvent($ev = new PlayerItemConsumeEvent($this, $this->inventory->getItemInHand()));
					if($ev->isCancelled()){
						$this->inventory->sendContents($this);
						break;
					}

					$pk = new EntityEventPacket();
					$pk->eid = $this->getId();
					$pk->event = EntityEventPacket::USE_ITEM;
					$this->dataPacket($pk);
					$this->server->broadcastPacket($this->getViewers(), $pk);

					if($this->isSurvival()){
						$slot = $this->inventory->getItemInHand();
						--$slot->count;
						$this->inventory->setItemInHand($slot);
						$this->inventory->addItem(Item::get(Item::BUCKET, 0, 1));
					}

					$this->removeAllEffects();
				}else{
					$this->inventory->sendContents($this);
				}
				break;
			case PlayerActionPacket::ACTION_STOP_SLEEPING:
				$this->stopSleep();
				break;
			case PlayerActionPacket::ACTION_RESPAWN:
				if($this->spawned === false or $this->isAlive() or !$this->isOnline()){
					break;
				}

				if($this->server->isHardcore()){
					$this->setBanned(true);
					break;
				}

				$this->craftingType = 0;

				$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $this->getSpawn()));

				$this->teleport($ev->getRespawnPosition());

				$this->setSprinting(false);
				$this->setSneaking(false);

				$this->extinguish();
				$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, 400, false);
				$this->deadTicks = 0;
				$this->noDamageTicks = 60;

				$this->removeAllEffects();
				$this->setHealth($this->getMaxHealth());

				foreach($this->attributeMap->getAll() as $attr){
					$attr->resetToDefault();
				}

				$this->sendData($this);

				$this->sendSettings();
				$this->inventory->sendContents($this);
				$this->inventory->sendArmorContents($this);

				$this->spawnToAll();
				$this->scheduleUpdate();
				break;
			case PlayerActionPacket::ACTION_JUMP:
				$this->jump();
				return true;
			case PlayerActionPacket::ACTION_START_SPRINT:
				$ev = new PlayerToggleSprintEvent($this, true);
				$this->server->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					$this->sendData($this);
				}else{
					$this->setSprinting(true);
				}
				return true;
			case PlayerActionPacket::ACTION_STOP_SPRINT:
				$ev = new PlayerToggleSprintEvent($this, false);
				$this->server->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					$this->sendData($this);
				}else{
					$this->setSprinting(false);
				}
				return true;
			case PlayerActionPacket::ACTION_START_SNEAK:
				$ev = new PlayerToggleSneakEvent($this, true);
				$this->server->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					$this->sendData($this);
				}else{
					$this->setSneaking(true);
				}
				return true;
			case PlayerActionPacket::ACTION_STOP_SNEAK:
				$ev = new PlayerToggleSneakEvent($this, false);
				$this->server->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					$this->sendData($this);
				}else{
					$this->setSneaking(false);
				}
				return true;
			case PlayerActionPacket::ACTION_START_GLIDE:
			case PlayerActionPacket::ACTION_STOP_GLIDE:
				break; //TODO
			default:
				$this->server->getLogger()->debug("Unhandled/unknown player action type " . $packet->action . " from " . $this->getName());
				return false;
		}

		$this->startAction = -1;
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);

		return true;
	}

	public function handlePlayerFall(PlayerFallPacket $packet) : bool{
		return false;
	}

	public function handleHurtArmor(HurtArmorPacket $packet) : bool{
		return false;
	}

	public function handleSetEntityData(SetEntityDataPacket $packet) : bool{
		return false;
	}

	public function handleSetEntityMotion(SetEntityMotionPacket $packet) : bool{
		return false;
	}

	public function handleSetEntityLink(SetEntityLinkPacket $packet) : bool{
		return false;
	}

	public function handleSetHealth(SetHealthPacket $packet) : bool{
		return false;
	}

	public function handleSetSpawnPosition(SetSpawnPositionPacket $packet) : bool{
		return false;
	}

	public function handleAnimate(AnimatePacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerAnimationEvent($this, $packet->action));
		if($ev->isCancelled()){
			return true;
		}

		$pk = new AnimatePacket();
		$pk->eid = $this->getId();
		$pk->action = $ev->getAnimationType();
		$this->server->broadcastPacket($this->getViewers(), $pk);

		return true;
	}

	public function handleRespawn(RespawnPacket $packet) : bool{
		return false;
	}

	public function handleDropItem(DropItemPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		if($packet->item->getId() === Item::AIR){
			// Windows 10 Edition drops the contents of the crafting grid on container close - including air.
			return true;
		}

		$item = $this->inventory->getItemInHand();
		$ev = new PlayerDropItemEvent($this, $item);
		$this->server->getPluginManager()->callEvent($ev);
		if($ev->isCancelled()){
			$this->inventory->sendContents($this);
			return true;
		}

		$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 1));
		$motion = $this->getDirectionVector()->multiply(0.4);

		$this->level->dropItem($this->add(0, 1.3, 0), $item, $motion, 40);

		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);

		return true;
	}

	public function handleInventoryAction(InventoryActionPacket $packet) : bool{
		return false;
	}

	public function handleContainerOpen(ContainerOpenPacket $packet) : bool{
		return false;
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool{
		if($this->spawned === false or $packet->windowid === 0){
			return true;
		}

		$this->craftingType = 0;
		$this->currentTransaction = null;
		if(isset($this->windowIndex[$packet->windowid])){
			$this->server->getPluginManager()->callEvent(new InventoryCloseEvent($this->windowIndex[$packet->windowid], $this));
			$this->removeWindow($this->windowIndex[$packet->windowid]);
		}else{
			unset($this->windowIndex[$packet->windowid]);
		}

		return true;
	}

	public function handleContainerSetSlot(ContainerSetSlotPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		if($packet->slot < 0){
			return false;
		}

		switch($packet->windowid){
			case ContainerSetContentPacket::SPECIAL_INVENTORY: //Normal inventory change
				if($packet->slot >= $this->inventory->getSize()){
					return false;
				}

				$transaction = new BaseTransaction($this->inventory, $packet->slot, $this->inventory->getItem($packet->slot), $packet->item);
				break;
			case ContainerSetContentPacket::SPECIAL_ARMOR: //Armour change
				if($packet->slot >= 4){
					return false;
				}

				$transaction = new BaseTransaction($this->inventory, $packet->slot + $this->inventory->getSize(), $this->inventory->getArmorItem($packet->slot), $packet->item);
				break;
			case ContainerSetContentPacket::SPECIAL_HOTBAR: //Hotbar link update
				//hotbarSlot 0-8, slot 9-44
				$this->inventory->setHotbarSlotIndex($packet->hotbarSlot, $packet->slot - 9);
				return true;
			default:
				if(!isset($this->windowIndex[$packet->windowid])){
					return false; //unknown windowID and/or not matching any open windows
				}

				$this->craftingType = 0;
				$inv = $this->windowIndex[$packet->windowid];
				$transaction = new BaseTransaction($inv, $packet->slot, $inv->getItem($packet->slot), $packet->item);
				break;
		}

		if($transaction->getSourceItem()->equals($transaction->getTargetItem()) and $transaction->getTargetItem()->getCount() === $transaction->getSourceItem()->getCount()){ //No changes!
			//No changes, just a local inventory update sent by the client
			return true;
		}

		if($this->currentTransaction === null or $this->currentTransaction->getCreationTime() < (microtime(true) - 8)){
			if($this->currentTransaction !== null){
				foreach($this->currentTransaction->getInventories() as $inventory){
					if($inventory instanceof PlayerInventory){
						$inventory->sendArmorContents($this);
					}
					$inventory->sendContents($this);
				}
			}
			$this->currentTransaction = new SimpleTransactionGroup($this);
		}

		$this->currentTransaction->addTransaction($transaction);

		if($this->currentTransaction->canExecute()){
			$achievements = [];
			foreach($this->currentTransaction->getTransactions() as $ts){
				$inv = $ts->getInventory();
				if($inv instanceof FurnaceInventory){
					if($ts->getSlot() === 2){
						switch($inv->getResult()->getId()){
							case Item::IRON_INGOT:
								$achievements[] = "acquireIron";
								break;
						}
					}
				}
			}

			if($this->currentTransaction->execute()){
				foreach($achievements as $a){
					$this->awardAchievement($a);
				}
			}

			$this->currentTransaction = null;
		}

		return true;
	}

	public function handleContainerSetData(ContainerSetDataPacket $packet) : bool{
		return false;
	}

	public function handleContainerSetContent(ContainerSetContentPacket $packet) : bool{
		return false;
	}

	public function handleCraftingData(CraftingDataPacket $packet) : bool{
		return false;
	}

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		$recipe = $this->server->getCraftingManager()->getRecipe($packet->id);

		if($recipe === null or (($recipe instanceof BigShapelessRecipe or $recipe instanceof BigShapedRecipe) and $this->craftingType === 0)){
			$this->inventory->sendContents($this);
			return true;
		}

		$canCraft = true;

		if($recipe instanceof ShapedRecipe){
			for($x = 0; $x < 3 and $canCraft; ++$x){
				for($y = 0; $y < 3; ++$y){
					/** @var Item $item */
					$item = $packet->input[$y * 3 + $x];
					$ingredient = $recipe->getIngredient($x, $y);
					if($item->getCount() > 0){
						if($ingredient === null or !$ingredient->equals($item, !$ingredient->hasAnyDamageValue(), $ingredient->hasCompoundTag())){
							$canCraft = false;
							break;
						}
					}
				}
			}
		}elseif($recipe instanceof ShapelessRecipe){
			$needed = $recipe->getIngredientList();

			for($x = 0; $x < 3 and $canCraft; ++$x){
				for($y = 0; $y < 3; ++$y){
					/** @var Item $item */
					$item = clone $packet->input[$y * 3 + $x];

					foreach($needed as $k => $n){
						if($n->equals($item, !$n->hasAnyDamageValue(), $n->hasCompoundTag())){
							$remove = min($n->getCount(), $item->getCount());
							$n->setCount($n->getCount() - $remove);
							$item->setCount($item->getCount() - $remove);

							if($n->getCount() === 0){
								unset($needed[$k]);
							}
						}
					}

					if($item->getCount() > 0){
						$canCraft = false;
						break;
					}
				}
			}

			if(count($needed) > 0){
				$canCraft = false;
			}
		}else{
			$canCraft = false;
		}

		/** @var Item[] $ingredients */
		$ingredients = $packet->input;
		$result = $packet->output[0];

		if(!$canCraft or !$recipe->getResult()->equals($result)){
			$this->server->getLogger()->debug("Unmatched recipe " . $recipe->getId() . " from player " . $this->getName() . ": expected " . $recipe->getResult() . ", got " . $result . ", using: " . implode(", ", $ingredients));
			$this->inventory->sendContents($this);
			return true;
		}

		$used = array_fill(0, $this->inventory->getSize(), 0);

		foreach($ingredients as $ingredient){
			$slot = -1;
			foreach($this->inventory->getContents() as $index => $item){
				if($ingredient->getId() !== 0 and $ingredient->equals($item, !$ingredient->hasAnyDamageValue(), $ingredient->hasCompoundTag()) and ($item->getCount() - $used[$index]) >= 1){
					$slot = $index;
					$used[$index]++;
					break;
				}
			}

			if($ingredient->getId() !== 0 and $slot === -1){
				$canCraft = false;
				break;
			}
		}

		if(!$canCraft){
			$this->server->getLogger()->debug("Unmatched recipe " . $recipe->getId() . " from player " . $this->getName() . ": client does not have enough items, using: " . implode(", ", $ingredients));
			$this->inventory->sendContents($this);
			return true;
		}

		$this->server->getPluginManager()->callEvent($ev = new CraftItemEvent($this, $ingredients, $recipe));

		if($ev->isCancelled()){
			$this->inventory->sendContents($this);
			return true;
		}

		foreach($used as $slot => $count){
			if($count === 0){
				continue;
			}

			$item = $this->inventory->getItem($slot);

			if($item->getCount() > $count){
				$newItem = clone $item;
				$newItem->setCount($item->getCount() - $count);
			}else{
				$newItem = Item::get(Item::AIR, 0, 0);
			}

			$this->inventory->setItem($slot, $newItem);
		}

		$extraItem = $this->inventory->addItem($recipe->getResult());
		if(count($extraItem) > 0){
			foreach($extraItem as $item){
				$this->level->dropItem($this, $item);
			}
		}

		switch($recipe->getResult()->getId()){
			case Item::WORKBENCH:
				$this->awardAchievement("buildWorkBench");
				break;
			case Item::WOODEN_PICKAXE:
				$this->awardAchievement("buildPickaxe");
				break;
			case Item::FURNACE:
				$this->awardAchievement("buildFurnace");
				break;
			case Item::WOODEN_HOE:
				$this->awardAchievement("buildHoe");
				break;
			case Item::BREAD:
				$this->awardAchievement("makeBread");
				break;
			case Item::CAKE:
				//TODO: detect complex recipes like cake that leave remains
				$this->awardAchievement("bakeCake");
				$this->inventory->addItem(Item::get(Item::BUCKET, 0, 3));
				break;
			case Item::STONE_PICKAXE:
			case Item::GOLD_PICKAXE:
			case Item::IRON_PICKAXE:
			case Item::DIAMOND_PICKAXE:
				$this->awardAchievement("buildBetterPickaxe");
				break;
			case Item::WOODEN_SWORD:
				$this->awardAchievement("buildSword");
				break;
			case Item::DIAMOND:
				$this->awardAchievement("diamond");
				break;
		}


		return true;
	}

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool{
		if($packet->isFlying and !$this->allowFlight and !$this->server->getAllowFlight()){
			$this->kick($this->server->getLanguage()->translateString("kick.reason.cheat", ["%ability.flight"]));
			return true;
		}elseif($packet->isFlying !== $this->isFlying()){
			$this->server->getPluginManager()->callEvent($ev = new PlayerToggleFlightEvent($this, $packet->isFlying));
			if($ev->isCancelled()){
				$this->sendSettings();
			}else{
				$this->flying = $ev->isFlying();
			}
		}

		if($packet->noClip and !$this->allowMovementCheats and !$this->isSpectator()){
			$this->kick($this->server->getLanguage()->translateString("kick.reason.cheat", ["%ability.noclip"]));
			return true;
		}

		//TODO: check other changes

		return true;
	}

	public function handleBlockEntityData(BlockEntityDataPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}
		$this->craftingType = 0;

		$pos = new Vector3($packet->x, $packet->y, $packet->z);
		if($pos->distanceSquared($this) > 10000){
			return true;
		}

		$t = $this->level->getTile($pos);
		if($t instanceof Spawnable){
			$nbt = new NBT(NBT::LITTLE_ENDIAN);
			$nbt->read($packet->namedtag, false, true);
			$nbt = $nbt->getData();
			if(!$t->updateCompoundTag($nbt, $this)){
				$t->spawnTo($this);
			}
		}

		return true;
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool{
		return false; //TODO
	}

	public function handleFullChunkData(FullChunkDataPacket $packet) : bool{
		return false;
	}

	public function handleSetCommandsEnabled(SetCommandsEnabledPacket $packet) : bool{
		return false;
	}

	public function handleSetDifficulty(SetDifficultyPacket $packet) : bool{
		return false;
	}

	public function handleChangeDimension(ChangeDimensionPacket $packet) : bool{
		return false;
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool{
		if($packet->gamemode !== ($this->gamemode & 0x01)){
			//GUI gamemode change, set it back to original for now (only possible through client bug or hack with current allowed client permissions)
			$pk = new SetPlayerGameTypePacket();
			$pk->gamemode = $this->gamemode & 0x01;
			$this->dataPacket($pk);
			$this->sendSettings();
		}
		return true;
	}

	public function handlePlayerList(PlayerListPacket $packet) : bool{
		return false;
	}

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool{
		return false; //TODO
	}

	public function handleClientboundMapItemData(ClientboundMapItemDataPacket $packet) : bool{
		return false;
	}

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool{
		return false; //TODO
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool{
		$this->setViewDistance($packet->radius);

		return true;
	}

	public function handleChunkRadiusUpdated(ChunkRadiusUpdatedPacket $packet) : bool{
		return false;
	}

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}

		$tile = $this->level->getTile($this->temporalVector->setComponents($packet->x, $packet->y, $packet->z));
		if($tile instanceof ItemFrame){
			if($this->isSpectator()){
				$tile->spawnTo($this);
				return true;
			}

			if(lcg_value() <= $tile->getItemDropChance()){
				$this->level->dropItem($tile->getBlock(), $tile->getItem());
			}
			$tile->setItem(null);
			$tile->setItemRotation(0);
		}

		return true;
	}

	public function handleReplaceItemInSlot(ReplaceItemInSlotPacket $packet) : bool{
		return false;
	}

	public function handleAddItem(AddItemPacket $packet) : bool{
		return false;
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool{
		return false; //TODO: handle resume
	}

	public function handleAvailableCommands(AvailableCommandsPacket $packet) : bool{
		return false;
	}

	public function handleCommandStep(CommandStepPacket $packet) : bool{
		if($this->spawned === false or !$this->isAlive()){
			return true;
		}
		$this->craftingType = 0;
		$commandText = $packet->command;
		if($packet->args !== null){
			foreach($packet->args as $arg){ //command ordering will be an issue
				$commandText .= " " . $arg;
			}
		}
		$this->server->getPluginManager()->callEvent($ev = new PlayerCommandPreprocessEvent($this, "/" . $commandText));
		if($ev->isCancelled()){
			return true;
		}

		Timings::$playerCommandTimer->startTiming();
		$this->server->dispatchCommand($ev->getPlayer(), substr($ev->getMessage(), 1));
		Timings::$playerCommandTimer->stopTiming();

		return true;
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool{
		return false; //TODO
	}

	public function handleUpdateTrade(UpdateTradePacket $packet) : bool{
		return false;
	}

	public function handleResourcePackDataInfo(ResourcePackDataInfoPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackChunkData(ResourcePackChunkDataPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool{
		$manager = $this->server->getResourceManager();
		$pack = $manager->getPackById($packet->packId);
		if(!($pack instanceof ResourcePack)){
			$this->close("", "disconnectionScreen.resourcePack", true);
			$this->server->getLogger()->debug("Got a resource pack chunk request for unknown pack with UUID " . $packet->packId . ", available packs: " . implode(", ", $manager->getPackIdList()));

			return false;
		}

		$pk = new ResourcePackChunkDataPacket();
		$pk->packId = $pack->getPackId();
		$pk->chunkIndex = $packet->chunkIndex;
		$pk->data = $pack->getPackChunk(1048576 * $packet->chunkIndex, 1048576);
		$pk->progress = (1048576 * $packet->chunkIndex);
		$this->dataPacket($pk);
		return true;
	}

	public function handleTransfer(TransferPacket $packet) : bool{
		return false;
	}

	public function handlePlaySound(PlaySoundPacket $packet) : bool{
		return false;
	}

	public function handleStopSound(StopSoundPacket $packet) : bool{
		return false;
	}

	public function handleSetTitle(SetTitlePacket $packet) : bool{
		return false;
	}

	public function handleUnknown(UnknownPacket $packet) : bool{
		$this->server->getLogger()->debug("Received unknown packet from " . $this->getName() . ": 0x" . bin2hex($packet->payload));
		return true;
	}

	/**
	 * Called when a packet is received from the client. This method will call DataPacketReceiveEvent.
	 *
	 * @param DataPacket $packet
	 */
	public function handleDataPacket(DataPacket $packet){
		if($this->connected === false){
			return;
		}

		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

		$packet->decode();
		assert($packet->feof(), "Still " . strlen(substr($packet->buffer, $packet->offset)) . " bytes unread in " . get_class($packet));

		$this->server->getPluginManager()->callEvent($ev = new DataPacketReceiveEvent($this, $packet));
		if(!$ev->isCancelled() and !$packet->handle($this)){
			$this->server->getLogger()->debug("Unhandled " . get_class($packet) . " received from " . $this->getName() . ": 0x" . bin2hex($packet->buffer));
		}

		$timings->stopTiming();
	}

	/**
	 * Transfers a player to another server.
	 *
	 * @param string $address The IP address or hostname of the destination server
	 * @param int    $port    The destination port, defaults to 19132
	 * @param string $message Message to show in the console when closing the player
	 *
	 * @return bool if transfer was successful.
	 */
	public function transfer(string $address, int $port = 19132, string $message = "transfer") : bool{
		$this->server->getPluginManager()->callEvent($ev = new PlayerTransferEvent($this, $address, $port, $message));

		if(!$ev->isCancelled()){
			$pk = new TransferPacket();
			$pk->address = $ev->getAddress();
			$pk->port = $ev->getPort();
			$this->directDataPacket($pk);
			$this->close("", $ev->getMessage(), false);

			return true;
		}

		return false;
	}

	/**
	 * Kicks a player from the server
	 *
	 * @param string $reason
	 * @param bool   $isAdmin
	 *
	 * @return bool
	 */
	public function kick($reason = "", $isAdmin = true){
		$this->server->getPluginManager()->callEvent($ev = new PlayerKickEvent($this, $reason, $this->getLeaveMessage()));
		if(!$ev->isCancelled()){
			if($isAdmin){
				if(!$this->isBanned()){
					$message = "Kicked by admin." . ($reason !== "" ? " Reason: " . $reason : "");
				}else{
					$message = $reason;
				}
			}else{
				if($reason === ""){
					$message = "disconnectionScreen.noReason";
				}else{
					$message = $reason;
				}
			}
			$this->close($ev->getQuitMessage(), $message);

			return true;
		}

		return false;
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
	public function addTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1){
		$this->setTitleDuration($fadeIn, $stay, $fadeOut);
		if($subtitle !== ""){
			$this->sendTitleText($subtitle, SetTitlePacket::TYPE_SET_SUBTITLE);
		}
		$this->sendTitleText($title, SetTitlePacket::TYPE_SET_TITLE);
	}

	/**
	 * Adds small text to the user's screen.
	 *
	 * @param string $message
	 */
	public function addActionBarMessage(string $message){
		$this->sendTitleText($message, SetTitlePacket::TYPE_SET_ACTIONBAR_MESSAGE);
	}

	/**
	 * Removes the title from the client's screen.
	 */
	public function removeTitles(){
		$pk = new SetTitlePacket();
		$pk->type = SetTitlePacket::TYPE_CLEAR_TITLE;
		$this->dataPacket($pk);
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
			$pk = new SetTitlePacket();
			$pk->type = SetTitlePacket::TYPE_SET_ANIMATION_TIMES;
			$pk->fadeInTime = $fadeIn;
			$pk->stayTime = $stay;
			$pk->fadeOutTime = $fadeOut;
			$this->dataPacket($pk);
		}
	}

	/**
	 * Internal function used for sending titles.
	 *
	 * @param string $title
	 * @param int $type
	 */
	protected function sendTitleText(string $title, int $type){
		$pk = new SetTitlePacket();
		$pk->type = $type;
		$pk->text = $title;
		$this->dataPacket($pk);
	}

	/**
	 * Sends a direct chat message to a player
	 *
	 * @param string|TextContainer $message
	 */
	public function sendMessage($message){
		if($message instanceof TextContainer){
			if($message instanceof TranslationContainer){
				$this->sendTranslation($message->getText(), $message->getParameters());
				return;
			}
			$message = $message->getText();
		}

		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_RAW;
		$pk->message = $this->server->getLanguage()->translateString($message);
		$this->dataPacket($pk);
	}

	public function sendTranslation($message, array $parameters = []){
		$pk = new TextPacket();
		if(!$this->server->isLanguageForced()){
			$pk->type = TextPacket::TYPE_TRANSLATION;
			$pk->message = $this->server->getLanguage()->translateString($message, $parameters, "pocketmine.");
			foreach($parameters as $i => $p){
				$parameters[$i] = $this->server->getLanguage()->translateString($p, $parameters, "pocketmine.");
			}
			$pk->parameters = $parameters;
		}else{
			$pk->type = TextPacket::TYPE_RAW;
			$pk->message = $this->server->getLanguage()->translateString($message, $parameters);
		}
		$this->dataPacket($pk);
	}

	public function sendPopup($message, $subtitle = ""){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_POPUP;
		$pk->source = $message;
		$pk->message = $subtitle;
		$this->dataPacket($pk);
	}

	public function sendTip($message){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_TIP;
		$pk->message = $message;
		$this->dataPacket($pk);
	}

	/**
	 * @param string $sender
	 * @param string $message
	 */
	public function sendWhisper($sender, $message){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_WHISPER;
		$pk->source = $sender;
		$pk->message = $message;
		$this->dataPacket($pk);
	}

	/**
	 * Note for plugin developers: use kick() with the isAdmin
	 * flag set to kick without the "Kicked by admin" part instead of this method.
	 *
	 * @param string $message Message to be broadcasted
	 * @param string $reason  Reason showed in console
	 * @param bool   $notify
	 */
	final public function close($message = "", $reason = "generic reason", $notify = true){
		if($this->connected and !$this->closed){
			if($notify and strlen((string) $reason) > 0){
				$pk = new DisconnectPacket();
				$pk->message = $reason;
				$this->directDataPacket($pk);
			}

			$this->connected = false;

			$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

			if($this->joined){
				//TODO: add events for player data saving
				$this->save();

				$this->server->getPluginManager()->callEvent($ev = new PlayerQuitEvent($this, $message));
				if($ev->getQuitMessage() != ""){
					$this->server->broadcastMessage($ev->getQuitMessage());
				}
			}
			$this->joined = false;

			if($this->isValid()){
				foreach($this->usedChunks as $index => $d){
					Level::getXZ($index, $chunkX, $chunkZ);
					$this->level->unregisterChunkLoader($this, $chunkX, $chunkZ);
					foreach($this->level->getChunkEntities($chunkX, $chunkZ) as $entity){
						$entity->despawnFrom($this);
					}
					unset($this->usedChunks[$index]);
				}
			}
			$this->usedChunks = [];
			$this->loadQueue = [];

			foreach($this->server->getOnlinePlayers() as $player){
				if(!$player->canSee($this)){
					$player->showPlayer($this);
				}
			}
			$this->hiddenPlayers = [];

			foreach($this->windowIndex as $window){
				$this->removeWindow($window);
			}
			$this->windows = null;
			$this->windowIndex = [];

			parent::close();
			$this->spawned = false;

			$this->interface->close($this, $notify ? $reason : "");

			if($this->loggedIn){
				$this->server->removeOnlinePlayer($this);
			}
			$this->loggedIn = false;

			$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logOut", [
				TextFormat::AQUA . $this->getName() . TextFormat::WHITE,
				$this->ip,
				$this->port,
				$this->getServer()->getLanguage()->translateString($reason)
			]));

			$this->spawnPosition = null;

			if($this->perm !== null){
				$this->perm->clearPermissions();
				$this->perm = null;
			}

			if($this->inventory !== null){
				$this->inventory = null;
				$this->currentTransaction = null;
			}

			$this->server->removePlayer($this);
		}
	}

	public function __debugInfo(){
		return [];
	}

	/**
	 * Handles player data saving
	 *
	 * @param bool $async
	 */
	public function save($async = false){
		if($this->closed){
			throw new \InvalidStateException("Tried to save closed player");
		}

		parent::saveNBT();

		if($this->isValid()){
			$this->namedtag->Level = new StringTag("Level", $this->level->getFolderName());
		}

		if($this->hasValidSpawnPosition()){
			$this->namedtag["SpawnLevel"] = $this->spawnPosition->getLevel()->getFolderName();
			$this->namedtag["SpawnX"] = (int) $this->spawnPosition->x;
			$this->namedtag["SpawnY"] = (int) $this->spawnPosition->y;
			$this->namedtag["SpawnZ"] = (int) $this->spawnPosition->z;
		}

		foreach($this->achievements as $achievement => $status){
			$this->namedtag->Achievements[$achievement] = new ByteTag($achievement, $status === true ? 1 : 0);
		}

		$this->namedtag["playerGameType"] = $this->gamemode;
		$this->namedtag["lastPlayed"] = new LongTag("lastPlayed", floor(microtime(true) * 1000));

		if($this->username != "" and $this->namedtag instanceof CompoundTag){
			$this->server->saveOfflinePlayerData($this->username, $this->namedtag, $async);
		}
	}

	/**
	 * Gets the username
	 *
	 * @return string
	 */
	public function getName(){
		return $this->username;
	}

	/**
	 * @return string
	 */
	public function getLowerCaseName() : string{
		return $this->iusername;
	}

	public function kill(){
		if(!$this->spawned){
			return;
		}

		parent::kill();

		$pk = new RespawnPacket();
		$pos = $this->getSpawn();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$this->dataPacket($pk);
	}

	protected function callDeathEvent(){
		$message = "death.attack.generic";

		$params = [
			$this->getDisplayName()
		];

		$cause = $this->getLastDamageCause();

		switch($cause === null ? EntityDamageEvent::CAUSE_CUSTOM : $cause->getCause()){
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
				if($cause instanceof EntityDamageByEntityEvent){
					$e = $cause->getDamager();
					if($e instanceof Player){
						$message = "death.attack.player";
						$params[] = $e->getDisplayName();
						break;
					}elseif($e instanceof Living){
						$message = "death.attack.mob";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}else{
						$params[] = "Unknown";
					}
				}
				break;
			case EntityDamageEvent::CAUSE_PROJECTILE:
				if($cause instanceof EntityDamageByEntityEvent){
					$e = $cause->getDamager();
					if($e instanceof Player){
						$message = "death.attack.arrow";
						$params[] = $e->getDisplayName();
					}elseif($e instanceof Living){
						$message = "death.attack.arrow";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}else{
						$params[] = "Unknown";
					}
				}
				break;
			case EntityDamageEvent::CAUSE_SUICIDE:
				$message = "death.attack.generic";
				break;
			case EntityDamageEvent::CAUSE_VOID:
				$message = "death.attack.outOfWorld";
				break;
			case EntityDamageEvent::CAUSE_FALL:
				if($cause instanceof EntityDamageEvent){
					if($cause->getFinalDamage() > 2){
						$message = "death.fell.accident.generic";
						break;
					}
				}
				$message = "death.attack.fall";
				break;

			case EntityDamageEvent::CAUSE_SUFFOCATION:
				$message = "death.attack.inWall";
				break;

			case EntityDamageEvent::CAUSE_LAVA:
				$message = "death.attack.lava";
				break;

			case EntityDamageEvent::CAUSE_FIRE:
				$message = "death.attack.onFire";
				break;

			case EntityDamageEvent::CAUSE_FIRE_TICK:
				$message = "death.attack.inFire";
				break;

			case EntityDamageEvent::CAUSE_DROWNING:
				$message = "death.attack.drown";
				break;

			case EntityDamageEvent::CAUSE_CONTACT:
				if($cause instanceof EntityDamageByBlockEvent){
					if($cause->getDamager()->getId() === Block::CACTUS){
						$message = "death.attack.cactus";
					}
				}
				break;

			case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
			case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
				if($cause instanceof EntityDamageByEntityEvent){
					$e = $cause->getDamager();
					if($e instanceof Player){
						$message = "death.attack.explosion.player";
						$params[] = $e->getDisplayName();
					}elseif($e instanceof Living){
						$message = "death.attack.explosion.player";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}
				}else{
					$message = "death.attack.explosion";
				}
				break;

			case EntityDamageEvent::CAUSE_MAGIC:
				$message = "death.attack.magic";
				break;

			case EntityDamageEvent::CAUSE_CUSTOM:
				break;

			default:
				break;
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerDeathEvent($this, $this->getDrops(), new TranslationContainer($message, $params)));

		if(!$ev->getKeepInventory()){
			foreach($ev->getDrops() as $item){
				$this->level->dropItem($this, $item);
			}

			if($this->inventory !== null){
				$this->inventory->clearAll();
				$this->inventory->setHeldItemIndex(0);
				$this->inventory->resetHotbar(true);
			}
		}

		if($ev->getDeathMessage() != ""){
			$this->server->broadcast($ev->getDeathMessage(), Server::BROADCAST_CHANNEL_USERS);
		}
	}

	public function attack($damage, EntityDamageEvent $source){
		if(!$this->isAlive()){
			return;
		}

		if($this->isCreative()
			and $source->getCause() !== EntityDamageEvent::CAUSE_MAGIC
			and $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
			and $source->getCause() !== EntityDamageEvent::CAUSE_VOID
		){
			$source->setCancelled();
		}elseif($this->allowFlight and $source->getCause() === EntityDamageEvent::CAUSE_FALL){
			$source->setCancelled();
		}

		parent::attack($damage, $source);

		if($source->isCancelled()){
			return;
		}elseif($this->getLastDamageCause() === $source and $this->spawned){
			$pk = new EntityEventPacket();
			$pk->eid = $this->id;
			$pk->event = EntityEventPacket::HURT_ANIMATION;
			$this->dataPacket($pk);

			if($this->isSurvival()){
				$this->exhaust(0.3, PlayerExhaustEvent::CAUSE_DAMAGE);
			}
		}
	}

	public function sendPosition(Vector3 $pos, $yaw = null, $pitch = null, $mode = MovePlayerPacket::MODE_NORMAL, array $targets = null){
		$yaw = $yaw === null ? $this->yaw : $yaw;
		$pitch = $pitch === null ? $this->pitch : $pitch;

		$pk = new MovePlayerPacket();
		$pk->eid = $this->getId();
		$pk->x = $pos->x;
		$pk->y = $pos->y + $this->getEyeHeight();
		$pk->z = $pos->z;
		$pk->bodyYaw = $yaw;
		$pk->pitch = $pitch;
		$pk->yaw = $yaw;
		$pk->mode = $mode;

		if($targets !== null){
			$this->server->broadcastPacket($targets, $pk);
		}else{
			$this->dataPacket($pk);
		}

		$this->newPosition = null;
	}

	protected function checkChunks(){
		if($this->chunk === null or ($this->chunk->getX() !== ($this->x >> 4) or $this->chunk->getZ() !== ($this->z >> 4))){
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->chunk = $this->level->getChunk($this->x >> 4, $this->z >> 4, true);

			if(!$this->justCreated){
				$newChunk = $this->level->getChunkPlayers($this->x >> 4, $this->z >> 4);
				unset($newChunk[$this->getLoaderId()]);

				/** @var Player[] $reload */
				$reload = [];
				foreach($this->hasSpawned as $player){
					if(!isset($newChunk[$player->getLoaderId()])){
						$this->despawnFrom($player);
					}else{
						unset($newChunk[$player->getLoaderId()]);
						$reload[] = $player;
					}
				}

				foreach($newChunk as $player){
					$this->spawnTo($player);
				}
			}

			if($this->chunk === null){
				return;
			}

			$this->chunk->addEntity($this);
		}
	}

	protected function checkTeleportPosition(){
		if($this->teleportPosition !== null){
			$chunkX = $this->teleportPosition->x >> 4;
			$chunkZ = $this->teleportPosition->z >> 4;

			for($X = -1; $X <= 1; ++$X){
				for($Z = -1; $Z <= 1; ++$Z){
					if(!isset($this->usedChunks[$index = Level::chunkHash($chunkX + $X, $chunkZ + $Z)]) or $this->usedChunks[$index] === false){
						return false;
					}
				}
			}

			$this->sendPosition($this, null, null, MovePlayerPacket::MODE_RESET);
			$this->spawnToAll();
			$this->forceMovement = $this->teleportPosition;
			$this->teleportPosition = null;

			return true;
		}

		return true;
	}

	/**
	 * @param Vector3|Position|Location $pos
	 * @param float                     $yaw
	 * @param float                     $pitch
	 *
	 * @return bool
	 */
	public function teleport(Vector3 $pos, $yaw = null, $pitch = null){
		if(!$this->isOnline()){
			return false;
		}

		$oldPos = $this->getPosition();
		if(parent::teleport($pos, $yaw, $pitch)){

			foreach($this->windowIndex as $window){
				if($window === $this->inventory){
					continue;
				}
				$this->removeWindow($window);
			}

			$this->teleportPosition = new Vector3($this->x, $this->y, $this->z);

			if(!$this->checkTeleportPosition()){
				$this->forceMovement = $oldPos;
			}else{
				$this->spawnToAll();
			}

			$this->resetFallDistance();
			$this->nextChunkOrderRun = 0;
			$this->newPosition = null;
			$this->stopSleep();
			return true;
		}
		return false;
	}

	/**
	 * This method may not be reliable. Clients don't like to be moved into unloaded chunks.
	 * Use teleport() for a delayed teleport after chunks have been sent.
	 *
	 * @param Vector3 $pos
	 * @param float   $yaw
	 * @param float   $pitch
	 */
	public function teleportImmediate(Vector3 $pos, $yaw = null, $pitch = null){
		if(parent::teleport($pos, $yaw, $pitch)){

			foreach($this->windowIndex as $window){
				if($window === $this->inventory){
					continue;
				}
				$this->removeWindow($window);
			}

			$this->forceMovement = new Vector3($this->x, $this->y, $this->z);
			$this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_RESET);

			$this->resetFallDistance();
			$this->orderChunks();
			$this->nextChunkOrderRun = 0;
			$this->newPosition = null;
		}
	}

	/**
	 * @param Inventory $inventory
	 *
	 * @return int
	 */
	public function getWindowId(Inventory $inventory){
		if($this->windows->contains($inventory)){
			return $this->windows[$inventory];
		}

		return -1;
	}

	/**
	 * Returns the created/existing window id
	 *
	 * @param Inventory $inventory
	 * @param int       $forceId
	 *
	 * @return int
	 */
	public function addWindow(Inventory $inventory, $forceId = null){
		if($this->windows->contains($inventory)){
			return $this->windows[$inventory];
		}

		if($forceId === null){
			$this->windowCnt = $cnt = max(2, ++$this->windowCnt % 99);
		}else{
			$cnt = (int) $forceId;
		}
		$this->windowIndex[$cnt] = $inventory;
		$this->windows->attach($inventory, $cnt);
		if($inventory->open($this)){
			return $cnt;
		}else{
			$this->removeWindow($inventory);

			return -1;
		}
	}

	public function removeWindow(Inventory $inventory){
		$inventory->close($this);
		if($this->windows->contains($inventory)){
			$id = $this->windows[$inventory];
			$this->windows->detach($this->windowIndex[$id]);
			unset($this->windowIndex[$id]);
		}
	}

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}

	public function onChunkChanged(Chunk $chunk){
		if(isset($this->usedChunks[$hash = Level::chunkHash($chunk->getX(), $chunk->getZ())])){
			$this->usedChunks[$hash] = false;
		}
	}

	public function onChunkLoaded(Chunk $chunk){

	}

	public function onChunkPopulated(Chunk $chunk){

	}

	public function onChunkUnloaded(Chunk $chunk){

	}

	public function onBlockChanged(Vector3 $block){

	}

	public function getLoaderId(){
		return $this->loaderId;
	}

	public function isLoaderActive(){
		return $this->isConnected();
	}
}
