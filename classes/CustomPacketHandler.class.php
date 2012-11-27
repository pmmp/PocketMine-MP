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
					$this->data["unknown1"] = Utils::readInt($this->get(4));
				}else{
					$this->raw .= Utils::writeInt($this->data["unknown1"]);
				}				
				break;
			case 0x85:
				if($this->c === false){	
					$this->data["message"] = $this->get(ord($this->get(1)));
				}else{
					$this->raw .= chr(strlen($this->data["message"])).$this->data["message"];
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
					$this->data["seed"] = $this->get(4);
					$this->data["spawnX"] = Utils::readInt($this->get(4));
					$this->data["spawnY"] = Utils::readInt($this->get(4));
					$this->data["spawnZ"] = Utils::readInt($this->get(4));
					$this->data["x"] = Utils::readFloat($this->get(4));
					$this->data["y"] = Utils::readFloat($this->get(4));
					$this->data["z"] = Utils::readFloat($this->get(4));
				}else{
					$this->raw .= $this->data["seed"];
					$this->raw .= Utils::writeInt($this->data["spawnX"]);
					$this->raw .= Utils::writeInt($this->data["spawnY"]);
					$this->raw .= Utils::writeInt($this->data["spawnZ"]);
					$this->raw .= Utils::writeFloat($this->data["x"]);
					$this->raw .= Utils::writeFloat($this->data["y"]);
					$this->raw .= Utils::writeFloat($this->data["z"]);
				}			
				break;
			case 0x09:
				if($this->c === false){	
					$this->data["clientID"] = $this->get(8);
					$this->data["unknown1"] = $this->get(1);
					$this->data["unknown2"] = $this->get(4);
					$this->data["session"] = $this->get(4);
				}else{
					$this->raw .= $this->data["clientID"];
					$this->raw .= "\x00";
					$this->raw .= "\x00\x00\x00\x00";
					$this->raw .= $this->data["session"];
				}
				break;
			case 0x10:
				if($this->c === false){
					$this->data["cookie"] = $this->get(4); // 043f57ff
					$this->data["unknown1"] = $this->get(1);
					$this->data["port"] = Utils::readShort($this->get(2), false);
					$this->data["dataArray"] = Utils::readDataArray($this->get(true, false), 10, $offset);
					$this->get($offset);
					$this->data["unknown2"] = $this->get(7);
					$this->data["session"] = $this->get(4);
					$this->data["unknown3"] = $this->get(7);
				}else{
					$this->raw .= "\x04\x3f\x57\xff";
					$this->raw .= "\x00";
					$this->raw .= Utils::writeShort($this->data["port"]);
					$this->raw .= Utils::writeDataArray(array(
						"\x80\xff\xff\xfe",
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
					$this->raw .= "\x00\x00\x00\x00\x00\x00\x00";
					$this->raw .= $this->data["session"];
					$this->raw .= "\x00\x00\x00\x00\x00\x00\x00";
				}		
				break;
			case 0x13:
				if($this->c === false){
					$this->data["cookie"] = $this->get(4); // 043f57ff
					$this->data["unknown1"] = $this->get(1);
					$this->data["port"] = Utils::readShort($this->get(2), false);
					$this->data["dataArray0"] = $this->get(ord($this->get(1)));
					$this->data["dataArray"] = Utils::readDataArray($this->get(true, false), 9, $offset);
					$this->get($offset);
					$this->data["unknown2"] = $this->get(13);
				}else{
					$this->raw .= "\x04\x3f\x57\xff";
					$this->raw .= "\x3e";
					$this->raw .= Utils::writeShort($this->data["port"]);
					$w = array_shift($this->data["dataArray"]);
					$this->raw .= chr(strlen($w)).$w;
					$this->raw .= Utils::writeDataArray($this->data["dataArray"]);
					$this->raw .= "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
					$this->raw .= "\x00\xae\x21\x4e";
				}
				break;
			case 0x15:
				//null
				break;
			case 0x00:
				if($this->c === false){
					$this->data["payload"] = $this->get(8);
				}else{
					$this->raw .= $this->data["payload"];
				}				
				break;
		}
	}

}