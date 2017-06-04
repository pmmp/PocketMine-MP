<?php

namespace pocketmine\block;

use pocketmine\item\Item;

class EndGateway extends Transparent {
    
    protected $id = self::END_GATEWAY;
    
    public function __construct(){
	}
    
   
    
    public function getName() {
        return 'End Gateway';
    }
    
    public function getLightLevel(){
		return 15;
	}
    public function getHardness(){
        return -1;
    }
    public function getResistance(){
        return 18000000;
    }
    public function isBreakable(Item $item){
        return false;
    }
}
