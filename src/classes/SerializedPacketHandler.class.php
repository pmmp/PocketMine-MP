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

class SerializedPacketHandler{
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
			case 0x60:
			case 0x40:
			case 0x00:
				if($this->c === false){
					$this->data["packets"] = array();
					$i = 0;
					while($this->offset < strlen($this->raw)){
						if($i > 0){
							$pid = ord($this->get(1));
						}

						$len = ceil(Utils::readShort($this->get(2), false) / 8); //Utils::readShort($this->get(2), false) >> 3;
						if($pid !== 0x00){
							$c = Utils::readTriad(strrev($this->get(3)));
						}
						if($pid === 0x60 and $i === 0){
							$this->data["unknown1"] = $this->get(4);
						}
						$id = ord($this->get(1));
						$raw = $this->get($len - 1);
						$pk = new CustomPacketHandler($id, $raw);
						$pk->data["length"] = $len;
						$pk->data["id"] = $id;
						if($pid !== 0x00){
							$pk->data["counter"] = $c;
						}
						$pk->data["packetName"] = $pk->name;
						$this->data["packets"][] = array($pid, $pk->data, $raw);
						++$i;
					}
				}
				break;
		}
	}

}