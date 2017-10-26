<?php

namespace pocketmine\ui\elements;

use pocketmine\Player;

class Toggle extends UIElement{

	/** @var boolean */
	protected $defaultValue = false;

	/**
	 *
	 * @param string $text
	 * @param bool $value
	 */
	public function __construct($text, bool $value = false){
		$this->text = $text;
		$this->defaultValue = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setDefaultValue(bool $value){
		$this->defaultValue = $value;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(){
		return [
			"type" => "toggle",
			"text" => $this->text,
			"default" => $this->defaultValue
		];
	}

	/**
	 * @param null $value
	 * @param Player $player
	 * @return mixed
	 */
	public function handle($value, Player $player){
		return $value;
	}

}
