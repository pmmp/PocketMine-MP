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

class StackableArray{
	public $counter = 0;
	public function run(){}
}

class Async extends Thread {
	public function __construct($method, $params = array()){
		$this->method = $method;
		$this->params = $params;
		$this->result = null;
		$this->joined = false;
	}

	public function run(){
		if(($this->result=call_user_func_array($this->method, $this->params))){
			return true;
		}else{
			return false;
		}
	}

	public static function call($method, $params = array()){
		$thread = new Async($method, $params);
		if($thread->start()){
			return $thread;
		}
	}

	public function __toString(){
		if(!$this->joined){
			$this->joined = true;
			$this->join();
		}

		return $this->result;
	}
}