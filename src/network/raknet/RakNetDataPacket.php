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

abstract class RakNetDataPacket extends stdClass{
	private $offset = 0;
	public $buffer = b"";
	
	public $reliability = 0;
	public $hasSplit = false;
	public $messageIndex;
	public $orderIndex;
	public $orderChannel;
	public $splitCount;
	public $splitID;
	public $splitIndex;

	
	abstract public function pid();
	
	abstract public function encode();
	
	abstract public function decode();

	protected function reset(){
		$this->setBuffer(chr($this->pid()));
	}
	
	public function setBuffer($buffer = ""){
		$this->buffer = $buffer;
		$this->offset = 0;
	}
	
	public function getBuffer(){
		return $this->buffer;
	}
	
	protected function get($len){
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
	
	protected function put($str){
		$this->buffer .= $str;
	}
	
	protected function getLong($unsigned = false){
		return Utils::readLong($this->get(8), $unsigned);
	}

	protected function putLong($v){
		$this->buffer .= Utils::writeLong($v);
	}
	
	protected function getInt($unsigned = false){
		return Utils::readInt($this->get(4), $unsigned);
	}
	
	protected function putInt($v){
		$this->buffer .= Utils::writeInt($v);
	}
	
	protected function getShort($unsigned = false){
		return Utils::readShort($this->get(2), $unsigned);
	}
	
	protected function putShort($v){
		$this->buffer .= Utils::writeShort($v);
	}
	
	protected function getFloat(){
		return Utils::readFloat($this->get(4));
	}

	protected function putFloat($v){
		$this->buffer .= Utils::writeFloat($v);
	}

	protected function getTriad(){
		return Utils::readTriad($this->get(3));
	}

	protected function putTriad($v){
		$this->buffer .= Utils::writeTriad($v);
	}
	
	
	protected function getLTriad(){
		return Utils::readTriad(strrev($this->get(3)));
	}
	
	protected function putLTriad($v){
		$this->buffer .= strrev(Utils::writeTriad($v));
	}
	
	protected function getByte(){
		return ord($this->get(1));
	}
	
	protected function putByte($v){
		$this->buffer .= chr($v);
	}

	protected function getDataArray($len = 10){
		$data = array();
		for($i = 1; $i <= $len and !$this->feof(); ++$i){
			$data[] = $this->get($this->getTriad());
		}
		return $data;
	}

	protected function putDataArray(array $data = array()){
		foreach($data as $v){
			$this->putTriad(strlen($v));
			$this->put($v);
		}
	}
	
	protected function getSlot(){
		$id = $this->getShort();
		$cnt = $this->getByte();
		return BlockAPI::getItem(
			$id,
			$this->getShort(),
			$cnt
		);
	}
	
	protected function putSlot(Item $item){
		$this->putShort($item->getID());
		$this->putByte($item->count);
		$this->putShort($item->getMetadata());
	}
	
	protected function getString(){
		return $this->get($this->getShort(true));
	}
	
	protected function putString($v){
		$this->putShort(strlen($v));
		$this->put($v);
	}
	
	protected function feof(){
		return !isset($this->buffer{$this->offset});
	}
}