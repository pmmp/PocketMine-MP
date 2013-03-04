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

class SignItem extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockAPI::get(SIGN_POST);
		$this->maxStackSize = 16;
		parent::__construct(SIGN, 0, $count, "Sign");
	}
}