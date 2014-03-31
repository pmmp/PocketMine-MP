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

namespace PocketMine\Network\RakNet;

use PocketMine\Network\Packet as NetworkPacket;
use PocketMine\Network\Protocol\AddEntityPacket;
use PocketMine\Network\Protocol\AddItemEntityPacket;
use PocketMine\Network\Protocol\AddMobPacket;
use PocketMine\Network\Protocol\AddPaintingPacket;
use PocketMine\Network\Protocol\AddPlayerPacket;
use PocketMine\Network\Protocol\AdventureSettingsPacket;
use PocketMine\Network\Protocol\AnimatePacket;
use PocketMine\Network\Protocol\ChatPacket;
use PocketMine\Network\Protocol\ChunkDataPacket;
use PocketMine\Network\Protocol\ClientConnectPacket;
use PocketMine\Network\Protocol\ContainerClosePacket;
use PocketMine\Network\Protocol\ContainerOpenPacket;
use PocketMine\Network\Protocol\ContainerSetContentPacket;
use PocketMine\Network\Protocol\ContainerSetDataPacket;
use PocketMine\Network\Protocol\ContainerSetSlotPacket;
use PocketMine\Network\Protocol\DataPacket;
use PocketMine\Network\Protocol\DisconnectPacket;
use PocketMine\Network\Protocol\DropItemPacket;
use PocketMine\Network\Protocol\EntityDataPacket;
use PocketMine\Network\Protocol\EntityEventPacket;
use PocketMine\Network\Protocol\ExplodePacket;
use PocketMine\Network\Protocol\HurtArmorPacket;
use PocketMine\Network\Protocol\Info as ProtocolInfo;
use PocketMine\Network\Protocol\InteractPacket;
use PocketMine\Network\Protocol\LevelEventPacket;
use PocketMine\Network\Protocol\LoginPacket;
use PocketMine\Network\Protocol\LoginStatusPacket;
use PocketMine\Network\Protocol\MessagePacket;
use PocketMine\Network\Protocol\MoveEntityPacket;
use PocketMine\Network\Protocol\MoveEntityPacket_PosRot;
use PocketMine\Network\Protocol\MovePlayerPacket;
use PocketMine\Network\Protocol\PingPacket;
use PocketMine\Network\Protocol\PlayerActionPacket;
use PocketMine\Network\Protocol\PlayerArmorEquipmentPacket;
use PocketMine\Network\Protocol\PlayerEquipmentPacket;
use PocketMine\Network\Protocol\PongPacket;
use PocketMine\Network\Protocol\ReadyPacket;
use PocketMine\Network\Protocol\RemoveBlockPacket;
use PocketMine\Network\Protocol\RemoveEntityPacket;
use PocketMine\Network\Protocol\RemovePlayerPacket;
use PocketMine\Network\Protocol\RequestChunkPacket;
use PocketMine\Network\Protocol\RespawnPacket;
use PocketMine\Network\Protocol\RotateHeadPacket;
use PocketMine\Network\Protocol\SendInventoryPacket;
use PocketMine\Network\Protocol\ServerHandshakePacket;
use PocketMine\Network\Protocol\SetEntityDataPacket;
use PocketMine\Network\Protocol\SetEntityMotionPacket;
use PocketMine\Network\Protocol\SetHealthPacket;
use PocketMine\Network\Protocol\SetSpawnPositionPacket;
use PocketMine\Network\Protocol\SetTimePacket;
use PocketMine\Network\Protocol\StartGamePacket;
use PocketMine\Network\Protocol\TakeItemEntityPacket;
use PocketMine\Network\Protocol\TileEventPacket;
use PocketMine\Network\Protocol\UnknownPacket;
use PocketMine\Network\Protocol\UpdateBlockPacket;
use PocketMine\Network\Protocol\UseItemPacket;
use PocketMine\Utils\Utils;

class Packet extends NetworkPacket{
	private $packetID;
	private $offset = 1;
	public $data = array();

	public function __construct($packetID){
		$this->packetID = (int) $packetID;
	}

	public function pid(){
		return $this->packetID;
	}

	protected function get($len){
		if($len <= 0){
			$this->offset = strlen($this->buffer) - 1;

			return "";
		}elseif($len === true){
			return substr($this->buffer, $this->offset);
		}

		$buffer = "";
		for(; $len > 0; --$len, ++$this->offset){
			$buffer .= @$this->buffer{$this->offset};
		}

		return $buffer;
	}

	private function getLong($unsigned = false){
		return Utils::readLong($this->get(8), $unsigned);
	}

	private function getInt(){
		return Utils::readInt($this->get(4));
	}

	private function getShort($unsigned = false){
		return Utils::readShort($this->get(2), $unsigned);
	}

	private function getLTriad(){
		return Utils::readTriad(strrev($this->get(3)));
	}

	private function getByte(){
		return ord($this->buffer{$this->offset++});
	}

	private function feof(){
		return !isset($this->buffer{$this->offset});
	}

