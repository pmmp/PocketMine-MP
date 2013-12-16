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

class CustomPacketHandler{
	public $offset;
	public $raw;
	public $c;
	public $data;
	public $name = "";

	public function get($len = true, $check = true){
		if($len === true){
			$data = substr($this->raw, $this->offset);
			if($check === true){
				$this->offset = strlen($this->raw);
			}
			return $data;
		}
		$data = substr($this->raw, $this->offset, $len);
		if($check === true){
			$this->offset += $len;
		}
		return $data;
	}
	
	private function feof(){
		return !isset($this->raw{$this->offset});
	}

	public function __construct($pid, $raw = "", $data = array(), $create = false){
		$this->raw = $raw;
		$this->data = $data;
		$this->offset = 0;
		$this->c = (bool) $create;
		if($pid === false){
			return;
		}
		switch($pid){
			case MC_PING:
				if($this->c === false){
					$this->data["time"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= Utils::writeLong($this->data["time"]);
				}
				break;
			case MC_PONG:
				if($this->c === false){
					$this->data["ptime"] = Utils::readLong($this->get(8));
					$this->data["time"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= Utils::writeLong($this->data["ptime"]);
					$this->raw .= Utils::writeLong($this->data["time"]);
				}
				break;
			case MC_CLIENT_CONNECT:
				if($this->c === false){
					$this->data["clientID"] = Utils::readLong($this->get(8));
					$this->data["session"] = Utils::readLong($this->get(8));
					$this->data["unknown2"] = $this->get(1);
				}else{
					$this->raw .= Utils::writeLong($this->data["clientID"]);
					$this->raw .= Utils::writeLong($this->data["session"]);
					$this->raw .= "\x00";
				}
				break;
			case MC_SERVER_HANDSHAKE:
				if($this->c === false){
					$this->data["cookie"] = $this->get(4); // 043f57fe
					$this->data["security"] = $this->get(1);
					$this->data["port"] = Utils::readShort($this->get(2), false);
					$this->data["dataArray"] = Utils::readDataArray($this->get(true, false), 10, $offset);
					$this->get($offset);
					$this->data["timestamp"] = $this->get(2);
					$this->data["session"] = Utils::readLong($this->get(8));
					$this->data["session2"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= "\x04\x3f\x57\xfe";
					$this->raw .= "\xcd";
					$this->raw .= Utils::writeShort($this->data["port"]);
					$this->raw .= Utils::writeDataArray(array(
						"\xf5\xff\xff\xf5",
						"\xff\xff\xff\xff",
						"\xff\xff\xff\xff",
						"\xff\xff\xff\xff",
						"\xff\xff\xff\xff",
						"\xff\xff\xff\xff",
						"\xff\xff\xff\xff",
						"\xff\xff\xff\xff",
						"\xff\xff\xff\xff",
						"\xff\xff\xff\xff",
					));
					$this->raw .= "\x00\x00";
					$this->raw .= Utils::writeLong($this->data["session"]);
					$this->raw .= Utils::writeLong($this->data["session2"]);
				}
				break;
			case MC_CLIENT_HANDSHAKE:
				if($this->c === false){
					$this->data["cookie"] = $this->get(4); // 043f57fe
					$this->data["security"] = $this->get(1);
					$this->data["port"] = Utils::readShort($this->get(2), false);
					$this->data["dataArray0"] = $this->get(ord($this->get(1)));
					$this->data["dataArray"] = Utils::readDataArray($this->get(true, false), 9, $offset);
					$this->get($offset);
					$this->data["timestamp"] = $this->get(2);
					$this->data["session2"] = Utils::readLong($this->get(8));
					$this->data["session"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= "\x04\x3f\x57\xfe";
					$this->raw .= "\xed";
					$this->raw .= Utils::writeShort($this->data["port"]);
					$w = array_shift($this->data["dataArray"]);
					$this->raw .= chr(strlen($w)).$w;
					$this->raw .= Utils::writeDataArray($this->data["dataArray"]);
					$this->raw .= "\x00\x00";
					$this->raw .= Utils::writeLong($this->data["session2"]);
					$this->raw .= Utils::writeLong($this->data["session"]);
				}
				break;
			case MC_SERVER_FULL:
				if($this->c === false){
				}else{
					$this->raw .= RAKNET_MAGIC;
					$this->raw .= Utils::writeLong($this->data["serverID"]);
				}
				break;
			case MC_DISCONNECT:
				//null
				break;
			case MC_BANNED:
				if($this->c === false){
				}else{
					$this->raw .= RAKNET_MAGIC;
					$this->raw .= Utils::writeLong($this->data["serverID"]);
				}
				break;
			case MC_LOGIN:
				if($this->c === false){
					$this->data["username"] = $this->get(Utils::readShort($this->get(2), false));
					$this->data["protocol1"] = Utils::readInt($this->get(4));
					$this->data["protocol2"] = Utils::readInt($this->get(4));
					$this->data["clientId"] = Utils::readInt($this->get(4));
					$this->data["realms_data"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["username"])).$this->data["username"];
					$this->raw .= Utils::writeInt(CURRENT_PROTOCOL).
									Utils::writeInt(CURRENT_PROTOCOL).
									Utils::writeInt($this->data["clientId"]);
					$this->raw .= Utils::writeShort(strlen($this->data["realms_data"])).$this->data["realms_data"];
				}
				break;
			case MC_LOGIN_STATUS:
				if($this->c === false){
					$this->data["status"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["status"]);
				}
				break;
			case MC_READY:
				if($this->c === false){
					$this->data["status"] = ord($this->get(1));
				}else{
					$this->raw .= chr($this->data["status"]);
				}
				break;
			case MC_CHAT:
				if($this->c === false){
					$this->data["player"] = $this->get(Utils::readShort($this->get(2), false));
					$this->data["message"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["player"])).$this->data["player"];
					$this->raw .= Utils::writeShort(strlen($this->data["message"])).$this->data["message"];
				}
				break;
			case MC_SET_TIME:
				if($this->c === false){
					$this->data["time"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["time"])."\x80";
				}
				break;
			case MC_START_GAME:
				if($this->c === false){
					$this->data["seed"] = Utils::readInt($this->get(4));
					$this->data["generator"] = Utils::readInt($this->get(4));
					$this->data["gamemode"] = Utils::readInt($this->get(4));
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["seed"]);
					$this->raw .= Utils::writeInt($this->data["generator"]);
					$this->raw .= Utils::writeInt($this->data["gamemode"]);
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
				}
				break;
			case MC_ADD_MOB:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["type"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
					$this->data["pitch"] = Utils::readByte($this->get(1));
					$this->data["yaw"] = Utils::readByte($this->get(1));
					$this->data["metadata"] = Utils::readMetadata($this->get(true));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeInt($this->data["type"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::writeByte($this->data["pitch"]);
					$this->raw .= Utils::writeByte($this->data["yaw"]);
					$this->raw .= Utils::writeMetadata($this->data["metadata"]);
				}
				break;
			case MC_ADD_PLAYER:
				if($this->c === false){
					$this->data["clientID"] = Utils::readLong($this->get(8));
					$this->data["username"] = $this->get(Utils::readShort($this->get(2), false));
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
					$this->data["pitch"] = Utils::readByte($this->get(1));
					$this->data["yaw"] = Utils::readByte($this->get(1));
					$this->data["unknown1"] = Utils::readShort($this->get(2));
					$this->data["unknown2"] = Utils::readShort($this->get(2));
					$this->data["metadata"] = Utils::readMetadata($this->get(true));
				}else{
					$this->raw .= Utils::writeLong($this->data["clientID"]);
					$this->raw .= Utils::writeShort(strlen($this->data["username"])).$this->data["username"];
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::writeByte($this->data["pitch"]);
					$this->raw .= Utils::writeByte($this->data["yaw"]);
					$this->raw .= Utils::writeShort($this->data["unknown1"]);
					$this->raw .= Utils::writeShort($this->data["unknown2"]);
					$this->raw .= Utils::writeMetadata($this->data["metadata"]);
				}
				break;
			case MC_REMOVE_PLAYER:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["clientID"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeLong($this->data["clientID"]);
				}
				break;
			case MC_ADD_ENTITY:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["type"] = ord($this->get(1));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
					$this->data["did"] = Utils::readInt($this->get(4));
					if($this->data["did"] > 0){
						$this->data["speedX"] = Utils::readShort($this->get(2));
						$this->data["speedY"] = Utils::readShort($this->get(2));
						$this->data["speedZ"] = Utils::readShort($this->get(2));
					}
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= chr($this->data["type"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::writeInt($this->data["did"]);
					if($this->data["did"] > 0){
						$this->raw .= Utils::writeShort($this->data["speedX"]);
						$this->raw .= Utils::writeShort($this->data["speedY"]);
						$this->raw .= Utils::writeShort($this->data["speedZ"]);
					}
				}
				break;
			case MC_REMOVE_ENTITY:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
				}
				break;
			case MC_ADD_ITEM_ENTITY:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["block"] = Utils::readShort($this->get(2), false);
					$this->data["stack"] = ord($this->get(1));
					$this->data["meta"] = Utils::readShort($this->get(2), false);
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
					$this->data["yaw"] = Utils::readByte($this->get(1));
					$this->data["pitch"] = Utils::readByte($this->get(1));
					$this->data["roll"] = Utils::readByte($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeShort($this->data["block"]);
					$this->raw .= chr($this->data["stack"]);
					$this->raw .= Utils::writeShort($this->data["meta"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::writeByte($this->data["yaw"]);
					$this->raw .= Utils::writeByte($this->data["pitch"]);
					$this->raw .= Utils::writeByte($this->data["roll"]);
				}
				break;
			case MC_TAKE_ITEM_ENTITY:
				if($this->c === false){
					$this->data["target"] = Utils::readInt($this->get(4));
					$this->data["eid"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["target"]);
					$this->raw .= Utils::writeInt($this->data["eid"]);
				}
				break;
			case MC_MOVE_ENTITY:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
				}
				break;
			case MC_MOVE_ENTITY_POSROT:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
					$this->data["yaw"] = Utils::readFloat($this->get(4));
					$this->data["pitch"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::writeFloat($this->data["yaw"]);
					$this->raw .= Utils::writeFloat($this->data["pitch"]);
				}
				break;
			case MC_ROTATE_HEAD:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["yaw"] = Utils::readFloat($this->get(4));
					$this->data["pitch"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["yaw"]);
					$this->raw .= Utils::writeFloat($this->data["pitch"]);
				}
				break;
			case MC_MOVE_PLAYER:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
					$this->data["yaw"] = Utils::readFloat($this->get(4));
					$this->data["pitch"] = Utils::readFloat($this->get(4));
					$this->data["bodyYaw"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::writeFloat($this->data["yaw"]);
					$this->raw .= Utils::writeFloat($this->data["pitch"]);
					$this->raw .= Utils::writeFloat($this->data["bodyYaw"]);
				}
				break;
			case MC_PLACE_BLOCK:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["y"] = ord($this->get(1));
					$this->data["block"] = ord($this->get(1));
					$this->data["meta"] = ord($this->get(1));
					$this->data["face"] = Utils::readByte($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= chr($this->data["y"]);
					$this->raw .= chr($this->data["block"]);
					$this->raw .= chr($this->data["meta"]);
					$this->raw .= chr($this->data["face"]);
				}
				break;
			case MC_REMOVE_BLOCK: //Sent when a player removes a block, not used
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["y"] = ord($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= chr($this->data["y"]);
				}
				break;
			case MC_UPDATE_BLOCK:
				if($this->c === false){
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["y"] = ord($this->get(1));
					$this->data["block"] = ord($this->get(1));
					$this->data["meta"] = ord($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= chr($this->data["y"]);
					$this->raw .= chr($this->data["block"]);
					$this->raw .= chr($this->data["meta"]);
				}
				break;
			case MC_ADD_PAINTING:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["y"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["direction"] = Utils::readInt($this->get(4));
					$this->data["title"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["y"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= Utils::writeInt($this->data["direction"]);
					$this->raw .= Utils::writeShort(strlen($this->data["title"])).$this->data["title"];
				}
				break;
			case MC_EXPLOSION:
				if($this->c === false){
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
					$this->data["radius"] = Utils::readFloat($this->get(4));
					$this->data["count"] = Utils::readInt($this->get(4));
					$this->data["records"] = array();
					for($r = 0; $r < $this->data["count"] and !$this->feof(); ++$r){
						$this->data["records"][] = new Vector3(Utils::readByte($this->get(1)), Utils::readByte($this->get(1)), Utils::readByte($this->get(1)));
					}
				}else{
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::writeFloat($this->data["radius"]);
					$this->data["records"] = (array) $this->data["records"];
					$this->raw .= Utils::writeInt(count($this->data["records"]));
					if(count($this->data["records"]) > 0){
						foreach($this->data["records"] as $record){
							$this->raw .= Utils::writeByte($record->x) . Utils::writeByte($record->y) . Utils::writeByte($record->z);
						}
					}
				}
				break;
			case MC_LEVEL_EVENT:
				if($this->c === false){
					$this->data["evid"] = Utils::readShort($this->get(2));
					$this->data["x"] = Utils::readShort($this->get(2));
					$this->data["y"] = Utils::readShort($this->get(2));
					$this->data["z"] = Utils::readShort($this->get(2));
					$this->data["data"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeShort($this->data["evid"]);
					$this->raw .= Utils::writeShort($this->data["x"]);
					$this->raw .= Utils::writeShort($this->data["y"]);
					$this->raw .= Utils::writeShort($this->data["z"]);
					$this->raw .= Utils::writeInt($this->data["data"]);
				}
				break;
			case MC_TILE_EVENT:
				if($this->c === false){
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["y"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["case1"] = Utils::readInt($this->get(4));
					$this->data["case2"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["y"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= Utils::writeInt($this->data["case1"]);
					$this->raw .= Utils::writeInt($this->data["case2"]);
				}
				break;
			case MC_ENTITY_EVENT:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["event"] = ord($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= chr($this->data["event"]);
				}
				break;
			case MC_REQUEST_CHUNK:
				if($this->c === false){
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
				}
				break;
			case MC_CHUNK_DATA:
				if($this->c === false){
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["data"] = $this->get(true);
				}else{
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= $this->data["data"];
				}
				break;
			case MC_PLAYER_EQUIPMENT:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["block"] = Utils::readShort($this->get(2), false);
					$this->data["meta"] = Utils::readShort($this->get(2), false);
					$this->data["slot"] = ord($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeShort($this->data["block"]);
					$this->raw .= Utils::writeShort($this->data["meta"]);
					$this->raw .= chr($this->data["slot"]);
				}
				break;
			case MC_PLAYER_ARMOR_EQUIPMENT:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["slot0"] = ord($this->get(1));
					$this->data["slot1"] = ord($this->get(1));
					$this->data["slot2"] = ord($this->get(1));
					$this->data["slot3"] = ord($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= chr($this->data["slot0"]);
					$this->raw .= chr($this->data["slot1"]);
					$this->raw .= chr($this->data["slot2"]);
					$this->raw .= chr($this->data["slot3"]);
				}
				break;
			case MC_INTERACT:
				if($this->c === false){
					$this->data["action"] = Utils::readByte($this->get(1));
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["target"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeByte($this->data["action"]);
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeInt($this->data["target"]);
				}
				break;
			case MC_USE_ITEM:
				if($this->c === false){
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["y"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["face"] = Utils::readInt($this->get(4));
					$this->data["block"] = Utils::readShort($this->get(2));
					$this->data["meta"] = Utils::readByte($this->get(1));
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["fx"] = Utils::readFloat($this->get(4));
					$this->data["fy"] = Utils::readFloat($this->get(4));
					$this->data["fz"] = Utils::readFloat($this->get(4));
					$this->data["posX"] = Utils::readFloat($this->get(4));
					$this->data["posY"] = Utils::readFloat($this->get(4));
					$this->data["posZ"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["y"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= Utils::writeInt($this->data["face"]);
					$this->raw .= Utils::writeShort($this->data["block"]);
					$this->raw .= Utils::writeByte($this->data["meta"]);
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["fx"]);
					$this->raw .= Utils::writeFloat($this->data["fy"]);
					$this->raw .= Utils::writeFloat($this->data["fz"]);	
					$this->raw .= Utils::writeFloat($this->data["posX"]);
					$this->raw .= Utils::writeFloat($this->data["posY"]);
					$this->raw .= Utils::writeFloat($this->data["posZ"]);	
				}
				break;
			case MC_PLAYER_ACTION:
				if($this->c === false){
					$this->data["action"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["y"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["face"] = Utils::readInt($this->get(4));
					$this->data["eid"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["action"]);
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["y"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= Utils::writeInt($this->data["face"]);
					$this->raw .= Utils::writeInt($this->data["eid"]);
				}
				break;
			case MC_SET_ENTITY_DATA:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["metadata"] = Utils::readMetadata($this->get(true));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeMetadata($this->data["metadata"]);
				}
				break;
			case MC_SET_ENTITY_MOTION:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["speedX"] = Utils::readShort($this->get(2));
					$this->data["speedY"] = Utils::readShort($this->get(2));
					$this->data["speedZ"] = Utils::readShort($this->get(2));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeShort($this->data["speedX"]);
					$this->raw .= Utils::writeShort($this->data["speedY"]);
					$this->raw .= Utils::writeShort($this->data["speedZ"]);
				}
				break;
			case MC_HURT_ARMOR:
				if($this->c === false){
					$this->data["health"] = Utils::readByte($this->get(1));
				}else{
					$this->raw .= Utils::writeByte($this->data["health"]);
				}
				break;
			case MC_SET_HEALTH:
				if($this->c === false){
					$this->data["health"] = Utils::readByte($this->get(1));
				}else{
					$this->raw .= Utils::writeByte($this->data["health"]);
				}
				break;
			case MC_SET_SPAWN_POSITION:
				if($this->c === false){
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["y"] = ord($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= chr($this->data["y"]);
				}
				break;
			case MC_ANIMATE:
				if($this->c === false){
					$this->data["action"] = Utils::readByte($this->get(1));
					$this->data["eid"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeByte($this->data["action"]);
					$this->raw .= Utils::writeInt($this->data["eid"]);
				}
				break;
			case MC_RESPAWN:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
				}
				break;
			case MC_SEND_INVENTORY:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["windowid"] = ord($this->get(1));
					$this->data["count"] = Utils::readShort($this->get(2), false);
					$this->data["slots"] = array();
					for($s = 0; $s < $this->data["count"] and !$this->feof(); ++$s){
						$this->data["slots"][$s] = Utils::readSlot($this);
					}
					if($this->data["windowid"] === 1){ //Armor is also sent
						$this->data["armor"] = array(
							Utils::readSlot($this),
							Utils::readSlot($this),
							Utils::readSlot($this),
							Utils::readSlot($this)
						);
					}
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= chr($this->data["windowid"]);
					$this->raw .= Utils::writeShort(count($this->data["slots"]));
					foreach($this->data["slots"] as $slot){
						$this->raw .= Utils::writeSlot($slot);
					}
					if($this->data["windowid"] === 1 and isset($this->data["armor"])){
						$this->raw .= Utils::writeSlot($this->data["armor"][0]);
						$this->raw .= Utils::writeSlot($this->data["armor"][1]);
						$this->raw .= Utils::writeSlot($this->data["armor"][2]);
						$this->raw .= Utils::writeSlot($this->data["armor"][3]);
					}
				}
				break;
			case MC_DROP_ITEM:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["unknown1"] = ord($this->get(1));
					$this->data["block"] = Utils::readShort($this->get(2), false);
					$this->data["stack"] = ord($this->get(1));
					$this->data["meta"] = Utils::readShort($this->get(2), false);
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= chr($this->data["unknown1"]);
					$this->raw .= Utils::writeShort($this->data["block"]);
					$this->raw .= chr($this->data["stack"]);
					$this->raw .= Utils::writeShort($this->data["meta"]);
				}
				break;
			case MC_CONTAINER_OPEN:
				if($this->c === false){
					$this->data["windowid"] = ord($this->get(1));
					$this->data["type"] = ord($this->get(1));
					$this->data["slots"] = ord($this->get(1));
					
					//$this->data["title"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= chr($this->data["windowid"]);
					$this->raw .= chr($this->data["type"]);
					$this->raw .= chr($this->data["slots"]);
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["y"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
				}
				break;
			case MC_CONTAINER_CLOSE:
				if($this->c === false){
					$this->data["windowid"] = ord($this->get(1));
				}else{
					$this->raw .= chr($this->data["windowid"]);
				}
				break;
			case MC_CONTAINER_SET_SLOT:
				if($this->c === false){
					$this->data["windowid"] = ord($this->get(1));
					$this->data["slot"] = Utils::readShort($this->get(2), false);
					$this->data["block"] = Utils::readShort($this->get(2), false);
					$this->data["stack"] = ord($this->get(1));
					$this->data["meta"] = Utils::readShort($this->get(2), false);
				}else{
					$this->raw .= chr($this->data["windowid"]);
					$this->raw .= Utils::writeShort($this->data["slot"]);
					$this->raw .= Utils::writeShort($this->data["block"]);
					$this->raw .= chr($this->data["stack"]);
					$this->raw .= Utils::writeShort($this->data["meta"]);
				}
				break;
			case MC_CONTAINER_SET_CONTENT:
				if($this->c === false){
					$this->data["windowid"] = ord($this->get(1));
					$this->data["count"] = Utils::readShort($this->get(2), false);
					$this->data["slots"] = array();
					for($s = 0; $s < $this->data["count"] and !$this->feof(); ++$s){
						$this->data["slots"][$s] = Utils::readSlot($this);
					}
				}else{
					$this->raw .= chr($this->data["windowid"]);
					$this->raw .= Utils::writeShort(count($this->data["slots"]));
					foreach($this->data["slots"] as $slot){
						$this->raw .= Utils::writeSlot($slot);
					}
				}
				break;
			case MC_CONTAINER_SET_DATA:
				if($this->c === false){
					$this->data["windowid"] = ord($this->get(1));
					$this->data["property"] = Utils::readShort($this->get(2));
					$this->data["value"] = Utils::readShort($this->get(2));
				}else{
					$this->raw .= chr($this->data["windowid"]);
					$this->raw .= Utils::writeShort($this->data["property"]);
					$this->raw .= Utils::writeShort($this->data["value"]);
				}
				break;
			case MC_CLIENT_MESSAGE:
				if($this->c === false){
					$this->data["message"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["message"])).$this->data["message"];
				}
				break;
			case MC_ADVENTURE_SETTINGS:
				if($this->c === false){
					$this->data["flags"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["flags"]);
				}
				break;
			case MC_ENTITY_DATA:
				if($this->c === false){
					$this->data["x"] = Utils::readShort($this->get(2));
					$this->data["y"] = ord($this->get(1));
					$this->data["z"] = Utils::readShort($this->get(2));
					$this->data["namedtag"] = $this->get(true);
				}else{
					$this->raw .= Utils::writeShort($this->data["x"]);
					$this->raw .= chr($this->data["y"]);
					$this->raw .= Utils::writeShort($this->data["z"]);
					$this->raw .= $this->data["namedtag"];
				}
				break;
			default:
				if($this->c === false){
					console("[DEBUG] Received unknown Data Packet ID 0x".dechex($pid), true, true, 2);
				}else{
					console("[DEBUG] Sent unknown Data Packet ID 0x".dechex($pid), true, true, 2);
				}
				break;
		}
	}

}