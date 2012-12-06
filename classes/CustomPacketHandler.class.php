<?php

/*

           -
         /   \
      /         \
   /    POCKET     \
/    MINECRAFT PHP    \
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
			case 0x00:
				if($this->c === false){
					$this->data["payload"] = Utils::readLong($this->get(8));
				}else{
					$this->raw .= Utils::writeLong($this->data["payload"]);
				}				
				break;
			case 0x09:
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
			case 0x10:
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
			case 0x13:
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
			case 0x15:
				//null
				break;
			case 0x18:
				//null
				break;
			case 0x82:
				if($this->c === false){	
					$this->data["username"] = $this->get(Utils::readShort($this->get(2), false));
					$this->data["unknown1"] = Utils::readInt($this->get(4));
					$this->data["unknown2"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["username"])).$this->data["username"];
					$this->raw .= "\x00\x00\x00\x07\x00\x00\x00\x07";
				}
				break;
			case 0x83:
				if($this->c === false){	
					$this->data["status"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["status"]);
				}
				break;
			case 0x84:
				if($this->c === false){	
					$this->data["status"] = ord($this->get(1));
				}else{
					$this->raw .= chr($this->data["status"]);
				}				
				break;
			case 0x85:
				if($this->c === false){	
					$this->data["message"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["message"])).$this->data["message"];
				}
				break;
			case 0x86:
				if($this->c === false){	
					$this->data["time"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["time"]);
				}
				break;
			case 0x87:
				if($this->c === false){	
					$this->data["seed"] = Utils::readInt($this->get(4));
					$this->data["unknown1"] = Utils::readInt($this->get(4));
					$this->data["gamemode"] = Utils::readInt($this->get(4));
					$this->data["unknown2"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["seed"]);
					$this->raw .= Utils::writeInt($this->data["unknown1"]);
					$this->raw .= Utils::writeInt($this->data["gamemode"]);
					$this->raw .= Utils::writeInt($this->data["unknown2"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
				}			
				break;				
			case 0x94: //MovePlayer
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
			case 0x96: //RemoveBlock
				if($this->c === false){	
					$this->data["x"] = Utils::readInt($this->get(4));
					$this->data["y"] = Utils::readInt($this->get(4));
					$this->data["z"] = Utils::readInt($this->get(4));
					$this->data["face"] = ord($this->get(1));
				}else{
					$this->raw .= Utils::writeInt($this->data["x"]);
					$this->raw .= Utils::writeInt($this->data["y"]);
					$this->raw .= Utils::writeInt($this->data["z"]);
					$this->raw .= chr($this->data["face"]);
				}				
				break;
			case 0xa5: //SetHealth
				if($this->c === false){	
					$this->data["health"] = ord($this->get(1));
				}else{
					$this->raw .= chr($this->data["health"]);
				}					
				break;
			case 0xb1:
				if($this->c === false){	
					$this->data["message"] = $this->get(Utils::readShort($this->get(2), false));
				}else{
					$this->raw .= Utils::writeShort(strlen($this->data["message"])).$this->data["message"];
				}
				break;
		}
	}

}