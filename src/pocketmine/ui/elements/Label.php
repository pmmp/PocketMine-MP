<?php

namespace pocketmine\ui\elements;

use pocketmine\Player;

class Label extends UIElement{

	/**
	 * @param string $text
	 */
	public function __construct($text){
		$this->text = $text;
	}

	/**
	 *
	 * @return array
	 */
	final public function jsonSerialize(){
		return [
			"type" => "label",
			"text" => $this->text
		];
	}

	/**
	 * Returns the labels text, labels always send null
	 *
	 * @param null $value
	 * @param Player $player
	 * @return mixed
	 */
	final public function handle($value, Player $player){
		return $this->text;
	}

}
