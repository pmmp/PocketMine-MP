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


class Packet{
	private $struct, $sock;
	protected $pid, $packet;
	public $data, $raw;

	function __construct($pid, $struct, $data = ""){
		$this->pid = $pid;
		$this->offset = 1;
		$this->raw = $data;
		$this->data = array();
		if($data === ""){
			$this->addRaw(chr($pid));
		}
		$this->struct = $struct;
		$this->sock = $sock;
	}

	public function create($raw = false){
		foreach($this->struct as $field => $type){
			if(!isset($this->data[$field])){
				$this->data[$field] = "";
			}
			if($raw === true){
				$this->addRaw($this->data[$field]);
				continue;
			}
			if(is_int($type)){
				$this->addRaw($this->data[$field]);
				continue;
			}
			switch($type){
				case "special1":
					switch($this->pid){
						case 0xc0:
						case 0xa0:
							if($this->data[1] === false){
								$this->addRaw($this->data[$field]);
							}
							break;
						case 0x05:
							$this->addRaw($this->data[$field]);
							break;
					}
					break;
				case "customData":
					switch($this->data[1]){
						case 0x40:
							$reply = new CustomPacketHandler($this->data[$field]["id"], "", $this->data[$field], true);
							$this->addRaw(Utils::writeShort((strlen($reply->raw) + 1) << 3));
							$this->addRaw(Utils::writeTriad(strrev($this->data[$field]["count"])));
							$this->addRaw(chr($this->data[$field]["id"]));
							$this->addRaw($reply->raw);
							break;
						case 0x00:
							if($this->data[$field]["id"] !== false){
								$raw = new CustomPacketHandler($this->data[$field]["id"], "", $this->data[$field], true);
								$raw = $raw->raw;
								$this->addRaw(Utils::writeShort((strlen($raw) + 1) << 3));
								$this->addRaw(chr($this->data[$field]["id"]));
								$this->addRaw($raw);
							}else{
								$this->addRaw($this->data[$field]["raw"]);
							}
							break;
					}
					break;
				case "magic":
					$this->addRaw(MAGIC);
					break;
				case "float":
					$this->addRaw(Utils::writeFloat($this->data[$field]));
					break;
				case "triad":
					$this->addRaw(Utils::writeTriad($this->data[$field]));
					break;
				case "itriad":
					$this->addRaw(strrev(Utils::writeTriad($this->data[$field])));
					break;
				case "int":
					$this->addRaw(Utils::writeInt($this->data[$field]));
					break;
				case "double":
					$this->addRaw(Utils::writeDouble($this->data[$field]));
					break;
				case "long":
					$this->addRaw(Utils::writeLong($this->data[$field]));
					break;
				case "bool":
				case "boolean":
					$this->addRaw(Utils::writeBool($this->data[$field]));
					break;
				case "ubyte":
				case "byte":
					$this->addRaw(Utils::writeByte($this->data[$field]));
					break;
				case "short":
					$this->addRaw(Utils::writeShort($this->data[$field]));
					break;
				case "byteArray":
					$this->addRaw($this->data[$field]);
					break;
				case "string":
					$this->addRaw(Utils::writeShort(strlen($this->data[$field])));
					$this->addRaw($this->data[$field]);
					break;
				case "slotData":
					$this->addRaw(Utils::writeShort($this->data[$field][0]));
					if($this->data[$field][0]!=-1){
						$this->addRaw(Utils::writeByte($this->data[$field][1]));
						$this->addRaw(Utils::writeShort($this->data[$field][2]));
					}
					break;
				default:
					$this->addRaw(Utils::writeByte($this->data[$field]));
					break;
			}
		}
	}

	private function get($len = true){
		if($len === true){
			$data = substr($this->raw, $this->offset);
			$this->offset = strlen($this->raw);
			return $data;
		}
		$data = substr($this->raw, $this->offset, $len);
		$this->offset += $len;
		return $data;
	}

	protected function addRaw($str){
		$this->raw .= $str;
		return $str;
	}

	public function parse(){
		$continue = true;
		foreach($this->struct as $field => $type){
			if(is_int($type)){
				$this->data[] = $this->get($type);
				continue;
			}
			switch($type){
				case "special1":
					switch($this->pid){
						case 0xc0:
						case 0xa0:
							if($this->data[1] === false){
								$this->data[] = $this->get(3);
							}
							break;
						case 0x05:
							$this->data[] = $this->get(true);
							break;
					}
					break;
				case "customData":
					$d = new SerializedPacketHandler($this->data[1], $this->get(true));
					if(isset($d->data["packets"])){
						$this->data["packets"] = $d->data["packets"];
					}else{
						$this->data[] = $d->data;
					}
					break;
				case "magic":
					$this->data[] = $this->get(16);
					break;
				case "triad":
					$this->data[] = Utils::readTriad($this->get(3));
					break;
				case "itriad":
					$this->data[] = Utils::readTriad(strrev($this->get(3)));
					break;
				case "int":
					$this->data[] = Utils::readInt($this->get(4));
					break;
				case "string":
					$this->data[] = $this->get(Utils::readShort($this->get(2)));
					break;
				case "long":
					$this->data[] = Utils::readLong($this->get(8));
					break;
				case "byte":
					$this->data[] = Utils::readByte($this->get(1));
					break;
				case "ubyte":
					$this->data[] = ord($this->get(1));
					break;
				case "float":
					$this->data[] = Utils::readFloat($this->get(4));
					break;
				case "double":
					$this->data[] = Utils::readDouble($this->get(8));
					break;
				case "ushort":
					$this->data[] = Utils::readShort($this->get(2), false);
					break;
				case "short":
					$this->data[] = Utils::readShort($this->get(2));
					break;
				case "bool":
				case "boolean":
					$this->data[] = Utils::readBool($this->get(1));
					break;
			}
			if($continue === false){
				break;
			}
		}
	}




}