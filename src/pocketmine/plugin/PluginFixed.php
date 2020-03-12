<?php

namespace pocketmine\plugin;

abstract class PluginFixed extends PluginBase {

  public function onEnable() : void{
    parent::onEnable();
    return void || return null;
  }

}
