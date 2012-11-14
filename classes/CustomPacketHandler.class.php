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
	var $data, $name = "";

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
			case 0x400090:
			case "clientHandshake":
				$this->name = "clientHandshake";
				if($this->c === false){
					$this->data["counter"] = Utils::readTriad($this->get(3));
					$this->data["id"] = ord($this->get(1));
					$this->data["clientID"] = $this->get(8);
					$this->data["unknown1"] = $this->get(1);
					$this->data["unknown2"] = $this->get(4);
					$this->data["session"] = $this->get(4);
				}else{
					$this->raw .= Utils::writeTriad(0); //counter
					$this->raw .= chr(9);
					$this->raw .= $this->data["clientID"];
					$this->raw .= "\x00";
					$this->raw .= "\x00\x00\x00\x00";
					$this->raw .= $this->data["session"];
				}
				break;
			case 0x600300:
			case "serverHandshake":
				$this->name = "serverHandshake";
				if($this->c === false){
					$this->data["counter"] = Utils::readTriad($this->get(3));
					$this->data["id"] = ord($this->get(1));
					$this->data["unknown1"] = $this->get(4);
					$this->data["cookie"] = $this->get(4); // 043f57ff
					$this->data["unknown2"] = ord($this->get(1));
					$this->data["port"] = Utils::readShort($this->get(2));
					$this->data["dataArray"] = Utils::readDataArray($this->get(true, false), 10);
					$this->data["unknown3"] = $this->get(1);
					$this->data["unknown4"] = $this->get(4);
					$this->data["session"] = $this->get(4);
					$this->data["unknown5"] = $this->get(8);
				}else{
					$this->raw .= Utils::writeTriad(0); //counter
					$this->raw .= chr(0);
					$this->raw .= "\x00\x00\x00\x10";
					$this->raw .= "\x04\x3f\x57\xff";
					$this->raw .= "\x00";
					$this->raw .= "\x7f";
					$this->raw .= Utils::writeShort($this->data["port"]);
				}
				break;
		}
	}

}