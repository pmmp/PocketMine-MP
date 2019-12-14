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
use pocketmine\network\mcpe\protocol\types\SkinAnimation;
use pocketmine\network\mcpe\protocol\types\SkinCape;
use pocketmine\network\mcpe\protocol\types\SkinImage;
use pocketmine\utils\UUID;
use function implode;
use function in_array;
use function json_encode;
use function strlen;

class Skin{
	public const ACCEPTED_SKIN_SIZES = [
		64 * 32 * 4,
		64 * 64 * 4,
		128 * 64 * 4,
		128 * 128 * 4,
		256 * 128 * 4,
		256 * 256 * 4
	];

	/** @var string */
	private $skinId;
	/** @var string */
	private $resourcePatch;
	/** @var SkinImage */
	private $skinImage;
	/** @var SkinAnimation[] */
	private $animations = [];
	/** @var string */
	private $geometryData;
	/** @var string */
	private $animationData = "";
	/** @var bool */
	private $persona = false;
	/** @var bool */
	private $premium = false;
	/** @var SkinCape */
	private $cape;

	/** @var string */
	private $geometryName = "";

	public function __construct(string $skinId, string $skinData, string $capeData = "", string $resourcePatch = "", string $geometryData = ""){
		$this->skinId = $skinId;
		$this->skinImage = SkinImage::fromLegacy($skinData);
		$this->resourcePatch = self::generateResourcePatch($resourcePatch, $this->geometryName);
		$noCape = $capeData === "";
		$this->cape = new SkinCape(UUID::fromRandom()->toString(), new SkinImage($noCape ? 0 : 32, $noCape ? 0 : 64, $capeData));
		$this->geometryData = $geometryData;
	}

	private function generateResourcePatch(string $input, string &$geometryName) : string{
		$json = @json_decode($input, true) ?? [];

		if(isset($json["minecraft:geomerty"]["description"]["identifier"]) or isset($json["geometry"]["default"])){
			$geometryName = $json["minecraft:geomerty"]["description"]["identifier"] ?? $json["geometry"]["default"];

			return $input;
		}

		$geometryName = $input;

		return json_encode([
			"minecraft:geometry" => [
				"description" => [
					"identifier" => $input
				]
			]
		]);
	}

	/**
	 * @deprecated
	 * @return bool
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
		$len = strlen($this->skinImage->getData());
		if(!in_array($len, self::ACCEPTED_SKIN_SIZES, true)){
			throw new \InvalidArgumentException("Invalid skin data size $len bytes (allowed sizes: " . implode(", ", self::ACCEPTED_SKIN_SIZES) . ")");
		}
		$capeData = $this->cape->getImage()->getData();
		if($capeData !== "" and strlen($capeData) !== 8192){
			throw new \InvalidArgumentException("Invalid cape data size " . strlen($capeData) . " bytes (must be exactly 8192 bytes)");
		}
		//TODO: validate geometry
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

		if($this->resourcePatch !== ""){
			$this->resourcePatch = (string) json_encode((new CommentedJsonDecoder())->decode($this->resourcePatch));
		}
	}

	/**
	 * @return bool
	 */
	public function isPremium() : bool{
		return $this->premium;
	}

	/**
	 * @return bool
	 */
	public function isPersona() : bool{
		return $this->persona;
	}

	/**
	 * @return SkinAnimation[]
	 */
	public function getAnimations() : array{
		return $this->animations;
	}

	/**
	 * @return SkinCape
	 */
	public function getCape() : SkinCape{
		return $this->cape;
	}

	/**
	 * @return string
	 */
	public function getAnimationData() : string{
		return $this->animationData;
	}

	/**
	 * @return SkinImage
	 */
	public function getSkinImage() : SkinImage{
		return $this->skinImage;
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
	public function getGeometryData() : string{
		return $this->geometryData;
	}

	/**
	 * @return string
	 */
	public function getResourcePatch() : string{
		return $this->resourcePatch;
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function getSkinData() : string{
		return $this->getSkinImage()->getData();
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function getCapeData() : string{
		return $this->getCape()->getImage()->getData();
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function getGeometryName() : string{
		return $this->geometryName;
	}

	/**
	 * @param SkinImage $skinImage
	 * @return Skin
	 */
	public function setSkinImage(SkinImage $skinImage) : Skin{
		$this->skinImage = $skinImage;
		return $this;
	}

	/**
	 * @param SkinAnimation[] $animations
	 * @return Skin
	 */
	public function setAnimations(array $animations) : Skin{
		$this->animations = $animations;
		return $this;
	}

	/**
	 * @param string $animationData
	 * @return Skin
	 */
	public function setAnimationData(string $animationData) : Skin{
		$this->animationData = $animationData;
		return $this;
	}

	/**
	 * @param bool $persona
	 * @return Skin
	 */
	public function setPersona(bool $persona) : Skin{
		$this->persona = $persona;
		return $this;
	}

	/**
	 * @param bool $premium
	 * @return Skin
	 */
	public function setPremium(bool $premium) : Skin{
		$this->premium = $premium;
		return $this;
	}

	/**
	 * @param SkinCape $cape
	 * @return Skin
	 */
	public function setCape(SkinCape $cape) : Skin{
		$this->cape = $cape;
		return $this;
	}
}
