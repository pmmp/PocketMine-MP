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

class AirBlock extends TransparentBlock{
	public function __construct(){
		parent::__construct(AIR, 0, "Air");
		$this->isActivable = false;
		$this->breakable = false;
		$this->isFlowable = true;
		$this->isTransparent = true;
		$this->isReplaceable = true;
		$this->isPlaceable = false;
		$this->inWorld = false;
		$this->hasPhysics = false;
	}
	
}