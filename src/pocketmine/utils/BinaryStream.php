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

namespace pocketmine\utils;

#include <rules/DataPacket.h>

#ifndef COMPILE
use pocketmine\utils\Binary;
#endif

use pocketmine\item\Item;
use pocketmine\utils\UUID;


class BinaryStream extends \stdClass{

	public $offset = 0;
	public $buffer = "";

	protected function reset(){
		$this->buffer = "";
		$this->offset = 0;
	}

	public function setBuffer($buffer = null, $offset = 0){
		$this->buffer = $buffer;
		$this->offset = (int) $offset;
	}

	public function getOffset(){
		return $this->offset;
	}

	public function getBuffer(){
		return $this->buffer;
	}

	protected function get($len){
		if($len < 0){
			$this->offset = strlen($this->buffer) - 1;
			return "";
		}elseif($len === true){
			return substr($this->buffer, $this->offset);
		}

		return $len === 1 ? $this->buffer{$this->offset++} : substr($this->buffer, ($this->offset += $len) - $len, $len);
	}

	protected function put($str){
		$this->buffer .= $str;
	}

	protected function getLong(){
		return Binary::readLong($this->get(8));
	}

	protected function putLong($v){
		$this->buffer .= Binary::writeLong($v);
	}

	protected function getInt(){
		return Binary::readInt($this->get(4));
	}

	protected function putInt($v){
		$this->buffer .= Binary::writeInt($v);
	}

	protected function getShort($signed = true){
		return $signed ? Binary::readSignedShort($this->get(2)) : Binary::readShort($this->get(2));
	}

	protected function putShort($v){
		$this->buffer .= Binary::writeShort($v);
	}

	protected function getFloat(){
		return Binary::readFloat($this->get(4));
	}

	protected function putFloat($v){
		$this->buffer .= Binary::writeFloat($v);
	}

	protected function getTriad(){
		return Binary::readTriad($this->get(3));
	}

	protected function putTriad($v){
		$this->buffer .= Binary::writeTriad($v);
	}


	protected function getLTriad(){
		return Binary::readLTriad($this->get(3));
	}

	protected function putLTriad($v){
		$this->buffer .= Binary::writeLTriad($v);
	}

	protected function getByte(){
		return ord($this->buffer{$this->offset++});
	}

	protected function putByte($v){
		$this->buffer .= chr($v);
	}

	protected function getDataArray($len = 10){
		$data = [];
		for($i = 1; $i <= $len and !$this->feof(); ++$i){
			$data[] = $this->get($this->getTriad());
		}

		return $data;
	}

	protected function putDataArray(array $data = []){
		foreach($data as $v){
			$this->putTriad(strlen($v));
			$this->put($v);
		}
	}

	protected function getUUID(){
		return UUID::fromBinary($this->get(16));
	}

	protected function putUUID(UUID $uuid){
		$this->put($uuid->toBinary());
	}

	protected function getSlot(){
		$id = $this->getShort(true);
		
		if($id <= 0){
			return Item::get(0, 0, 0);
		}
		
		$cnt = $this->getByte();
		
		$data = $this->getShort();
		
		$nbtLen = $this->getShort();
		
		$nbt = "";
		
		if($nbtLen > 0){
			$nbt = $this->get($nbtLen);
		}

		return Item::get(
			$id,
			$data,
			$cnt,
			$nbt
		);
	}

	protected function putSlot(Item $item){
		if($item->getId() === 0){
			$this->putShort(0);
			return;
		}
		
		$this->putShort($item->getId());
		$this->putByte($item->getCount());
		$this->putShort($item->getDamage() === null ? -1 : $item->getDamage());
		$nbt = $item->getCompoundTag();
		$this->putShort(strlen($nbt));
		$this->put($nbt);
		
	}

	protected function getString(){
		return $this->get($this->getShort());
	}

	protected function putString($v){
		$this->putShort(strlen($v));
		$this->put($v);
	}

	protected function feof(){
		return !isset($this->buffer{$this->offset});
	}
}
