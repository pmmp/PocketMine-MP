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

use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\form\Form;
use pocketmine\math\Vector3;
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\Zlib;
use pocketmine\network\mcpe\encryption\NetworkCipher;
use pocketmine\network\mcpe\encryption\PrepareEncryptionTask;
use pocketmine\network\mcpe\handler\DeathPacketHandler;
use pocketmine\network\mcpe\handler\HandshakePacketHandler;
use pocketmine\network\mcpe\handler\InGamePacketHandler;
use pocketmine\network\mcpe\handler\LoginPacketHandler;
use pocketmine\network\mcpe\handler\NullPacketHandler;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\handler\PreSpawnPacketHandler;
use pocketmine\network\mcpe\handler\ResourcePacksPacketHandler;
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
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\NetworkSessionManager;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\world\Position;
use function array_map;
use function assert;
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
	/** @var int */
	private $ping;

	/** @var PacketHandler */
	private $handler;

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

	/** @var NetworkCipher */
	private $cipher;

	/** @var PacketBatch|null */
	private $sendBuffer;

	/** @var \SplQueue|CompressBatchPromise[] */
	private $compressedQueue;

	/** @var InventoryManager|null */
	private $invManager = null;

	/** @var PacketSender */
	private $sender;

	public function __construct(Server $server, NetworkSessionManager $manager, PacketSender $sender, string $ip, int $port){
		$this->server = $server;
		$this->manager = $manager;
		$this->sender = $sender;
		$this->ip = $ip;
		$this->port = $port;

		$this->logger = new \PrefixedLogger($this->server->getLogger(), $this->getLogPrefix());

		$this->compressedQueue = new \SplQueue();

		$this->connectTime = time();

		$this->setHandler(new LoginPacketHandler($this->server, $this));

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

		/**
		 * @var Player $player
		 * @see Player::__construct()
		 */
		$this->player = new $class($this->server, $this, $this->info, $this->authenticated);

		$this->invManager = new InventoryManager($this->player, $this);
		$this->player->getEffects()->onEffectAdd(function(EffectInstance $effect, bool $replacesOldEffect) : void{
			$this->onEntityEffectAdded($this->player, $effect, $replacesOldEffect);
		});
		$this->player->getEffects()->onEffectRemove(function(EffectInstance $effect) : void{
			$this->onEntityEffectRemoved($this->player, $effect);
		});
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function getPlayerInfo() : ?PlayerInfo{
		return $this->info;
	}

	/**
	 * TODO: this shouldn't be accessible after the initial login phase
	 *
	 * @param PlayerInfo $info
	 * @throws \InvalidStateException
	 */
	public function setPlayerInfo(PlayerInfo $info) : void{
		if($this->info !== null){
			throw new \InvalidStateException("Player info has already been set");
		}
		$this->info = $info;
		$this->logger->info("Player: " . TextFormat::AQUA . $info->getUsername() . TextFormat::RESET);
		$this->logger->setPrefix($this->getLogPrefix());
	}

	public function isConnected() : bool{
		return $this->connected;
	}

	/**
	 * @return string
	 */
	public function getIp() : string{
		return $this->ip;
	}

	/**
	 * @return int
	 */
	public function getPort() : int{
		return $this->port;
	}

	public function getDisplayName() : string{
		return $this->info !== null ? $this->info->getUsername() : $this->ip . " " . $this->port;
	}

	/**
	 * Returns the last recorded ping measurement for this session, in milliseconds.
	 *
	 * @return int
	 */
	public function getPing() : int{
		return $this->ping;
	}

	/**
	 * @internal Called by the network interface to update last recorded ping measurements.
	 *
	 * @param int $ping
	 */
	public function updatePing(int $ping) : void{
		$this->ping = $ping;
	}

	public function getHandler() : PacketHandler{
		return $this->handler;
	}

	public function setHandler(PacketHandler $handler) : void{
		if($this->connected){ //TODO: this is fine since we can't handle anything from a disconnected session, but it might produce surprises in some cases
			$this->handler = $handler;
			$this->handler->setUp();
		}
	}

	/**
	 * @param string $payload
	 *
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
			}catch(\UnexpectedValueException $e){
				$this->logger->debug("Encrypted packet: " . base64_encode($payload));
				throw new BadPacketException("Packet decryption error: " . $e->getMessage(), 0, $e);
			}finally{
				Timings::$playerNetworkReceiveDecryptTimer->stopTiming();
			}
		}

		Timings::$playerNetworkReceiveDecompressTimer->startTiming();
		try{
			$stream = new PacketBatch(Zlib::decompress($payload));
		}catch(\ErrorException $e){
			$this->logger->debug("Failed to decompress packet: " . base64_encode($payload));
			//TODO: this isn't incompatible game version if we already established protocol version
			throw new BadPacketException("Compressed packet batch decode error: " . $e->getMessage(), 0, $e);
		}finally{
			Timings::$playerNetworkReceiveDecompressTimer->stopTiming();
		}

		$count = 0;
		while(!$stream->feof() and $this->connected){
			if($count++ >= 500){
				throw new BadPacketException("Too many packets in a single batch");
			}
			try{
				$pk = $stream->getPacket();
			}catch(BinaryDataException $e){
				$this->logger->debug("Packet batch: " . base64_encode($stream->getBuffer()));
				throw new BadPacketException("Packet batch decode error: " . $e->getMessage(), 0, $e);
			}

			try{
				$this->handleDataPacket($pk);
			}catch(BadPacketException $e){
				$this->logger->debug($pk->getName() . ": " . base64_encode($pk->getBuffer()));
				throw new BadPacketException("Error processing " . $pk->getName() . ": " . $e->getMessage(), 0, $e);
			}
		}
	}

	/**
	 * @param Packet $packet
	 *
	 * @throws BadPacketException
	 */
	public function handleDataPacket(Packet $packet) : void{
		if(!($packet instanceof ServerboundPacket)){
			if($packet instanceof GarbageServerboundPacket){
				$this->logger->debug("Garbage serverbound " . $packet->getName() . ": " . base64_encode($packet->getBuffer()));
				return;
			}
			throw new BadPacketException("Unexpected non-serverbound packet");
		}

		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

		try{
			$packet->decode();
			if(!$packet->feof()){
				$remains = substr($packet->getBuffer(), $packet->getOffset());
				$this->logger->debug("Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": " . bin2hex($remains));
			}

			$ev = new DataPacketReceiveEvent($this, $packet);
			$ev->call();
			if(!$ev->isCancelled() and !$packet->handle($this->handler)){
				$this->logger->debug("Unhandled " . $packet->getName() . ": " . base64_encode($packet->getBuffer()));
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
	 * @param ClientboundPacket $packet
	 */
	public function addToSendBuffer(ClientboundPacket $packet) : void{
		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try{
			if($this->sendBuffer === null){
				$this->sendBuffer = new PacketBatch();
			}
			$this->sendBuffer->putPacket($packet);
			$this->manager->scheduleUpdate($this); //schedule flush at end of tick
		}finally{
			$timings->stopTiming();
		}
	}

	private function flushSendBuffer(bool $immediate = false) : void{
		if($this->sendBuffer !== null){
			$promise = $this->server->prepareBatch($this->sendBuffer, $immediate);
			$this->sendBuffer = null;
			$this->queueCompressed($promise, $immediate);
		}
	}

	public function queueCompressed(CompressBatchPromise $payload, bool $immediate = false) : void{
		$this->flushSendBuffer($immediate); //Maintain ordering if possible
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

	private function tryDisconnect(\Closure $func, string $reason) : void{
		if($this->connected and !$this->disconnectGuard){
			$this->disconnectGuard = true;
			$func();
			$this->disconnectGuard = false;
			$this->setHandler(NullPacketHandler::getInstance());
			$this->connected = false;
			$this->manager->remove($this);
			$this->logger->info("Session closed due to $reason");

			$this->invManager = null; //break cycles - TODO: this really ought to be deferred until it's safe
		}
	}

	/**
	 * Disconnects the session, destroying the associated player (if it exists).
	 *
	 * @param string $reason
	 * @param bool   $notify
	 */
	public function disconnect(string $reason, bool $notify = true) : void{
		$this->tryDisconnect(function() use ($reason, $notify){
			if($this->player !== null){
				$this->player->disconnect($reason, null, $notify);
			}
			$this->doServerDisconnect($reason, $notify);
		}, $reason);
	}

	/**
	 * Instructs the remote client to connect to a different server.
	 *
	 * @param string $ip
	 * @param int    $port
	 * @param string $reason
	 *
	 * @throws \UnsupportedOperationException
	 */
	public function transfer(string $ip, int $port, string $reason = "transfer") : void{
		$this->tryDisconnect(function() use ($ip, $port, $reason){
			$this->sendDataPacket(TransferPacket::create($ip, $port), true);
			$this->disconnect($reason, false);
			if($this->player !== null){
				$this->player->disconnect($reason, null, false);
			}
			$this->doServerDisconnect($reason, false);
		}, $reason);
	}

	/**
	 * Called by the Player when it is closed (for example due to getting kicked).
	 *
	 * @param string $reason
	 * @param bool   $notify
	 */
	public function onPlayerDestroyed(string $reason, bool $notify = true) : void{
		$this->tryDisconnect(function() use ($reason, $notify){
			$this->doServerDisconnect($reason, $notify);
		}, $reason);
	}

	/**
	 * Internal helper function used to handle server disconnections.
	 *
	 * @param string $reason
	 * @param bool   $notify
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
	 *
	 * @param string $reason
	 */
	public function onClientDisconnect(string $reason) : void{
		$this->tryDisconnect(function() use ($reason){
			if($this->player !== null){
				$this->player->disconnect($reason, null, false);
			}
		}, $reason);
	}

	public function setAuthenticationStatus(bool $authenticated, bool $authRequired, ?string $error, ?PublicKeyInterface $clientPubKey) : void{
		if(!$this->connected){
			return;
		}
		if($error === null){
			if($authenticated and $this->info->getXuid() === ""){
				$error = "Expected XUID but none found";
			}elseif(!$authenticated and $this->info->getXuid() !== ""){
				$error = "Unexpected XUID for non-XBOX-authenticated player";
			}elseif($clientPubKey === null){
				$error = "Missing client public key"; //failsafe
			}
		}

		if($error !== null){
			$this->disconnect($this->server->getLanguage()->translateString("pocketmine.disconnect.invalidSession", [$error]));

			return;
		}

		$this->authenticated = $authenticated;

		if(!$this->authenticated and $authRequired){
			$this->disconnect("disconnectionScreen.notAuthenticated");
			return;
		}
		$this->logger->debug("Xbox Live authenticated: " . ($this->authenticated ? "YES" : "NO"));

		if($this->manager->kickDuplicates($this)){
			if(NetworkCipher::$ENABLED){
				$this->server->getAsyncPool()->submitTask(new PrepareEncryptionTask($this, $clientPubKey));
			}else{
				$this->onLoginSuccess();
			}
		}
	}

	public function enableEncryption(string $encryptionKey, string $handshakeJwt) : void{
		if(!$this->connected){
			return;
		}
		$this->sendDataPacket(ServerToClientHandshakePacket::create($handshakeJwt), true); //make sure this gets sent before encryption is enabled

		$this->cipher = new NetworkCipher($encryptionKey);

		$this->setHandler(new HandshakePacketHandler($this));
		$this->logger->debug("Enabled encryption");
	}

	public function onLoginSuccess() : void{
		$this->loggedIn = true;

		$this->sendDataPacket(PlayStatusPacket::create(PlayStatusPacket::LOGIN_SUCCESS));

		$this->logger->debug("Initiating resource packs phase");
		$this->setHandler(new ResourcePacksPacketHandler($this, $this->server->getResourcePackManager()));
	}

	public function onResourcePacksDone() : void{
		$this->createPlayer();

		$this->setHandler(new PreSpawnPacketHandler($this->server, $this->player, $this));
		$this->logger->debug("Waiting for spawn chunks");
	}

	public function onTerrainReady() : void{
		$this->logger->debug("Sending spawn notification, waiting for spawn response");
		$this->sendDataPacket(PlayStatusPacket::create(PlayStatusPacket::PLAYER_SPAWN));
	}

	public function onSpawn() : void{
		$this->logger->debug("Received spawn response, entering in-game phase");
		$this->player->doFirstSpawn();
		$this->setHandler(new InGamePacketHandler($this->player, $this));
	}

	public function onDeath() : void{
		$this->setHandler(new DeathPacketHandler($this->player, $this));
	}

	public function onRespawn() : void{
		$this->player->sendData($this->player);
		$this->player->sendData($this->player->getViewers());

		$this->syncAdventureSettings($this->player);
		$this->invManager->syncAll();
		$this->setHandler(new InGamePacketHandler($this->player, $this));
	}

	public function syncMovement(Vector3 $pos, ?float $yaw = null, ?float $pitch = null, int $mode = MovePlayerPacket::MODE_NORMAL) : void{
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

		$this->sendDataPacket($pk);
	}

	public function syncViewAreaRadius(int $distance) : void{
		$this->sendDataPacket(ChunkRadiusUpdatedPacket::create($distance));
	}

	public function syncViewAreaCenterPoint(Vector3 $newPos, int $viewDistance) : void{
		$this->sendDataPacket(NetworkChunkPublisherUpdatePacket::create($newPos->getFloorX(), $newPos->getFloorY(), $newPos->getFloorZ(), $viewDistance * 16)); //blocks, not chunks >.>
	}

	public function syncPlayerSpawnPoint(Position $newSpawn) : void{
		$this->sendDataPacket(SetSpawnPositionPacket::playerSpawn($newSpawn->getFloorX(), $newSpawn->getFloorY(), $newSpawn->getFloorZ(), false)); //TODO: spawn forced
	}

	public function syncGameMode(GameMode $mode, bool $isRollback = false) : void{
		$this->sendDataPacket(SetPlayerGameTypePacket::create(self::getClientFriendlyGamemode($mode)));
		$this->syncAdventureSettings($this->player);
		if(!$isRollback){
			$this->invManager->syncCreative();
		}
	}

	/**
	 * TODO: make this less specialized
	 *
	 * @param Player $for
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

	public function syncAttributes(Living $entity, bool $sendAll = false){
		$entries = $sendAll ? $entity->getAttributeMap()->getAll() : $entity->getAttributeMap()->needSend();
		if(count($entries) > 0){
			$this->sendDataPacket(UpdateAttributesPacket::create($entity->getId(), $entries));
			foreach($entries as $entry){
				$entry->markSynchronized();
			}
		}
	}

	public function onEntityEffectAdded(Living $entity, EffectInstance $effect, bool $replacesOldEffect) : void{
		$this->sendDataPacket(MobEffectPacket::add($entity->getId(), $replacesOldEffect, $effect->getId(), $effect->getAmplifier(), $effect->isVisible(), $effect->getDuration()));
	}

	public function onEntityEffectRemoved(Living $entity, EffectInstance $effect) : void{
		$this->sendDataPacket(MobEffectPacket::remove($entity->getId(), $effect->getId()));
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
			if(!empty($aliases)){
				if(!in_array($lname, $aliases, true)){
					//work around a client bug which makes the original name not show when aliases are used
					$aliases[] = $lname;
				}
				$aliasObj = new CommandEnum(ucfirst($command->getName()) . "Aliases", $aliases);
			}

			$data = new CommandData(
				$lname, //TODO: commands containing uppercase letters in the name crash 1.9.0 client
				$this->server->getLanguage()->translateString($command->getDescription()),
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

	public function onTranslatedChatMessage(string $key, array $parameters) : void{
		$this->sendDataPacket(TextPacket::translation($key, $parameters));
	}

	public function onPopup(string $message) : void{
		$this->sendDataPacket(TextPacket::popup($message));
	}

	public function onTip(string $message) : void{
		$this->sendDataPacket(TextPacket::tip($message));
	}

	public function onFormSent(int $id, array $data) : bool{
		$formData = json_encode($data);
		if($formData === false){
			throw new \InvalidArgumentException("Failed to encode form JSON: " . json_last_error_msg());
		}
		return $this->sendDataPacket(ModalFormRequestPacket::create($id, $formData));
	}

	/**
	 * Instructs the networksession to start using the chunk at the given coordinates. This may occur asynchronously.
	 * @param int      $chunkX
	 * @param int      $chunkZ
	 * @param \Closure $onCompletion To be called when chunk sending has completed.
	 */
	public function startUsingChunk(int $chunkX, int $chunkZ, \Closure $onCompletion) : void{
		Utils::validateCallableSignature(function(int $chunkX, int $chunkZ){}, $onCompletion);

		$world = $this->player->getLocation()->getWorld();
		assert($world !== null);
		ChunkCache::getInstance($world)->request($chunkX, $chunkZ)->onResolve(

			//this callback may be called synchronously or asynchronously, depending on whether the promise is resolved yet
			function(CompressBatchPromise $promise) use ($world, $chunkX, $chunkZ, $onCompletion){
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
		$world->sendTime($this->player);
		$world->sendDifficulty($this->player);
	}

	/**
	 * @return InventoryManager
	 */
	public function getInvManager() : InventoryManager{
		return $this->invManager;
	}

	/**
	 * TODO: expand this to more than just humans
	 * TODO: offhand
	 *
	 * @param Human $mob
	 */
	public function onMobEquipmentChange(Human $mob) : void{
		//TODO: we could send zero for slot here because remote players don't need to know which slot was selected
		$inv = $mob->getInventory();
		$this->sendDataPacket(MobEquipmentPacket::create($mob->getId(), $inv->getItemInHand(), $inv->getHeldItemIndex(), ContainerIds::INVENTORY));
	}

	public function onMobArmorChange(Living $mob) : void{
		$inv = $mob->getArmorInventory();
		$this->sendDataPacket(MobArmorEquipmentPacket::create($mob->getId(), $inv->getHelmet(), $inv->getChestplate(), $inv->getLeggings(), $inv->getBoots()));
	}

	public function syncPlayerList() : void{
		$this->sendDataPacket(PlayerListPacket::add(array_map(function(Player $player){
			return PlayerListEntry::createAdditionEntry($player->getUniqueId(), $player->getId(), $player->getDisplayName(), SkinAdapterSingleton::get()->toSkinData($player->getSkin()), $player->getXuid());
		}, $this->server->getOnlinePlayers())));
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

		if($this->sendBuffer !== null){
			$this->flushSendBuffer();
		}

		return false;
	}

	/**
	 * Returns a client-friendly gamemode of the specified real gamemode
	 * This function takes care of handling gamemodes known to MCPE (as of 1.1.0.3, that includes Survival, Creative and Adventure)
	 *
	 * @internal
	 * @param GameMode $gamemode
	 *
	 * @return int
	 */
	public static function getClientFriendlyGamemode(GameMode $gamemode) : int{
		if($gamemode->equals(GameMode::SPECTATOR())){
			return GameMode::CREATIVE()->getMagicNumber();
		}

		return $gamemode->getMagicNumber();
	}
}