	public function decode(){
		$this->offset = 1;
		switch($this->packetID){
			case Info::UNCONNECTED_PING:
			case Info::UNCONNECTED_PING_OPEN_CONNECTIONS:
				$this->pingID = $this->getLong();
				$this->offset += 16; //Magic
				break;
			case Info::OPEN_CONNECTION_REQUEST_1:
				$this->offset += 16; //Magic
				$this->structure = $this->getByte();
				$this->mtuSize = strlen($this->get(true));
				break;
			case Info::OPEN_CONNECTION_REQUEST_2:
				$this->offset += 16; //Magic
				$this->security = $this->get(5);
				$this->clientPort = $this->getShort(false);
				$this->mtuSize = $this->getShort(false);
				$this->clientID = $this->getLong();
				break;
			case Info::DATA_PACKET_0:
			case Info::DATA_PACKET_1:
			case Info::DATA_PACKET_2:
			case Info::DATA_PACKET_3:
			case Info::DATA_PACKET_4:
			case Info::DATA_PACKET_5:
			case Info::DATA_PACKET_6:
			case Info::DATA_PACKET_7:
			case Info::DATA_PACKET_8:
			case Info::DATA_PACKET_9:
			case Info::DATA_PACKET_A:
			case Info::DATA_PACKET_B:
			case Info::DATA_PACKET_C:
			case Info::DATA_PACKET_D:
			case Info::DATA_PACKET_E:
			case Info::DATA_PACKET_F:
				$this->seqNumber = $this->getLTriad();
				$this->data = array();
				while(!$this->feof() and $this->parseDataPacket() !== false){

				}
				break;
			case Info::NACK:
			case Info::ACK:
				$count = $this->getShort();
				$this->packets = array();
				for($i = 0; $i < $count and !$this->feof(); ++$i){
					if($this->getByte() === 0){
						$start = $this->getLTriad();
						$end = $this->getLTriad();
						if(($end - $start) > 4096){
							$end = $start + 4096;
						}
						for($c = $start; $c <= $end; ++$c){
							$this->packets[] = $c;
						}
					}else{
						$this->packets[] = $this->getLTriad();
					}
				}
				break;
			default:
				break;
		}
	}

	private function parseDataPacket(){
		$packetFlags = $this->getByte();
		$reliability = ($packetFlags & 0b11100000) >> 5;
		$hasSplit = ($packetFlags & 0b00010000) > 0;
		$length = (int) ceil($this->getShort() / 8);
		if($reliability === 2
			or $reliability === 3
			or $reliability === 4
			or $reliability === 6
			or $reliability === 7
		){
			$messageIndex = $this->getLTriad();
		}else{
			$messageIndex = false;
		}

		if($reliability === 1
			or $reliability === 3
			or $reliability === 4
			or $reliability === 7
		){
			$orderIndex = $this->getLTriad();
			$orderChannel = $this->getByte();
		}else{
			$orderIndex = false;
			$orderChannel = false;
		}

		if($hasSplit == true){
			$splitCount = $this->getInt();
			$splitID = $this->getShort();
			$splitIndex = $this->getInt();
		}else{
			$splitCount = false;
			$splitID = false;
			$splitIndex = false;
		}

		if($length <= 0
			or $orderChannel >= 32
			or ($hasSplit === true and $splitIndex >= $splitCount)
		){
			return false;
		}else{
			$pid = $this->getByte();
			$buffer = $this->get($length - 1);
			if(strlen($buffer) < ($length - 1)){
				return false;
			}
			switch($pid){
				case ProtocolInfo::PING_PACKET:
					$data = new PingPacket();
					break;
				case ProtocolInfo::PONG_PACKET:
					$data = new PongPacket();
					break;
				case ProtocolInfo::CLIENT_CONNECT_PACKET:
					$data = new ClientConnectPacket();
					break;
				case ProtocolInfo::SERVER_HANDSHAKE_PACKET:
					$data = new ServerHandshakePacket();
					break;
				case ProtocolInfo::DISCONNECT_PACKET:
					$data = new DisconnectPacket();
					break;
				case ProtocolInfo::LOGIN_PACKET:
					$data = new LoginPacket();
					break;
				case ProtocolInfo::LOGIN_STATUS_PACKET:
					$data = new LoginStatusPacket();
					break;
				case ProtocolInfo::READY_PACKET:
					$data = new ReadyPacket();
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
				case ProtocolInfo::MOVE_ENTITY_PACKET_POSROT:
					$data = new MoveEntityPacket_PosRot();
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
				case ProtocolInfo::REQUEST_CHUNK_PACKET:
					$data = new RequestChunkPacket();
					break;
				case ProtocolInfo::CHUNK_DATA_PACKET:
					$data = new ChunkDataPacket();
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
				default:
					$data = new UnknownPacket();
					$data->packetID = $pid;
					break;
			}
			$data->reliability = $reliability;
			$data->hasSplit = $hasSplit;
			$data->messageIndex = $messageIndex;
			$data->orderIndex = $orderIndex;
			$data->orderChannel = $orderChannel;
			$data->splitCount = $splitCount;
			$data->splitID = $splitID;
			$data->splitIndex = $splitIndex;
			$data->setBuffer($buffer);
			$this->data[] = $data;
		}

		return true;
	}

