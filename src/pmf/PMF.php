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

define("PMF_CURRENT_VERSION", 0x01);

class PMF{
	protected $fp;
	protected $file;
	private $version;
	private $type;
	
	public function __construct($file, $new = false, $type = 0, $version = PMF_CURRENT_VERSION){
		if($new === true){
			$this->create($file, $type, $version);
		}else{
			if($this->load($file) !== true){
				$this->parseInfo();
			}
		}
	}
	
	public function getVersion(){
		return $this->version;
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function load($file){
		$this->close();
		$this->file = $file;
		if(($this->fp = @fopen($file, "c+b")) !== false){
			fseek($this->fp, 0, SEEK_END);
			if(ftell($this->fp) >= 5){ //Header + 2 Bytes
				@flock($this->fp, LOCK_EX);
				return true;
			}
			$this->close();
		}
		return false;
	}
	
	public function parseInfo(){
		$this->seek(0);
		if(fread($this->fp, 3) !== "PMF"){
			return false;			
		}
		$this->version = ord($this->read(1));
		switch($this->version){
			case 0x01:
				$this->type = ord($this->read(1));
				break;
			default:
				console("[ERROR] Tried loading non-supported PMF version ".$this->version." on file ".$this->file);
				return false;
		}
		return true;
	}
	
	public function getFile(){
		return $this->file;
	}
	
	public function close(){
		unset($this->version, $this->type, $this->file);
		if(is_object($this->fp)){
			@flock($this->fp, LOCK_UN);
			fclose($this->fp);
		}
	}
	
	public function create($file, $type, $version = PMF_CURRENT_VERSION){
		$this->file = $file;
		@mkdir(dirname($this->file), 0755, true);
		if(!is_resource($this->fp)){
			if(($this->fp = @fopen($file, "c+b")) === false){
				return false;
			}
		}
		$this->seek(0);
		$this->write("PMF" . chr((int) $version) . chr((int) $type));
	}
	
	public function read($length){
		if($length <= 0){
			return "";
		}
		if(is_resource($this->fp)){
			return fread($this->fp, (int) $length);
		}
		return false;
	}
	
	public function write($string, $length = false){
		if(is_resource($this->fp)){
			return ($length === false ? fwrite($this->fp, $string) : fwrite($this->fp, $string, $length));
		}
		return false;
	}
	
	public function seek($offset, $whence = SEEK_SET){
		if(is_resource($this->fp)){
			return fseek($this->fp, (int) $offset, (int) $whence);
		}
		return false;
	}

}