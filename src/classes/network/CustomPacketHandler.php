<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

class CustomPacketHandler{
	var $offset, $raw, $c, $data, $name = "";

	private function get($len = true, $check = true){
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

	public function __construct($pid, $raw = "", $data = array(), $create = false){
		$this->raw = $raw;
		$this->data = $data;
		$this->offset = 0;
		$this->c = (bool) $create;
		switch($pid){
			case MC_KEEP_ALIVE:
				if($this->c === false){
					$this->data["payload"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= Utils::writeLong($this->data["payload"]);
				}
				break;
			case 0x03:
				if($this->c === false){
					$this->data["unknown1"] = Utils::readLong($this->get(8));
					$this->data["unknown2"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= Utils::writeLong($this->data["unknown1"]);
					$this->raw .= Utils::writeLong($this->data["unknown2"]);
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
					$this->data["unknown1"] = $this->get(2);
					$this->data["session"] = Utils::readLong($this->get(8));
					$this->data["session2"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= "\x04\x3f\x57\xfe";
					$this->raw .= "\xcd";
					$this->raw .= Utils::writeShort($this->data["port"]);
					$this->raw .= Utils::writeDataArray(array(
						"\xff\xff\xff\xff",
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
					$this->data["unknown1"] = $this->get(2);
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
			case MC_DISCONNECT:
				//null
				break;
			case 0x18:
			case 0xa9:
				//null
				break;
			case MC_LOGIN:
				if($this->c === false){
					$this->data["username"] = $this->get(Utils::readShort($this->get(2), false));
					$this->data["maxX"] = Utils::readInt($this->get(4));
					$this->data["maxY"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["username"])).$this->data["username"];
					$this->raw .= "\x00\x00\x00\x08\x00\x00\x00\x08";
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
					$this->data["message"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["message"])).$this->data["message"];
				}
				break;
			case MC_SET_TIME:
				if($this->c === false){
					$this->data["time"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["time"]);
				}
				break;
			case MC_START_GAME:
				if($this->c === false){
					$this->data["seed"] = Utils::readInt($this->get(4));
					$this->data["unknown1"] = Utils::readInt($this->get(4));
					$this->data["gamemode"] = Utils::readInt($this->get(4));
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["seed"]);
					$this->raw .= Utils::writeInt($this->data["unknown1"]);
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
					$this->data["metadata"] = Utils::readMetadata($this->get(true));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeInt($this->data["type"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
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
					$this->data["metadata"] = Utils::readMetadata($this->get(true));
				}else{
					$this->raw .= Utils::writeLong($this->data["clientID"]);
					$this->raw .= Utils::writeShort(strlen($this->data["username"])).$this->data["username"];
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::writeMetadata($this->data["metadata"]);
				}
				break;
			case MC_ADD_ENTITY:
				if($this->c === false){
					$this->data["eid"] = Utils::readInt($this->get(4));
					$this->data["type"] = ord($this->get(1));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= chr($this->data["type"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
					$this->raw .= Utils::hexToStr("000000020000ffd30000");//Utils::writeInt(0);
					/*$this->raw .= Utils::writeShort(0);
					$this->raw .= Utils::writeShort(0);
					$this->raw .= Utils::writeShort(0);*/
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
			case MC_MOVE_PLAYER:
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
			case MC_REMOVE_BLOCK:
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
			case MC_EXPLOSION:
				if($this->c === false){
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
					$this->data["radius"] = Utils::readFloat($this->get(4));
					$this->data["count"] = Utils::readInt($this->get(4));
					$this->data["records"] = array();
					for($r = 0; $r < $this->data["count"]; ++$r){
						$this->data["records"][] = array(Utils::readByte($this->get(1)), Utils::readByte($this->get(1)), Utils::readByte($this->get(1)));
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
							$this->raw .= Utils::writeByte($record[0]) . Utils::writeByte($record[1]) . Utils::writeByte($record[2]);
						}
					}
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
				}else{
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeShort($this->data["block"]);
					$this->raw .= Utils::writeShort($this->data["meta"]);
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
				}else{
					/*$this->raw .= Utils::writeByte($this->data["action"]);
					$this->raw .= Utils::writeInt($this->data["eid"]);
					$this->raw .= Utils::writeInt($this->data["target"]);*/
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
			case MC_SET_HEALTH:
				if($this->c === false){
					$this->data["health"] = Utils::readByte($this->get(1));
				}else{
					$this->raw .= Utils::writeByte($this->data["health"]);
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
					$this->data["slots"] = Utils::readShort($this->get(2), false);
					$this->data["title"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= chr($this->data["windowid"]);
					$this->raw .= chr($this->data["type"]);
					$this->raw .= Utils::writeShort($this->data["slots"]);
					$this->raw .= Utils::writeShort(strlen($this->data["title"])).$this->data["title"];
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
			case MC_CLIENT_MESSAGE:
				if($this->c === false){
					$this->data["message"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["message"])).$this->data["message"];
				}
				break;
			case MC_SIGN_UPDATE:
				if($this->c === false){
					$this->data["x"] = Utils::readShort($this->get(2));
					$this->data["y"] = ord($this->get(1));
					$this->data["z"] = Utils::readShort($this->get(2));
					for($i = 0; $i < 4; ++$i){
						$this->data["line$i"] = $this->get(Utils::readLShort($this->get(2), false));
					}
				}else{
					$this->raw .= Utils::writeShort($this->data["x"]);
					$this->raw .= chr($this->data["y"]);
					$this->raw .= Utils::writeShort($this->data["z"]);
					for($i = 0; $i < 4; ++$i){
						$this->raw .= Utils::writeLShort(strlen($this->data["line$i"])).$this->data["line$i"];
					}
				}
				break;
			case MC_ADVENTURE_SETTINGS:
				if($this->c === false){
					$this->data["x"] = Utils::readShort($this->get(2));
					$this->data["y"] = ord($this->get(1));
					$this->data["z"] = Utils::readShort($this->get(2));
					for($i = 0; $i < 4; ++$i){
						$this->data["line$i"] = $this->get(Utils::readLShort($this->get(2), false));
					}
				}else{
					$this->raw .= $this->data["unknown1"];
					$this->raw .= $this->data["unknown2"];
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