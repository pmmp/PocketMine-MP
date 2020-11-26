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

namespace pocketmine\network\mcpe;

use Ds\Set;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\Attribute;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\form\Form;
use pocketmine\math\Vector3;
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\cache\ChunkCache;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\compression\DecompressionException;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\encryption\DecryptionException;
use pocketmine\network\mcpe\encryption\EncryptionContext;
use pocketmine\network\mcpe\encryption\PrepareEncryptionTask;
use pocketmine\network\mcpe\handler\DeathPacketHandler;
use pocketmine\network\mcpe\handler\HandshakePacketHandler;
use pocketmine\network\mcpe\handler\InGamePacketHandler;
use pocketmine\network\mcpe\handler\LoginPacketHandler;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\handler\PreSpawnPacketHandler;
use pocketmine\network\mcpe\handler\ResourcePacksPacketHandler;
use pocketmine\network\mcpe\handler\SpawnResponsePacketHandler;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\GarbageServerboundPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkChunkPublisherUpdatePacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\NetworkSessionManager;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\player\XboxLivePlayerInfo;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\world\Position;
use function array_map;
use function array_values;
use function base64_encode;
use function bin2hex;
use function count;
use function get_class;
use function in_array;
use function json_encode;
use function json_last_error_msg;
use function strlen;
use function strtolower;
use function substr;
use function time;
use function ucfirst;

class NetworkSession{
	/** @var \PrefixedLogger */
	private $logger;
	/** @var Server */
	private $server;
	/** @var Player|null */
	private $player = null;
	/** @var NetworkSessionManager */
	private $manager;
	/** @var string */
	private $ip;
	/** @var int */
	private $port;
	/** @var PlayerInfo */
	private $info;
	/** @var int|null */
	private $ping = null;

	/** @var PacketHandler|null */
	private $handler = null;

	/** @var bool */
	private $connected = true;
	/** @var bool */
	private $disconnectGuard = false;
	/** @var bool */
	private $loggedIn = false;
	/** @var bool */
	private $authenticated = false;
	/** @var int */
	private $connectTime;

	/** @var EncryptionContext */
	private $cipher;

	/** @var Packet[] */
	private $sendBuffer = [];

	/**
	 * @var \SplQueue|CompressBatchPromise[]
	 * @phpstan-var \SplQueue<CompressBatchPromise>
	 */
	private $compressedQueue;
	/** @var Compressor */
	private $compressor;
	/** @var bool */
	private $forceAsyncCompression = true;

	/** @var PacketPool */
	private $packetPool;

	/** @var InventoryManager|null */
	private $invManager = null;

	/** @var PacketSender */
	private $sender;

	/**
	 * @var \Closure[]|Set
	 * @phpstan-var Set<\Closure() : void>
	 */
	private $disposeHooks;

	public function __construct(Server $server, NetworkSessionManager $manager, PacketPool $packetPool, PacketSender $sender, Compressor $compressor, string $ip, int $port){
		$this->server = $server;
		$this->manager = $manager;
		$this->sender = $sender;
		$this->ip = $ip;
		$this->port = $port;

		$this->logger = new \PrefixedLogger($this->server->getLogger(), $this->getLogPrefix());

		$this->compressedQueue = new \SplQueue();
		$this->compressor = $compressor;
		$this->packetPool = $packetPool;

		$this->disposeHooks = new Set();

		$this->connectTime = time();

		$this->setHandler(new LoginPacketHandler(
			$this->server,
			$this,
			function(PlayerInfo $info) : void{
				$this->info = $info;
				$this->logger->info("Player: " . TextFormat::AQUA . $info->getUsername() . TextFormat::RESET);
				$this->logger->setPrefix($this->getLogPrefix());
			},
			function(bool $isAuthenticated, bool $authRequired, ?string $error, ?PublicKeyInterface $clientPubKey) : void{
				$this->setAuthenticationStatus($isAuthenticated, $authRequired, $error, $clientPubKey);
			}
		));

		$this->manager->add($this);
		$this->logger->info("Session opened");
	}

