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

namespace pocketmine\network\mcpe\protocol\types;

class SkinData{

	/** @var string */
	private $skinId;
	/** @var string */
	private $resourcePatch;
	/** @var SkinImage */
	private $skinImage;
	/** @var SkinAnimation[] */
	private $animations;
	/** @var SkinImage */
	private $capeImage;
	/** @var string */
	private $geometryData;
	/** @var string */
	private $animationData;
	/** @var bool */
	private $persona;
	/** @var bool */
	private $premium;
	/** @var bool */
	private $personaCapeOnClassic;
	/** @var string */
	private $capeId;

	/**
	 * @param string          $skinId
	 * @param string          $resourcePatch
	 * @param SkinImage       $skinImage
	 * @param SkinAnimation[] $animations
	 * @param SkinImage|null  $capeImage
	 * @param string          $geometryData
	 * @param string          $animationData
	 * @param bool            $premium
	 * @param bool            $persona
	 * @param bool            $personaCapeOnClassic
	 * @param string          $capeId
	 */
	public function __construct(string $skinId, string $resourcePatch, SkinImage $skinImage, array $animations = [], SkinImage $capeImage = null, string $geometryData = "", string $animationData = "", bool $premium = false, bool $persona = false, bool $personaCapeOnClassic = false, string $capeId = ""){
		$this->skinId = $skinId;
		$this->resourcePatch = $resourcePatch;
		$this->skinImage = $skinImage;
		$this->animations = $animations;
		$this->capeImage = $capeImage;
		$this->geometryData = $geometryData;
		$this->animationData = $animationData;
		$this->premium = $premium;
		$this->persona = $persona;
		$this->personaCapeOnClassic = $personaCapeOnClassic;
		$this->capeId = $capeId;
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
	public function getResourcePatch() : string{
		return $this->resourcePatch;
	}

	/**
	 * @return SkinImage
	 */
	public function getSkinImage() : SkinImage{
		return $this->skinImage;
	}

	/**
	 * @return SkinAnimation[]
	 */
	public function getAnimations() : array{
		return $this->animations;
	}

	/**
	 * @return SkinImage
	 */
	public function getCapeImage() : SkinImage{
		return $this->capeImage;
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
	public function getAnimationData() : string{
		return $this->animationData;
	}

	/**
	 * @return bool
	 */
	public function isPersona() : bool{
		return $this->persona;
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
	public function isPersonaCapeOnClassic() : bool{
		return $this->personaCapeOnClassic;
	}

	/**
	 * @return string
	 */
	public function getCapeId() : string{
		return $this->capeId;
	}

}
