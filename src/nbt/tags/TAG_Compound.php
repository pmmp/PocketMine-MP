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

class NBTTag_Compound extends NamedNBTTag{
	
	public function getType(){
		return NBTTag::TAG_Compound;
	}
	
	public function __get($name){
		return isset($this->value[$name]) ? $this->value[$name]->getValue() : false;
	}

	public function __set($name, $value){
		if(isset($this->value[$name])){
			$this->value[$name]->setValue($value);
		}
	}
	
	public function __isset($name){
		return isset($this->value[$name]);
	}
	
	public function __unset($name){
		unset($this->value[$name]);
	}
	
	public function read(NBT $nbt){
		$this->value = array();
		do{
			$tag = $nbt->readTag();
			if($tag instanceof NamedNBTTag and $tag->getName() !== ""){
				$this->value[$tag->getName()] = $tag;
			}else{
				$this->value[] = $tag;
			}
		}while(!($tag instanceof NBTTag_End) and !$nbt->feof());
	}
	
	public function write(NBT $nbt){
		foreach($this->value as $tag){
			$nbt->writeTag($tag);
		}
	}
}