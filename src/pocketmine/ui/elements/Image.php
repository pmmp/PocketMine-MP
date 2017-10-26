<?php

namespace pocketmine\ui\elements;

use pocketmine\Player;

class Image extends UIElement{
//TODO! Blame mojang, doesn't work yet
	public $texture;
	public $width;
	public $height;

	public function __construct($texture, $width = 0, $height = 0){
		$this->texture = $texture;
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 *
	 * @return array
	 */
	final public function jsonSerialize(){
		return [
			"text" => "sign",
			"type" => "image",
			"texture" => $this->texture,
			"size" => [$this->width, $this->height]
		];
	}

	/**
	 * TODO
	 *
	 * @param null $value
	 * @param Player $player
	 * @return mixed
	 */
	public function handle($value, Player $player){
		return null;
	}

}
