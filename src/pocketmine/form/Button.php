<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\form;

class Button implements \JsonSerializable{

	const IMAGE_TYPE_PATH = "path";
	const IMAGE_TYPE_URL = "url";

	/**
	 * @var string
	 */
	private $text;
	/**
	 * @var string|null
	 */
	private $imageType;
	/**
	 * @var string|null
	 */
	private $imagePath;

	public function __construct(string $text, ?string $imageType = null, ?string $imagePath = null){
		$this->text = $text;
		$this->imageType = $imageType;
		$this->imagePath = $imagePath;
	}

	public function getText() : string{
		return $this->text;
	}

	public function hasImage() : bool{
		return $this->imageType !== null and $this->imagePath !== null;
	}

	public function getImageType() : ?string{
		return $this->imageType;
	}

	public function getImagePath() : ?string{
		return $this->imagePath;
	}

	public function jsonSerialize(){
		$json = [
			"text" => $this->text
		];

		if($this->hasImage()){
			$json["image"] = [
				"type" => $this->getImageType(),
				"data" => $this->getImagePath()
			];
		}

		return $json;
	}

}