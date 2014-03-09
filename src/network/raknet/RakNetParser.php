<?php

/**
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

class RakNetParser{
	private $buffer;
	private $offset;
	public $packet;
	
	public function __construct(&$buffer){
		$this->buffer =& $buffer;
		$this->offset = 0;
		if(strlen($this->buffer) > 0){
			$this->parse();
		}else{
			$this->packet = false;
		}
	}
	
	private function get($len){
		if($len <= 0){
			$this->offset = strlen($this->buffer) - 1;
			return "";
		}
		if($len === true){
			return substr($this->buffer, $this->offset);
		}
		$this->offset += $len;
		return substr($this->buffer, $this->offset - $len, $len);
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
		return ord($this->get(1));
	}
	
	
	private function feof(){
		return !isset($this->buffer{$this->offset});
	}
	
	private function parse(){
		$this->packet = new RakNetPacket(ord($this->get(1)));
		$this->packet->buffer =& $this->buffer;
		$this->packet->length = strlen($this->buffer);
		switch($this->packet->pid()){
			case RakNetInfo::UNCONNECTED_PING:
			case RakNetInfo::UNCONNECTED_PING_OPEN_CONNECTIONS:
				$this->packet->pingID = $this->getLong();
				$this->offset += 16; //Magic
				break;
			case RakNetInfo::OPEN_CONNECTION_REQUEST_1:
				$this->offset += 16; //Magic
				$this->packet->structure = $this->getByte();
				$this->packet->mtuSize = strlen($this->get(true));
				break;
			case RakNetInfo::OPEN_CONNECTION_REQUEST_2:
				$this->offset += 16; //Magic
				$this->packet->security = $this->get(5);
				$this->packet->port = $this->getShort(false);
				$this->packet->mtuSize = $this->getShort(false);
				$this->packet->clientID = $this->getLong();
				break;
			case RakNetInfo::DATA_PACKET_0:
			case RakNetInfo::DATA_PACKET_1:
			case RakNetInfo::DATA_PACKET_2:
			case RakNetInfo::DATA_PACKET_3:
			case RakNetInfo::DATA_PACKET_4:
			case RakNetInfo::DATA_PACKET_5:
			case RakNetInfo::DATA_PACKET_6:
			case RakNetInfo::DATA_PACKET_7:
			case RakNetInfo::DATA_PACKET_8:
			case RakNetInfo::DATA_PACKET_9:
			case RakNetInfo::DATA_PACKET_A:
			case RakNetInfo::DATA_PACKET_B:
			case RakNetInfo::DATA_PACKET_C:
			case RakNetInfo::DATA_PACKET_D:
			case RakNetInfo::DATA_PACKET_E:
			case RakNetInfo::DATA_PACKET_F:
				$this->packet->seqNumber = $this->getLTriad();
				$this->packet->data = array();
				while(!$this->feof() and ($pk = $this->parseDataPacket()) instanceof RakNetDataPacket){
					$this->packet->data[] = $pk;	
				}
				break;
			case RakNetInfo::NACK:
			case RakNetInfo::ACK:
				$count = $this->getShort();
				$this->packet->packets = array();
				for($i = 0; $i < $count and !$this->feof(); ++$i){
					if($this->getByte() === 0){
						$start = $this->getLTriad();
						$end = $this->getLTriad();
						if(($end - $start) > 4096){
							$end = $start + 4096;
						}
						for($c = $start; $c <= $end; ++$c){
							$this->packet->packets[] = $c;
						}
					}else{
						$this->packet->packets[] = $this->getLTriad();
					}
				}
				break;
			default:
				$this->packet = false;
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
		or $reliability === 7){
			$messageIndex = $this->getLTriad();
		}else{
			$messageIndex = false;
		}
		
		if($reliability === 1
		or $reliability === 3
		or $reliability === 4
		or $reliability === 7){
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
		or ($hasSplit === true and $splitIndex >= $splitCount)){
			return false;
		}else{
			$pid = $this->getByte();
			$buffer = $this->get($length - 1);
			if(strlen($buffer) < ($length - 1)){
				return false;
			}
			switch($pid){
				case ProtocolInfo::PING_PACKET:
					$data = new PingPacket;
					break;
				case ProtocolInfo::PONG_PACKET:
					$data = new PongPacket;
					break;
				case ProtocolInfo::CLIENT_CONNECT_PACKET:
					$data = new ClientConnectPacket;
					break;
				case ProtocolInfo::SERVER_HANDSHAKE_PACKET:
					$data = new ServerHandshakePacket;
					break;
				case ProtocolInfo::DISCONNECT_PACKET:
					$data = new DisconnectPacket;
					break;
				case ProtocolInfo::LOGIN_PACKET:
					$data = new LoginPacket;
					break;
				case ProtocolInfo::LOGIN_STATUS_PACKET:
					$data = new LoginStatusPacket;
					break;
				case ProtocolInfo::READY_PACKET:
					$data = new ReadyPacket;
					break;
				case ProtocolInfo::MESSAGE_PACKET:
					$data = new MessagePacket;
					break;
				case ProtocolInfo::SET_TIME_PACKET:
					$data = new SetTimePacket;
					break;
				case ProtocolInfo::START_GAME_PACKET:
					$data = new StartGamePacket;
					break;
				case ProtocolInfo::ADD_MOB_PACKET:
					$data = new AddMobPacket;
					break;
				case ProtocolInfo::ADD_PLAYER_PACKET:
					$data = new AddPlayerPacket;
					break;
				case ProtocolInfo::REMOVE_PLAYER_PACKET:
					$data = new RemovePlayerPacket;
					break;
				case ProtocolInfo::ADD_ENTITY_PACKET:
					$data = new AddEntityPacket;
					break;
				case ProtocolInfo::REMOVE_ENTITY_PACKET:
					$data = new RemoveEntityPacket;
					break;
				case ProtocolInfo::ADD_ITEM_ENTITY_PACKET:
					$data = new AddItemEntityPacket;
					break;
				case ProtocolInfo::TAKE_ITEM_ENTITY_PACKET:
					$data = new TakeItemEntityPacket;
					break;
				case ProtocolInfo::MOVE_ENTITY_PACKET:
					$data = new MoveEntityPacket;
					break;
				case ProtocolInfo::MOVE_ENTITY_PACKET_POSROT:
					$data = new MoveEntityPacket_PosRot;
					break;
				case ProtocolInfo::ROTATE_HEAD_PACKET:
					$data = new RotateHeadPacket;
					break;
				case ProtocolInfo::MOVE_PLAYER_PACKET:
					$data = new MovePlayerPacket;
					break;
				case ProtocolInfo::REMOVE_BLOCK_PACKET:
					$data = new RemoveBlockPacket;
					break;
				case ProtocolInfo::UPDATE_BLOCK_PACKET:
					$data = new UpdateBlockPacket;
					break;
				case ProtocolInfo::ADD_PAINTING_PACKET:
					$data = new AddPaintingPacket;
					break;
				case ProtocolInfo::EXPLODE_PACKET:
					$data = new ExplodePacket;
					break;
				case ProtocolInfo::LEVEL_EVENT_PACKET:
					$data = new LevelEventPacket;
					break;
				case ProtocolInfo::TILE_EVENT_PACKET:
					$data = new TileEventPacket;
					break;
				case ProtocolInfo::ENTITY_EVENT_PACKET:
					$data = new EntityEventPacket;
					break;
				case ProtocolInfo::REQUEST_CHUNK_PACKET:
					$data = new RequestChunkPacket;
					break;
				case ProtocolInfo::CHUNK_DATA_PACKET:
					$data = new ChunkDataPacket;
					break;
				case ProtocolInfo::PLAYER_EQUIPMENT_PACKET:
					$data = new PlayerEquipmentPacket;
					break;
				case ProtocolInfo::PLAYER_ARMOR_EQUIPMENT_PACKET:
					$data = new PlayerArmorEquipmentPacket;
					break;
				case ProtocolInfo::INTERACT_PACKET:
					$data = new InteractPacket;
					break;
				case ProtocolInfo::USE_ITEM_PACKET:
					$data = new UseItemPacket;
					break;
				case ProtocolInfo::PLAYER_ACTION_PACKET:
					$data = new PlayerActionPacket;
					break;
				case ProtocolInfo::HURT_ARMOR_PACKET:
					$data = new HurtArmorPacket;
					break;
				case ProtocolInfo::SET_ENTITY_DATA_PACKET:
					$data = new SetEntityDataPacket;
					break;
				case ProtocolInfo::SET_ENTITY_MOTION_PACKET:
					$data = new SetEntityMotionPacket;
					break;
				case ProtocolInfo::SET_HEALTH_PACKET:
					$data = new SetHealthPacket;
					break;
				case ProtocolInfo::SET_SPAWN_POSITION_PACKET:
					$data = new SetSpawnPositionPacket;
					break;
				case ProtocolInfo::ANIMATE_PACKET:
					$data = new AnimatePacket;
					break;
				case ProtocolInfo::RESPAWN_PACKET:
					$data = new RespawnPacket;
					break;
				case ProtocolInfo::SEND_INVENTORY_PACKET:
					$data = new SendInventoryPacket;
					break;
				case ProtocolInfo::DROP_ITEM_PACKET:
					$data = new DropItemPacket;
					break;
				case ProtocolInfo::CONTAINER_OPEN_PACKET:
					$data = new ContainerOpenPacket;
					break;
				case ProtocolInfo::CONTAINER_CLOSE_PACKET:
					$data = new ContainerClosePacket;
					break;
				case ProtocolInfo::CONTAINER_SET_SLOT_PACKET:
					$data = new ContainerSetSlotPacket;
					break;
				case ProtocolInfo::CONTAINER_SET_DATA_PACKET:
					$data = new ContainerSetDataPacket;
					break;
				case ProtocolInfo::CONTAINER_SET_CONTENT_PACKET:
					$data = new ContainerSetContentPacket;
					break;
				case ProtocolInfo::CHAT_PACKET:
					$data = new ChatPacket;
					break;
				case ProtocolInfo::ADVENTURE_SETTINGS_PACKET:
					$data = new AdventureSettingsPacket;
					break;
				case ProtocolInfo::ENTITY_DATA_PACKET:
					$data = new EntityDataPacket;
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
		}
		return $data;
	}

}