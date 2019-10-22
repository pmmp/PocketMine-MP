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
use function json_last_error_msg;
use function strlen;

final class Skin{
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
		if($skinId === ""){
			throw new \InvalidArgumentException("Skin ID must not be empty");
		}
		$len = strlen($skinData);
		if(!in_array($len, self::ACCEPTED_SKIN_SIZES, true)){
			throw new \InvalidArgumentException("Invalid skin data size $len bytes (allowed sizes: " . implode(", ", self::ACCEPTED_SKIN_SIZES) . ")");
		}
		if($capeData !== "" and strlen($capeData) !== 8192){
			throw new \InvalidArgumentException("Invalid cape data size " . strlen($capeData) . " bytes (must be exactly 8192 bytes)");
		}

		if($geometryData !== ""){
			$decodedGeometry = (new CommentedJsonDecoder())->decode($geometryData);
			if($decodedGeometry === false){
				throw new \InvalidArgumentException("Invalid geometry data (" . json_last_error_msg() . ")");
			}

			/*
			 * Hack to cut down on network overhead due to skins, by un-pretty-printing geometry JSON.
			 *
			 * Mojang, some stupid reason, send every single model for every single skin in the selected skin-pack.
			 * Not only that, they are pretty-printed.
			 * TODO: find out what model crap can be safely dropped from the packet (unless it gets fixed first)
			 */
			$geometryData = json_encode($decodedGeometry);
		}

		$this->skinId = $skinId;
		$this->skinData = $skinData;
		$this->capeData = $capeData;
		$this->geometryName = $geometryName;
		$this->geometryData = $geometryData;
	}

	/**
	 * @return string
	 */
	public function getSkinId() : string{
		return $this->skinId;
	}

	/**
	 * @return string
	 */
	public function getSkinData() : string{
		return $this->skinData;
	}

	/**
	 * @return string
	 */
	public function getCapeData() : string{
		return $this->capeData;
	}

	/**
	 * @return string
	 */
	public function getGeometryName() : string{
		return $this->geometryName;
	}

	/**
	 * @return string
	 */
	public function getGeometryData() : string{
		return $this->geometryData;
	}
}