	private function getLogPrefix() : string{
		return "NetworkSession: " . $this->getDisplayName();
	}

	public function getLogger() : \Logger{
		return $this->logger;
	}

	protected function createPlayer() : void{
		$ev = new PlayerCreationEvent($this);
		$ev->call();
		$class = $ev->getPlayerClass();

		//TODO: make this async
		//TODO: this really has no business being in NetworkSession at all - what about allowing it to be provided by PlayerCreationEvent?
		$namedtag = $this->server->getOfflinePlayerData($this->info->getUsername());

		/** @see Player::__construct() */
		$this->player = new $class($this->server, $this, $this->info, $this->authenticated, $namedtag);

		$this->invManager = new InventoryManager($this->player, $this);

		$effectManager = $this->player->getEffects();
		$effectManager->getEffectAddHooks()->add($effectAddHook = function(EffectInstance $effect, bool $replacesOldEffect) : void{
			$this->onEntityEffectAdded($this->player, $effect, $replacesOldEffect);
		});
		$effectManager->getEffectRemoveHooks()->add($effectRemoveHook = function(EffectInstance $effect) : void{
			$this->onEntityEffectRemoved($this->player, $effect);
		});
		$this->disposeHooks->add(static function() use ($effectManager, $effectAddHook, $effectRemoveHook) : void{
			$effectManager->getEffectAddHooks()->remove($effectAddHook);
			$effectManager->getEffectRemoveHooks()->remove($effectRemoveHook);
		});
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function getPlayerInfo() : ?PlayerInfo{
		return $this->info;
	}

	public function isConnected() : bool{
		return $this->connected && !$this->disconnectGuard;
	}

	public function getIp() : string{
		return $this->ip;
	}

	public function getPort() : int{
		return $this->port;
	}

	public function getDisplayName() : string{
		return $this->info !== null ? $this->info->getUsername() : $this->ip . " " . $this->port;
	}

	/**
	 * Returns the last recorded ping measurement for this session, in milliseconds, or null if a ping measurement has not yet been recorded.
	 */
	public function getPing() : ?int{
		return $this->ping;
	}

	/**
	 * @internal Called by the network interface to update last recorded ping measurements.
	 */
	public function updatePing(int $ping) : void{
		$this->ping = $ping;
	}

	public function getHandler() : ?PacketHandler{
		return $this->handler;
	}

	public function setHandler(?PacketHandler $handler) : void{
		if($this->connected){ //TODO: this is fine since we can't handle anything from a disconnected session, but it might produce surprises in some cases
			$this->handler = $handler;
			if($this->handler !== null){
				$this->handler->setUp();
			}
		}
	}

	/**
	 * @throws BadPacketException
	 */
	public function handleEncoded(string $payload) : void{
		if(!$this->connected){
			return;
		}

		if($this->cipher !== null){
			Timings::$playerNetworkReceiveDecryptTimer->startTiming();
			try{
				$payload = $this->cipher->decrypt($payload);
			}catch(DecryptionException $e){
				$this->logger->debug("Encrypted packet: " . base64_encode($payload));
				throw BadPacketException::wrap($e, "Packet decryption error");
			}finally{
				Timings::$playerNetworkReceiveDecryptTimer->stopTiming();
			}
		}

		Timings::$playerNetworkReceiveDecompressTimer->startTiming();
		try{
			$stream = new PacketBatch($this->compressor->decompress($payload));
		}catch(DecompressionException $e){
			$this->logger->debug("Failed to decompress packet: " . base64_encode($payload));
			throw BadPacketException::wrap($e, "Compressed packet batch decode error");
		}finally{
			Timings::$playerNetworkReceiveDecompressTimer->stopTiming();
		}

		try{
			foreach($stream->getPackets($this->packetPool, 500) as $packet){
				try{
					$this->handleDataPacket($packet);
				}catch(BadPacketException $e){
					$this->logger->debug($packet->getName() . ": " . base64_encode($packet->getSerializer()->getBuffer()));
					throw BadPacketException::wrap($e, "Error processing " . $packet->getName());
				}
			}
		}catch(PacketDecodeException $e){
			$this->logger->logException($e);
			throw BadPacketException::wrap($e, "Packet batch decode error");
		}
	}

	/**
	 * @throws BadPacketException
	 */
	public function handleDataPacket(Packet $packet) : void{
		if(!($packet instanceof ServerboundPacket)){
			if($packet instanceof GarbageServerboundPacket){
				$this->logger->debug("Garbage serverbound " . $packet->getName() . ": " . base64_encode($packet->getSerializer()->getBuffer()));
				return;
			}
			throw new BadPacketException("Unexpected non-serverbound packet");
		}

		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

		try{
			try{
				$packet->decode();
			}catch(PacketDecodeException $e){
				throw BadPacketException::wrap($e);
			}
			$stream = $packet->getSerializer();
			if(!$stream->feof()){
				$remains = substr($stream->getBuffer(), $stream->getOffset());
				$this->logger->debug("Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": " . bin2hex($remains));
			}

			$ev = new DataPacketReceiveEvent($this, $packet);
			$ev->call();
			if(!$ev->isCancelled() and ($this->handler === null or !$packet->handle($this->handler))){
				$this->logger->debug("Unhandled " . $packet->getName() . ": " . base64_encode($stream->getBuffer()));
			}
		}finally{
			$timings->stopTiming();
		}
	}

	public function sendDataPacket(ClientboundPacket $packet, bool $immediate = false) : bool{
		//Basic safety restriction. TODO: improve this
		if(!$this->loggedIn and !$packet->canBeSentBeforeLogin()){
			throw new \InvalidArgumentException("Attempted to send " . get_class($packet) . " to " . $this->getDisplayName() . " too early");
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try{
			$ev = new DataPacketSendEvent([$this], [$packet]);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}

			$this->addToSendBuffer($packet);
			if($immediate){
				$this->flushSendBuffer(true);
			}

			return true;
		}finally{
			$timings->stopTiming();
		}
	}

	/**
	 * @internal
	 */
	public function addToSendBuffer(ClientboundPacket $packet) : void{
		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try{
			$this->sendBuffer[] = $packet;
			$this->manager->scheduleUpdate($this); //schedule flush at end of tick
		}finally{
			$timings->stopTiming();
		}
	}

	private function flushSendBuffer(bool $immediate = false) : void{
		if(count($this->sendBuffer) > 0){
			$syncMode = null; //automatic
			if($immediate){
				$syncMode = true;
			}elseif($this->forceAsyncCompression){
				$syncMode = false;
			}
			$promise = $this->server->prepareBatch(PacketBatch::fromPackets(...$this->sendBuffer), $this->compressor, $syncMode);
			$this->sendBuffer = [];
			$this->queueCompressedNoBufferFlush($promise, $immediate);
		}
	}

	public function getCompressor() : Compressor{
		return $this->compressor;
	}

	public function queueCompressed(CompressBatchPromise $payload, bool $immediate = false) : void{
		$this->flushSendBuffer($immediate); //Maintain ordering if possible
		$this->queueCompressedNoBufferFlush($payload, $immediate);
	}

	private function queueCompressedNoBufferFlush(CompressBatchPromise $payload, bool $immediate = false) : void{
		if($immediate){
			//Skips all queues
			$this->sendEncoded($payload->getResult(), true);
		}else{
			$this->compressedQueue->enqueue($payload);
			$payload->onResolve(function(CompressBatchPromise $payload) : void{
				if($this->connected and $this->compressedQueue->bottom() === $payload){
					$this->compressedQueue->dequeue(); //result unused
					$this->sendEncoded($payload->getResult());

					while(!$this->compressedQueue->isEmpty()){
						/** @var CompressBatchPromise $current */
						$current = $this->compressedQueue->bottom();
						if($current->hasResult()){
							$this->compressedQueue->dequeue();

							$this->sendEncoded($current->getResult());
						}else{
							//can't send any more queued until this one is ready
							break;
						}
					}
				}
			});
		}
	}

	private function sendEncoded(string $payload, bool $immediate = false) : void{
		if($this->cipher !== null){
			Timings::$playerNetworkSendEncryptTimer->startTiming();
			$payload = $this->cipher->encrypt($payload);
			Timings::$playerNetworkSendEncryptTimer->stopTiming();
		}
		$this->sender->send($payload, $immediate);
	}

	/**
	 * @phpstan-param \Closure() : void $func
	 */
	private function tryDisconnect(\Closure $func, string $reason) : void{
		if($this->connected and !$this->disconnectGuard){
			$this->disconnectGuard = true;
			$func();
			$this->disconnectGuard = false;
			foreach($this->disposeHooks as $callback){
				$callback();
			}
			$this->disposeHooks->clear();
			$this->setHandler(null);
			$this->connected = false;
			$this->manager->remove($this);
			$this->logger->info("Session closed due to $reason");

			$this->invManager = null; //break cycles - TODO: this really ought to be deferred until it's safe
		}
	}

	/**
	 * Disconnects the session, destroying the associated player (if it exists).
	 */
	public function disconnect(string $reason, bool $notify = true) : void{
		$this->tryDisconnect(function() use ($reason, $notify) : void{
			if($this->player !== null){
				$this->player->onPostDisconnect($reason, null);
			}
			$this->doServerDisconnect($reason, $notify);
		}, $reason);
	}

	/**
	 * Instructs the remote client to connect to a different server.
	 *
	 * @throws \UnsupportedOperationException
	 */
	public function transfer(string $ip, int $port, string $reason = "transfer") : void{
		$this->tryDisconnect(function() use ($ip, $port, $reason) : void{
			$this->sendDataPacket(TransferPacket::create($ip, $port), true);
			if($this->player !== null){
				$this->player->onPostDisconnect($reason, null);
			}
			$this->doServerDisconnect($reason, false);
		}, $reason);
	}

	/**
	 * Called by the Player when it is closed (for example due to getting kicked).
	 */
	public function onPlayerDestroyed(string $reason) : void{
		$this->tryDisconnect(function() use ($reason) : void{
			$this->doServerDisconnect($reason, true);
		}, $reason);
	}

	/**
	 * Internal helper function used to handle server disconnections.
	 */
	private function doServerDisconnect(string $reason, bool $notify = true) : void{
		if($notify){
			$this->sendDataPacket($reason === "" ? DisconnectPacket::silent() : DisconnectPacket::message($reason), true);
		}

		$this->sender->close($notify ? $reason : "");
	}

	/**
	 * Called by the network interface to close the session when the client disconnects without server input, for
	 * example in a timeout condition or voluntary client disconnect.
	 */
	public function onClientDisconnect(string $reason) : void{
		$this->tryDisconnect(function() use ($reason) : void{
			if($this->player !== null){
				$this->player->onPostDisconnect($reason, null);
			}
		}, $reason);
	}

	private function setAuthenticationStatus(bool $authenticated, bool $authRequired, ?string $error, ?PublicKeyInterface $clientPubKey) : void{
		if(!$this->connected){
			return;
		}
		if($error === null){
			if($authenticated and !($this->info instanceof XboxLivePlayerInfo)){
				$error = "Expected XUID but none found";
			}elseif($clientPubKey === null){
				$error = "Missing client public key"; //failsafe
			}
		}

		if($error !== null){
			$this->disconnect($this->server->getLanguage()->translateString("pocketmine.disconnect.invalidSession", [$error]));

			return;
		}

		$this->authenticated = $authenticated;

		if(!$this->authenticated){
			if($authRequired){
				$this->disconnect("disconnectionScreen.notAuthenticated");
				return;
			}
			if($this->info instanceof XboxLivePlayerInfo){
				$this->logger->warning("Discarding unexpected XUID for non-authenticated player");
				$this->info = $this->info->withoutXboxData();
			}
		}
		$this->logger->debug("Xbox Live authenticated: " . ($this->authenticated ? "YES" : "NO"));

		if($this->manager->kickDuplicates($this)){
			if(EncryptionContext::$ENABLED){
				$this->server->getAsyncPool()->submitTask(new PrepareEncryptionTask($clientPubKey, function(string $encryptionKey, string $handshakeJwt) : void{
					if(!$this->connected){
						return;
					}
					$this->sendDataPacket(ServerToClientHandshakePacket::create($handshakeJwt), true); //make sure this gets sent before encryption is enabled

					$this->cipher = new EncryptionContext($encryptionKey);

					$this->setHandler(new HandshakePacketHandler(function() : void{
						$this->onServerLoginSuccess();
					}));
					$this->logger->debug("Enabled encryption");
				}));
			}else{
				$this->onServerLoginSuccess();
			}
		}
	}

	private function onServerLoginSuccess() : void{
		$this->loggedIn = true;

		$this->sendDataPacket(PlayStatusPacket::create(PlayStatusPacket::LOGIN_SUCCESS));

		$this->logger->debug("Initiating resource packs phase");
		$this->setHandler(new ResourcePacksPacketHandler($this, $this->server->getResourcePackManager(), function() : void{
			$this->beginSpawnSequence();
		}));
	}

	private function beginSpawnSequence() : void{
		$this->createPlayer();

		$this->setHandler(new PreSpawnPacketHandler($this->server, $this->player, $this));
		$this->player->setImmobile(); //TODO: HACK: fix client-side falling pre-spawn

		$this->logger->debug("Waiting for spawn chunks");
	}

	public function notifyTerrainReady() : void{
		$this->logger->debug("Sending spawn notification, waiting for spawn response");
		$this->sendDataPacket(PlayStatusPacket::create(PlayStatusPacket::PLAYER_SPAWN));
		$this->setHandler(new SpawnResponsePacketHandler(function() : void{
			$this->onClientSpawnResponse();
		}));
	}

	private function onClientSpawnResponse() : void{
		$this->logger->debug("Received spawn response, entering in-game phase");
		$this->player->setImmobile(false); //TODO: HACK: we set this during the spawn sequence to prevent the client sending junk movements
		$this->player->doFirstSpawn();
		$this->forceAsyncCompression = false;
		$this->setHandler(new InGamePacketHandler($this->player, $this));
	}

	public function onServerDeath() : void{
		if($this->handler instanceof InGamePacketHandler){ //TODO: this is a bad fix for pre-spawn death, this shouldn't be reachable at all at this stage :(
			$this->setHandler(new DeathPacketHandler($this->player, $this));
		}
	}

	public function onServerRespawn() : void{
		$this->player->sendData(null);

		$this->syncAdventureSettings($this->player);
		$this->invManager->syncAll();
		$this->setHandler(new InGamePacketHandler($this->player, $this));
	}

	public function syncMovement(Vector3 $pos, ?float $yaw = null, ?float $pitch = null, int $mode = MovePlayerPacket::MODE_NORMAL) : void{
		if($this->player !== null){
			$location = $this->player->getLocation();
			$yaw = $yaw ?? $location->getYaw();
			$pitch = $pitch ?? $location->getPitch();

			$pk = new MovePlayerPacket();
			$pk->entityRuntimeId = $this->player->getId();
			$pk->position = $this->player->getOffsetPosition($pos);
			$pk->pitch = $pitch;
			$pk->headYaw = $yaw;
			$pk->yaw = $yaw;
			$pk->mode = $mode;
			$pk->onGround = $this->player->onGround;

			$this->sendDataPacket($pk);
		}
	}

	public function syncViewAreaRadius(int $distance) : void{
		$this->sendDataPacket(ChunkRadiusUpdatedPacket::create($distance));
	}

	public function syncViewAreaCenterPoint(Vector3 $newPos, int $viewDistance) : void{
		$this->sendDataPacket(NetworkChunkPublisherUpdatePacket::create($newPos->getFloorX(), $newPos->getFloorY(), $newPos->getFloorZ(), $viewDistance * 16)); //blocks, not chunks >.>
	}

	public function syncPlayerSpawnPoint(Position $newSpawn) : void{
		[$x, $y, $z] = [$newSpawn->getFloorX(), $newSpawn->getFloorY(), $newSpawn->getFloorZ()];
		$this->sendDataPacket(SetSpawnPositionPacket::playerSpawn($x, $y, $z, DimensionIds::OVERWORLD, $x, $y, $z));
	}

	public function syncGameMode(GameMode $mode, bool $isRollback = false) : void{
		$this->sendDataPacket(SetPlayerGameTypePacket::create(TypeConverter::getInstance()->coreGameModeToProtocol($mode)));
		$this->syncAdventureSettings($this->player);
		if(!$isRollback){
			$this->invManager->syncCreative();
		}
	}

	/**
	 * TODO: make this less specialized
	 */
	public function syncAdventureSettings(Player $for) : void{
		$pk = new AdventureSettingsPacket();

		$pk->setFlag(AdventureSettingsPacket::WORLD_IMMUTABLE, $for->isSpectator());
		$pk->setFlag(AdventureSettingsPacket::NO_PVP, $for->isSpectator());
		$pk->setFlag(AdventureSettingsPacket::AUTO_JUMP, $for->hasAutoJump());
		$pk->setFlag(AdventureSettingsPacket::ALLOW_FLIGHT, $for->getAllowFlight());
		$pk->setFlag(AdventureSettingsPacket::NO_CLIP, $for->isSpectator());
		$pk->setFlag(AdventureSettingsPacket::FLYING, $for->isFlying());

		//TODO: permission flags

		$pk->commandPermission = ($for->isOp() ? AdventureSettingsPacket::PERMISSION_OPERATOR : AdventureSettingsPacket::PERMISSION_NORMAL);
		$pk->playerPermission = ($for->isOp() ? PlayerPermissions::OPERATOR : PlayerPermissions::MEMBER);
		$pk->entityUniqueId = $for->getId();

		$this->sendDataPacket($pk);
	}

	/**
	 * @param Attribute[] $attributes
	 */
	public function syncAttributes(Living $entity, array $attributes) : void{
		if(count($attributes) > 0){
			$this->sendDataPacket(UpdateAttributesPacket::create($entity->getId(), array_map(function(Attribute $attr) : NetworkAttribute{
				return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue());
			}, $attributes), 0));
		}
	}

	/**
	 * @param MetadataProperty[] $properties
	 * @phpstan-param array<int, MetadataProperty> $properties
	 */
	public function syncActorData(Entity $entity, array $properties) : void{
		$this->sendDataPacket(SetActorDataPacket::create($entity->getId(), $properties, 0));
	}

	public function onEntityEffectAdded(Living $entity, EffectInstance $effect, bool $replacesOldEffect) : void{
		//TODO: we may need yet another effect <=> ID map in the future depending on protocol changes
		$this->sendDataPacket(MobEffectPacket::add($entity->getId(), $replacesOldEffect, EffectIdMap::getInstance()->toId($effect->getType()), $effect->getAmplifier(), $effect->isVisible(), $effect->getDuration()));
	}

	public function onEntityEffectRemoved(Living $entity, EffectInstance $effect) : void{
		$this->sendDataPacket(MobEffectPacket::remove($entity->getId(), EffectIdMap::getInstance()->toId($effect->getType())));
	}

	public function onEntityRemoved(Entity $entity) : void{
		$this->sendDataPacket(RemoveActorPacket::create($entity->getId()));
	}

	public function syncAvailableCommands() : void{
		$pk = new AvailableCommandsPacket();
		foreach($this->server->getCommandMap()->getCommands() as $name => $command){
			if(isset($pk->commandData[$command->getName()]) or $command->getName() === "help" or !$command->testPermissionSilent($this->player)){
				continue;
			}

			$lname = strtolower($command->getName());
			$aliases = $command->getAliases();
			$aliasObj = null;
			if(count($aliases) > 0){
				if(!in_array($lname, $aliases, true)){
					//work around a client bug which makes the original name not show when aliases are used
					$aliases[] = $lname;
				}
				$aliasObj = new CommandEnum(ucfirst($command->getName()) . "Aliases", array_values($aliases));
			}

			$data = new CommandData(
				$lname, //TODO: commands containing uppercase letters in the name crash 1.9.0 client
				$this->player->getLanguage()->translateString($command->getDescription()),
				0,
				0,
				$aliasObj,
				[
					[CommandParameter::standard("args", AvailableCommandsPacket::ARG_TYPE_RAWTEXT, 0, true)]
				]
			);

			$pk->commandData[$command->getName()] = $data;
		}

		$this->sendDataPacket($pk);
	}

	public function onRawChatMessage(string $message) : void{
		$this->sendDataPacket(TextPacket::raw($message));
	}

	/**
	 * @param string[] $parameters
	 */
	public function onTranslatedChatMessage(string $key, array $parameters) : void{
		$this->sendDataPacket(TextPacket::translation($key, $parameters));
	}

	/**
	 * @param string[] $parameters
	 */
	public function onJukeboxPopup(string $key, array $parameters) : void{
		$this->sendDataPacket(TextPacket::jukeboxPopup($key, $parameters));
	}

	public function onPopup(string $message) : void{
		$this->sendDataPacket(TextPacket::popup($message));
	}

	public function onTip(string $message) : void{
		$this->sendDataPacket(TextPacket::tip($message));
	}

	public function onFormSent(int $id, Form $form) : bool{
		$formData = json_encode($form);
		if($formData === false){
			throw new \InvalidArgumentException("Failed to encode form JSON: " . json_last_error_msg());
		}
		return $this->sendDataPacket(ModalFormRequestPacket::create($id, $formData));
	}

	/**
	 * Instructs the networksession to start using the chunk at the given coordinates. This may occur asynchronously.
	 * @param \Closure $onCompletion To be called when chunk sending has completed.
	 * @phpstan-param \Closure(int $chunkX, int $chunkZ) : void $onCompletion
	 */
	public function startUsingChunk(int $chunkX, int $chunkZ, \Closure $onCompletion) : void{
		Utils::validateCallableSignature(function(int $chunkX, int $chunkZ) : void{}, $onCompletion);

		$world = $this->player->getLocation()->getWorld();
		ChunkCache::getInstance($world, $this->compressor)->request($chunkX, $chunkZ)->onResolve(

			//this callback may be called synchronously or asynchronously, depending on whether the promise is resolved yet
			function(CompressBatchPromise $promise) use ($world, $chunkX, $chunkZ, $onCompletion) : void{
				if(!$this->isConnected()){
					return;
				}
				$currentWorld = $this->player->getLocation()->getWorld();
				if($world !== $currentWorld or !$this->player->isUsingChunk($chunkX, $chunkZ)){
					$this->logger->debug("Tried to send no-longer-active chunk $chunkX $chunkZ in world " . $world->getFolderName());
					return;
				}
				$currentWorld->timings->syncChunkSendTimer->startTiming();
				try{
					$this->queueCompressed($promise);
					$onCompletion($chunkX, $chunkZ);
				}finally{
					$currentWorld->timings->syncChunkSendTimer->stopTiming();
				}
			}
		);
	}

	public function stopUsingChunk(int $chunkX, int $chunkZ) : void{

	}

	public function onEnterWorld() : void{
		$world = $this->player->getWorld();
		$this->syncWorldTime($world->getTime());
		$this->syncWorldDifficulty($world->getDifficulty());
		//TODO: weather needs to be synced here (when implemented)
		//TODO: world spawn needs to be synced here
	}

	public function syncWorldTime(int $worldTime) : void{
		$this->sendDataPacket(SetTimePacket::create($worldTime));
	}

	public function syncWorldDifficulty(int $worldDifficulty) : void{
		$this->sendDataPacket(SetDifficultyPacket::create($worldDifficulty));
	}

	public function getInvManager() : InventoryManager{
		return $this->invManager;
	}

	/**
	 * TODO: expand this to more than just humans
	 * TODO: offhand
	 */
	public function onMobEquipmentChange(Human $mob) : void{
		//TODO: we could send zero for slot here because remote players don't need to know which slot was selected
		$inv = $mob->getInventory();
		$this->sendDataPacket(MobEquipmentPacket::create($mob->getId(), TypeConverter::getInstance()->coreItemStackToNet($inv->getItemInHand()), $inv->getHeldItemIndex(), ContainerIds::INVENTORY));
	}

	public function onMobArmorChange(Living $mob) : void{
		$inv = $mob->getArmorInventory();
		$converter = TypeConverter::getInstance();
		$this->sendDataPacket(MobArmorEquipmentPacket::create(
			$mob->getId(),
			$converter->coreItemStackToNet($inv->getHelmet()),
			$converter->coreItemStackToNet($inv->getChestplate()),
			$converter->coreItemStackToNet($inv->getLeggings()),
			$converter->coreItemStackToNet($inv->getBoots())
		));
	}

	public function onPlayerPickUpItem(Player $collector, Entity $pickedUp) : void{
		$this->sendDataPacket(TakeItemActorPacket::create($collector->getId(), $pickedUp->getId()));
	}

	/**
	 * @param Player[] $players
	 */
	public function syncPlayerList(array $players) : void{
		$this->sendDataPacket(PlayerListPacket::add(array_map(function(Player $player) : PlayerListEntry{
			return PlayerListEntry::createAdditionEntry($player->getUniqueId(), $player->getId(), $player->getDisplayName(), SkinAdapterSingleton::get()->toSkinData($player->getSkin()), $player->getXuid());
		}, $players)));
	}

	public function onPlayerAdded(Player $p) : void{
		$this->sendDataPacket(PlayerListPacket::add([PlayerListEntry::createAdditionEntry($p->getUniqueId(), $p->getId(), $p->getDisplayName(), SkinAdapterSingleton::get()->toSkinData($p->getSkin()), $p->getXuid())]));
	}

	public function onPlayerRemoved(Player $p) : void{
		if($p !== $this->player){
			$this->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($p->getUniqueId())]));
		}
	}

	public function onTitle(string $title) : void{
		$this->sendDataPacket(SetTitlePacket::title($title));
	}

	public function onSubTitle(string $subtitle) : void{
		$this->sendDataPacket(SetTitlePacket::subtitle($subtitle));
	}

	public function onActionBar(string $actionBar) : void{
		$this->sendDataPacket(SetTitlePacket::actionBarMessage($actionBar));
	}

	public function onClearTitle() : void{
		$this->sendDataPacket(SetTitlePacket::clearTitle());
	}

	public function onResetTitleOptions() : void{
		$this->sendDataPacket(SetTitlePacket::resetTitleOptions());
	}

	public function onTitleDuration(int $fadeIn, int $stay, int $fadeOut) : void{
		$this->sendDataPacket(SetTitlePacket::setAnimationTimes($fadeIn, $stay, $fadeOut));
	}

	public function tick() : bool{
		if($this->info === null){
			if(time() >= $this->connectTime + 10){
				$this->disconnect("Login timeout");
				return false;
			}

			return true; //keep ticking until timeout
		}

		if($this->player !== null){
			$this->player->doChunkRequests();

			$dirtyAttributes = $this->player->getAttributeMap()->needSend();
			$this->syncAttributes($this->player, $dirtyAttributes);
			foreach($dirtyAttributes as $attribute){
				//TODO: we might need to send these to other players in the future
				//if that happens, this will need to become more complex than a flag on the attribute itself
				$attribute->markSynchronized();
			}
		}

		$this->flushSendBuffer();

		return true;
	}
}
