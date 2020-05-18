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

namespace pocketmine\entity;

use Ahc\Json\Comment as CommentedJsonDecoder;
use function implode;
use function in_array;
use function json_encode;
use function strlen;

class Skin{
	public const ACCEPTED_SKIN_SIZES = [
		64 * 32 * 4,
		64 * 64 * 4,
		128 * 128 * 4
	];

	/** @var string */
	private $skinId;
	/** @var string */
	private $skinData;
	/** @var string */
	private $capeData;
	/** @var string */
	private $geometryName;
	/** @var string */
	private $geometryData;

	public function __construct(string $skinId, string $skinData, string $capeData = "", string $geometryName = "", string $geometryData = ""){
		$this->skinId = $skinId;
		$this->skinData = $skinData;
		$this->capeData = $capeData;
		$this->geometryName = $geometryName;
		$this->geometryData = $geometryData;
	}

	/**
	 * @deprecated
	 */
	public function isValid() : bool{
		try{
			$this->validate();
			return true;
		}catch(\InvalidArgumentException $e){
			return false;
		}
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function validate() : void{
		if($this->skinId === ""){
			throw new \InvalidArgumentException("Skin ID must not be empty");
		}
		$len = strlen($this->skinData);
		if(!in_array($len, self::ACCEPTED_SKIN_SIZES, true)){
			throw new \InvalidArgumentException("Invalid skin data size $len bytes (allowed sizes: " . implode(", ", self::ACCEPTED_SKIN_SIZES) . ")");
		}
		if($this->capeData !== "" and strlen($this->capeData) !== 8192){
			throw new \InvalidArgumentException("Invalid cape data size " . strlen($this->capeData) . " bytes (must be exactly 8192 bytes)");
		}
		//TODO: validate geometry
	}

	public function getSkinId() : string{
		return $this->skinId;
	}

	public function getSkinData() : string{
		return $this->skinData;
	}

	public function getCapeData() : string{
		return $this->capeData;
	}

	public function getGeometryName() : string{
		return $this->geometryName;
	}

	public function getGeometryData() : string{
		return $this->geometryData;
	}

	/**
	 * Hack to cut down on network overhead due to skins, by un-pretty-printing geometry JSON.
	 *
	 * Mojang, some stupid reason, send every single model for every single skin in the selected skin-pack.
	 * Not only that, they are pretty-printed.
	 * TODO: find out what model crap can be safely dropped from the packet (unless it gets fixed first)
	 */
	public function debloatGeometryData() : void{
		if($this->geometryData !== ""){
			$this->geometryData = (string) json_encode((new CommentedJsonDecoder())->decode($this->geometryData));
		}
	}
}
