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
 * Network-related classes
 */
namespace pocketmine\network;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddPaintingPacket;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\AdventureSettingsPacket;
use pocketmine\network\protocol\AnimatePacket;
use pocketmine\network\protocol\ChatPacket;
use pocketmine\network\protocol\ContainerClosePacket;
use pocketmine\network\protocol\ContainerOpenPacket;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetDataPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\DropItemPacket;
use pocketmine\network\protocol\EntityDataPacket;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\network\protocol\HurtArmorPacket;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\InteractPacket;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\network\protocol\LoginStatusPacket;
use pocketmine\network\protocol\MessagePacket;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\PlayerActionPacket;
use pocketmine\network\protocol\PlayerArmorEquipmentPacket;
use pocketmine\network\protocol\PlayerEquipmentPacket;
use pocketmine\network\protocol\RemoveBlockPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\RemovePlayerPacket;
use pocketmine\network\protocol\RespawnPacket;
use pocketmine\network\protocol\RotateHeadPacket;
use pocketmine\network\protocol\SendInventoryPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\StartGamePacket;
use pocketmine\network\protocol\TakeItemEntityPacket;
use pocketmine\network\protocol\TileEventPacket;
use pocketmine\network\protocol\UnknownPacket;
use pocketmine\network\protocol\UnloadChunkPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\Player;
use pocketmine\scheduler\CallbackTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use raklib\protocol\EncapsulatedPacket;
use raklib\RakLib;
use raklib\server\RakLibServer;
use raklib\server\ServerHandler;
use raklib\server\ServerInstance;

class RakLibInterface implements ServerInstance, SourceInterface{

	private $server;
	/** @var Player[] */
	private $players = [];

	/** @var \SplObjectStorage */
	private $identifers;

	/** @var int[] */
	private $identifiersACK = [];

	/** @var ServerHandler */
	private $interface;

	private $tickTask;

	private $upload = 0;
	private $download = 0;

