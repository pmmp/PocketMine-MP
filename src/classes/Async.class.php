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

class Async extends Thread {
	/**
	* Provide a passthrough to call_user_func_array
	**/
	public function __construct($method, $params = array()){
		$this->method = $method;
		$this->params = $params;
		$this->result = null;
		$this->joined = false;
	}

	/**
	* The smallest thread in the world
	**/
	public function run(){
		if(($this->result=call_user_func_array($this->method, $this->params))){
			return true;
		}else{
			return false;
		}
	}

	/**
	* Static method to create your threads from functions ...
	**/
	public static function call($method, $params = array()){
		$thread = new Async($method, $params);
		if($thread->start()){
			return $thread;
		} /** else throw Nastyness **/
	}

	/**
	* Do whatever, result stored in $this->result, don't try to join twice
	**/
	public function __toString(){
		if(!$this->joined) {
			$this->joined = true;
			$this->join();
		}

		return $this->result;
	}
}