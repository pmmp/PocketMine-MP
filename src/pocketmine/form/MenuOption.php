<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\form;

/**
 * Represents an option on a MenuForm. The option is shown as a button and may optionally have an image next to it.
 */
class MenuOption implements \JsonSerializable{

	/**
	 * @var string
	 */
	private $text;
	/**
	 * @var FormIcon|null
	 */
	private $image;

	public function __construct(string $text, ?FormIcon $image = null){
		$this->text = $text;
		$this->image = $image;
	}

	public function getText() : string{
		return $this->text;
	}

	public function hasImage() : bool{
		return $this->image !== null;
	}

	public function getImage() : ?FormIcon{
		return $this->image;
	}

	public function jsonSerialize(){
		$json = [
			"text" => $this->text
		];

		if($this->hasImage()){
			$json["image"] = $this->image;
		}

		return $json;
	}

}