	public function __construct(Server $server){
		$this->server = $server;
		$this->identifers = new \SplObjectStorage();

		$server = new RakLibServer($this->server->getLogger(), $this->server->getLoader(), $this->server->getPort(), $this->server->getIp() === "" ? "0.0.0.0" : $this->server->getIp());
		$this->interface = new ServerHandler($server, $this);
		$this->setName($this->server->getMotd());
		$this->tickTask = $this->server->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "doTick"]), 1);
	}

	public function doTick(){
		$this->interface->sendTick();
	}

	public function process(){
		$work = false;
		if($this->interface->handlePacket()){
			$work = true;
			while($this->interface->handlePacket()){
			}
		}

		return $work;
	}

	public function closeSession($identifier, $reason){
		if(isset($this->players[$identifier])){
			$player = $this->players[$identifier];
			$this->identifers->detach($player);
			unset($this->players[$identifier]);
			unset($this->identifiersACK[$identifier]);
			$player->close(TextFormat::YELLOW . $player->getName() . " has left the game", $reason);
		}
	}

	public function close(Player $player, $reason = "unknown reason"){
		if(isset($this->identifers[$player])){
			unset($this->players[$this->identifers[$player]]);
			unset($this->identifiersACK[$this->identifers[$player]]);
			$this->interface->closeSession($this->identifers[$player], $reason);
			$this->identifers->detach($player);
		}
	}

	public function shutdown(){
		$this->tickTask->cancel();
		$this->interface->shutdown();
	}

	public function emergencyShutdown(){
		$this->tickTask->cancel();
		$this->interface->emergencyShutdown();
	}

	public function openSession($identifier, $address, $port, $clientID){
		$player = new Player($this, null, $address, $port);
		$this->players[$identifier] = $player;
		$this->identifiersACK[$identifier] = 0;
		$this->identifers->attach($player, $identifier);
		$this->server->addPlayer($identifier, $player);
	}

	public function handleEncapsulated($identifier, EncapsulatedPacket $packet, $flags){
		if(isset($this->players[$identifier])){
			$pk = $this->getPacket($packet->buffer);
			$pk->decode();
			$this->players[$identifier]->handleDataPacket($pk);
		}
	}

	public function handleRaw($address, $port, $payload){
		$this->server->handlePacket($address, $port, $payload);
	}

	public function putRaw($address, $port, $payload){
		$this->interface->sendRaw($address, $port, $payload);
	}

	public function notifyACK($identifier, $identifierACK){
		if(isset($this->players[$identifier])){
			$this->players[$identifier]->handleACK($identifierACK);
		}
	}

	public function setName($name){
		$this->interface->sendOption("name", "MCCPP;Demo;$name");
	}

	public function setPortCheck($name){
		$this->interface->sendOption("portChecking", (bool) $name);
	}

	public function handleOption($name, $value){
		if($name === "bandwidth"){
			$v = unserialize($value);
			$this->upload = $v["up"];
			$this->download = $v["down"];
		}
	}

	public function getUploadUsage(){
		return $this->upload;
	}

	public function getDownloadUsage(){
		return $this->download;
	}

	public function putPacket(Player $player, DataPacket $packet, $needACK = false, $immediate = false){
		if(isset($this->identifers[$player])){
			$identifier = $this->identifers[$player];
			$packet->encode();
			$pk = new EncapsulatedPacket();
			$pk->buffer = $packet->buffer;
			$pk->reliability = 2;
			if($needACK === true){
				$pk->identifierACK = $this->identifiersACK[$identifier]++;
			}
			$this->interface->sendEncapsulated($identifier, $pk, ($needACK === true ? RakLib::FLAG_NEED_ACK : 0) | ($immediate === true ? RakLib::PRIORITY_IMMEDIATE : RakLib::PRIORITY_NORMAL));

			return $pk->identifierACK;
		}

		return null;
	}

	private function getPacket($buffer){
		$pid = ord($buffer{0});
		switch($pid){ //TODO: more efficient selection based on range
			case ProtocolInfo::LOGIN_PACKET:
				$data = new LoginPacket();
				break;
			case ProtocolInfo::LOGIN_STATUS_PACKET:
				$data = new LoginStatusPacket();
				break;
			case ProtocolInfo::MESSAGE_PACKET:
				$data = new MessagePacket();
				break;
			case ProtocolInfo::SET_TIME_PACKET:
				$data = new SetTimePacket();
				break;
			case ProtocolInfo::START_GAME_PACKET:
				$data = new StartGamePacket();
				break;
			case ProtocolInfo::ADD_MOB_PACKET:
				$data = new AddMobPacket();
				break;
			case ProtocolInfo::ADD_PLAYER_PACKET:
				$data = new AddPlayerPacket();
				break;
			case ProtocolInfo::REMOVE_PLAYER_PACKET:
				$data = new RemovePlayerPacket();
				break;
			case ProtocolInfo::ADD_ENTITY_PACKET:
				$data = new AddEntityPacket();
				break;
			case ProtocolInfo::REMOVE_ENTITY_PACKET:
				$data = new RemoveEntityPacket();
				break;
			case ProtocolInfo::ADD_ITEM_ENTITY_PACKET:
				$data = new AddItemEntityPacket();
				break;
			case ProtocolInfo::TAKE_ITEM_ENTITY_PACKET:
				$data = new TakeItemEntityPacket();
				break;
			case ProtocolInfo::MOVE_ENTITY_PACKET:
				$data = new MoveEntityPacket();
				break;
			case ProtocolInfo::ROTATE_HEAD_PACKET:
				$data = new RotateHeadPacket();
				break;
			case ProtocolInfo::MOVE_PLAYER_PACKET:
				$data = new MovePlayerPacket();
				break;
			case ProtocolInfo::REMOVE_BLOCK_PACKET:
				$data = new RemoveBlockPacket();
				break;
			case ProtocolInfo::UPDATE_BLOCK_PACKET:
				$data = new UpdateBlockPacket();
				break;
			case ProtocolInfo::ADD_PAINTING_PACKET:
				$data = new AddPaintingPacket();
				break;
			case ProtocolInfo::EXPLODE_PACKET:
				$data = new ExplodePacket();
				break;
			case ProtocolInfo::LEVEL_EVENT_PACKET:
				$data = new LevelEventPacket();
				break;
			case ProtocolInfo::TILE_EVENT_PACKET:
				$data = new TileEventPacket();
				break;
			case ProtocolInfo::ENTITY_EVENT_PACKET:
				$data = new EntityEventPacket();
				break;
			case ProtocolInfo::PLAYER_EQUIPMENT_PACKET:
				$data = new PlayerEquipmentPacket();
				break;
			case ProtocolInfo::PLAYER_ARMOR_EQUIPMENT_PACKET:
				$data = new PlayerArmorEquipmentPacket();
				break;
			case ProtocolInfo::INTERACT_PACKET:
				$data = new InteractPacket();
				break;
			case ProtocolInfo::USE_ITEM_PACKET:
				$data = new UseItemPacket();
				break;
			case ProtocolInfo::PLAYER_ACTION_PACKET:
				$data = new PlayerActionPacket();
				break;
			case ProtocolInfo::HURT_ARMOR_PACKET:
				$data = new HurtArmorPacket();
				break;
			case ProtocolInfo::SET_ENTITY_DATA_PACKET:
				$data = new SetEntityDataPacket();
				break;
			case ProtocolInfo::SET_ENTITY_MOTION_PACKET:
				$data = new SetEntityMotionPacket();
				break;
			case ProtocolInfo::SET_HEALTH_PACKET:
				$data = new SetHealthPacket();
				break;
			case ProtocolInfo::SET_SPAWN_POSITION_PACKET:
				$data = new SetSpawnPositionPacket();
				break;
			case ProtocolInfo::ANIMATE_PACKET:
				$data = new AnimatePacket();
				break;
			case ProtocolInfo::RESPAWN_PACKET:
				$data = new RespawnPacket();
				break;
			case ProtocolInfo::SEND_INVENTORY_PACKET:
				$data = new SendInventoryPacket();
				break;
			case ProtocolInfo::DROP_ITEM_PACKET:
				$data = new DropItemPacket();
				break;
			case ProtocolInfo::CONTAINER_OPEN_PACKET:
				$data = new ContainerOpenPacket();
				break;
			case ProtocolInfo::CONTAINER_CLOSE_PACKET:
				$data = new ContainerClosePacket();
				break;
			case ProtocolInfo::CONTAINER_SET_SLOT_PACKET:
				$data = new ContainerSetSlotPacket();
				break;
			case ProtocolInfo::CONTAINER_SET_DATA_PACKET:
				$data = new ContainerSetDataPacket();
				break;
			case ProtocolInfo::CONTAINER_SET_CONTENT_PACKET:
				$data = new ContainerSetContentPacket();
				break;
			case ProtocolInfo::CHAT_PACKET:
				$data = new ChatPacket();
				break;
			case ProtocolInfo::ADVENTURE_SETTINGS_PACKET:
				$data = new AdventureSettingsPacket();
				break;
			case ProtocolInfo::ENTITY_DATA_PACKET:
				$data = new EntityDataPacket();
				break;
			case ProtocolInfo::UNLOAD_CHUNK_PACKET:
				$data = new UnloadChunkPacket();
				break;
			default:
				$data = new UnknownPacket();
				$data->packetID = $pid;
				break;
		}

		$data->setBuffer(substr($buffer, 1));

		return $data;
	}
}