	public function encode(){
		if(strlen($this->buffer) > 0){
			return;
		}
		$this->buffer = chr($this->packetID);

		switch($this->packetID){
			case Info::OPEN_CONNECTION_REPLY_1:
				$this->buffer .= Info::MAGIC;
				$this->putLong($this->serverID);
				$this->putByte(0); //server security
				$this->putShort($this->mtuSize);
				break;
			case Info::OPEN_CONNECTION_REPLY_2:
				$this->buffer .= Info::MAGIC;
				$this->putLong($this->serverID);
				$this->putShort($this->serverPort);
				$this->putShort($this->mtuSize);
				$this->putByte(0); //Server security
				break;
			case Info::INCOMPATIBLE_PROTOCOL_VERSION:
				$this->putByte(Info::STRUCTURE);
				$this->buffer .= Info::MAGIC;
				$this->putLong($this->serverID);
				break;
			case Info::UNCONNECTED_PONG:
			case Info::ADVERTISE_SYSTEM:
				$this->putLong($this->pingID);
				$this->putLong($this->serverID);
				$this->buffer .= Info::MAGIC;
				$this->putString($this->serverType);
				break;
			case Info::DATA_PACKET_0:
			case Info::DATA_PACKET_1:
			case Info::DATA_PACKET_2:
			case Info::DATA_PACKET_3:
			case Info::DATA_PACKET_4:
			case Info::DATA_PACKET_5:
			case Info::DATA_PACKET_6:
			case Info::DATA_PACKET_7:
			case Info::DATA_PACKET_8:
			case Info::DATA_PACKET_9:
			case Info::DATA_PACKET_A:
			case Info::DATA_PACKET_B:
			case Info::DATA_PACKET_C:
			case Info::DATA_PACKET_D:
			case Info::DATA_PACKET_E:
			case Info::DATA_PACKET_F:
				$this->putLTriad($this->seqNumber);
				foreach($this->data as $pk){
					$this->encodeDataPacket($pk);
				}
				break;
			case Info::NACK:
			case Info::ACK:
				$payload = "";
				$records = 0;
				$pointer = 0;
				sort($this->packets, SORT_NUMERIC);
				$max = count($this->packets);

				while($pointer < $max){
					$type = true;
					$curr = $start = $this->packets[$pointer];
					for($i = $start + 1; $i < $max; ++$i){
						$n = $this->packets[$i];
						if(($n - $curr) === 1){
							$curr = $end = $n;
							$type = false;
							$pointer = $i + 1;
						}else{
							break;
						}
					}
					++$pointer;
					if($type === false){
						$payload .= "\x00";
						$payload .= strrev(Utils::writeTriad($start));
						$payload .= strrev(Utils::writeTriad($end));
					}else{
						$payload .= Utils::writeBool(true);
						$payload .= strrev(Utils::writeTriad($start));
					}
					++$records;
				}
				$this->putShort($records);
				$this->buffer .= $payload;
				break;
			default:

		}

	}

	private function encodeDataPacket(DataPacket $pk){
		$this->putByte(($pk->reliability << 5) | ($pk->hasSplit > 0 ? 0b00010000 : 0));
		$this->putShort(strlen($pk->buffer) << 3);
		if($pk->reliability === 2
			or $pk->reliability === 3
			or $pk->reliability === 4
			or $pk->reliability === 6
			or $pk->reliability === 7
		){
			$this->putLTriad($pk->messageIndex);
		}

		if($pk->reliability === 1
			or $pk->reliability === 3
			or $pk->reliability === 4
			or $pk->reliability === 7
		){
			$this->putLTriad($pk->orderIndex);
			$this->putByte($pk->orderChannel);
		}

		if($pk->hasSplit === true){
			$this->putInt($pk->splitCount);
			$this->putShort($pk->splitID);
			$this->putInt($pk->splitIndex);
		}

		$this->buffer .= $pk->buffer;
	}

	protected function put($str){
		$this->buffer .= $str;
	}

	protected function putLong($v){
		$this->buffer .= Utils::writeLong($v);
	}

	protected function putInt($v){
		$this->buffer .= Utils::writeInt($v);
	}

	protected function putShort($v){
		$this->buffer .= Utils::writeShort($v);
	}

	protected function putTriad($v){
		$this->buffer .= Utils::writeTriad($v);
	}

	protected function putLTriad($v){
		$this->buffer .= strrev(Utils::writeTriad($v));
	}

	protected function putByte($v){
		$this->buffer .= chr($v);
	}

	protected function putString($v){
		$this->putShort(strlen($v));
		$this->put($v);
	}

	public function __destruct(){
	}
}