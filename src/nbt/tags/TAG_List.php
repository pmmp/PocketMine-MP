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

class NBTTag_List extends NamedNBTTag{
	
	public function getType(){
		return self::TAG_List;
	}
	
	public function read(NBT $nbt){
		$this->value = array();
		$tagId = $nbt->getByte();
		$this->value[-1] = $tagId;
		$size = $nbt->getInt();
		for($i = 0; $i < $size and !$nbt->feof(); ++$i){
			switch($tagId){
				case NBTTag::TAG_Byte:
					$tag = new NBTTag_Byte(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Byte:
					$tag = new NBTTag_Byte(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Short:
					$tag = new NBTTag_Short(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Int:
					$tag = new NBTTag_Int(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Long:
					$tag = new NBTTag_Long(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Float:
					$tag = new NBTTag_Float(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Double:
					$tag = new NBTTag_Double(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Byte_Array:
					$tag = new NBTTag_Byte_Array(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_String:
					$tag = new NBTTag_String(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_List:
					$tag = new NBTTag_List(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Compound:
					$tag = new NBTTag_Compound(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Int_Array:
					$tag = new NBTTag_Int_Array(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
			}
		}
	}
	
	public function write(NBT $nbt){
		$nbt->putByte($this->value[-1]);
		$nbt->putInt(count($this->value) - 1);
		foreach($this->value as $tag){
			if($tag instanceof NBTTag){
				$nbt->writeTag($tag);
			}
		}
	}
}