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

namespace pocketmine\network\mcpe\convert;

use InvalidArgumentException;
use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use pocketmine\Server;
use Symfony\Component\Filesystem\Path;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function random_bytes;
use function str_repeat;
use const JSON_THROW_ON_ERROR;

class VanillaSkinAdapter implements SkinAdapter{
	private array $bounds = [];
	private Skin $default;

	public function __construct(){
		$cubes = $this->getCubes(json_decode(file_get_contents(Path::join(\pocketmine\RESOURCE_PATH, "humanoid.json")), true)['geometry.humanoid']);
		$this->bounds[0] = $this->getBounds($cubes);
		$this->bounds[1] = $this->getBounds($cubes, 2.0);

		$this->default = new Skin("Steve", file_get_contents(Path::join(\pocketmine\RESOURCE_PATH, "steve.bin")));
	}

	public function toSkinData(Skin $skin) : SkinData{
		$capeData = $skin->getCapeData();
		$capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
		$geometryName = $skin->getGeometryName();
		if($geometryName === ""){
			$geometryName = "geometry.humanoid.custom";
		}
		return new SkinData(
			$skin->getSkinId(),
			"", //TODO: playfab ID
			json_encode(["geometry" => ["default" => $geometryName]], JSON_THROW_ON_ERROR),
			SkinImage::fromLegacy($skin->getSkinData()), [],
			$capeImage,
			$skin->getGeometryData()
		);
	}

	public function fromSkinData(SkinData $data) : Skin{
		if($data->isPersona()) {
			return $this->default;
		}

		try {
			[$geometryName, $geometry] = $this->parseGeometry($data->getResourcePatch(), $data->getGeometryData());
		} catch(InvalidSkinException $e) {
			var_dump($e->getMessage());
			return $this->default;
		}

		if($this->getSkinTransparencyPercentage($data->getSkinImage()->getData()) > 4) {
			return $this->default;
		}

		return new Skin($data->getSkinId(), $data->getSkinImage()->getData(), $data->getCapeImage()->getData(), $geometryName, $geometry);
	}

	/**
	 * @param string $skinData
	 *
	 * @return int
	 */
	private function getSkinTransparencyPercentage(string $skinData): int
	{
		switch (strlen($skinData)) {
			case 8192:
				$maxX = 64;
				$maxY = 32;
				$bounds = $this->bounds[0];
				break;
			case 16384:
				$maxX = 64;
				$maxY = 64;
				$bounds = $this->bounds[0];
				break;
			case 65536:
				$maxX = 128;
				$maxY = 128;
				$bounds = $this->bounds[1];
				break;
			default:
				throw new InvalidArgumentException('Inappropriate skin data length: ' . strlen($skinData));
		}
		$transparentPixels = $pixels = 0;
		foreach ($bounds as $bound) {
			if ($bound['max']['x'] > $maxX || $bound['max']['y'] > $maxY) {
				continue;
			}
			for ($y = $bound['min']['y']; $y <= $bound['max']['y']; $y++) {
				for ($x = $bound['min']['x']; $x <= $bound['max']['x']; $x++) {
					$key = (($maxX * $y) + $x) * 4;
					$a = ord($skinData[$key + 3]);
					if ($a < 127) {
						++$transparentPixels;
					}
					++$pixels;
				}
			}
		}
		return (int)round($transparentPixels * 100 / max(1, $pixels));
	}

	/**
	 * @param array $geometryData
	 *
	 * @return array
	 */
	private function getCubes(array $geometryData): array
	{
		$cubes = [];
		foreach ($geometryData['bones'] as $bone) {
			if (!isset($bone['cubes'])) {
				continue;
			}
			if ($bone['mirror'] ?? false) {
				throw new InvalidArgumentException('Unsupported geometry data');
			}
			foreach ($bone['cubes'] as $cubeData) {
				$cube = [];
				$cube['x'] = $cubeData['size'][0];
				$cube['y'] = $cubeData['size'][1];
				$cube['z'] = $cubeData['size'][2];
				$cube['uvX'] = $cubeData['uv'][0];
				$cube['uvY'] = $cubeData['uv'][1];
				$cubes[] = $cube;
			}
		}
		return $cubes;
	}

	/**
	 * @param array $cubes
	 * @param float $scale
	 *
	 * @return array
	 */
	private function getBounds(array $cubes, float $scale = 1.0): array
	{
		$bounds = [];
		foreach ($cubes as $cube) {
			$x = (int)($scale * $cube['x']);
			$y = (int)($scale * $cube['y']);
			$z = (int)($scale * $cube['z']);
			$uvX = (int)($scale * $cube['uvX']);
			$uvY = (int)($scale * $cube['uvY']);
			$bounds[] = ['min' => ['x' => $uvX + $z, 'y' => $uvY], 'max' => ['x' => $uvX + $z + (2 * $x) - 1, 'y' => $uvY + $z - 1]];
			$bounds[] = ['min' => ['x' => $uvX, 'y' => $uvY + $z], 'max' => ['x' => $uvX + (2 * ($z + $x)) - 1, 'y' => $uvY + $z + $y - 1]];
		}
		return $bounds;
	}

	private function parseGeometry(string $resourcePatch, string $geometryData): array {
		$resourcePatch = json_decode($resourcePatch, true);
		if (!is_array($resourcePatch)) {
			throw new InvalidSkinException("Invalid resourcePatch: not a valid JSON object.");
		}

		if (!isset($resourcePatch["geometry"])) {
			throw new InvalidSkinException("Invalid resourcePatch: missing 'geometry' key.");
		}

		if (isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])) {
			$geometryName = $resourcePatch["geometry"]["default"];
		} else {
			throw new InvalidSkinException("Invalid resourcePatch: missing or non-string 'geometry.default'.");
		}

		if (isset($resourcePatch["geometry"]["animated_face"]) && is_string($resourcePatch["geometry"]["animated_face"])) {
			$geometryName = $resourcePatch["geometry"]["animated_face"];
		}

		$geometry = json_decode($geometryData, true);
		if (!isset($geometry["minecraft:geometry"])) {
			throw new InvalidSkinException("Invalid geometry data: missing 'minecraft:geometry' array.");
		}

		$minecraftGeometry = $geometry["minecraft:geometry"];
		if (count($minecraftGeometry) > 15) {
			throw new InvalidSkinException("Invalid geometry data: more than 15 geometry definitions found (max allowed is 15).");
		}

		$matchedGeometry = null;
		$bones = [];

		foreach ($minecraftGeometry as $k => $data) {
			if (!isset($data["description"]) || !is_array($data["description"])) {
				throw new InvalidSkinException("Invalid geometry at index $k: missing or malformed 'description' field.");
			}

			if (!isset($data["description"]["identifier"]) || !is_string($data["description"]["identifier"])) {
				throw new InvalidSkinException("Invalid geometry at index $k: missing or non-string 'description.identifier'.");
			}

			$identifier = $data["description"]["identifier"];
			if ($identifier === $geometryName) {
				if (!isset($data["bones"]) || !is_array($data["bones"])) {
					throw new InvalidSkinException("Geometry '$identifier' is invalid: missing or malformed 'bones' array.");
				}
				$bones = $data["bones"];
				$matchedGeometry = $data;
				break;
			}
		}

		if (empty($bones)) {
			throw new InvalidSkinException("Invalid geometry: could not find bones for geometry identifier '$geometryName'.");
		}

		$body = null;
		$head = null;
		$rightArm = null;
		$leftArm = null;
		$rightLeg = null;
		$leftLeg = null;

		foreach ($bones as $k => $bone) {
			if (!isset($bone["name"]) || !is_string($bone["name"])) {
				throw new InvalidSkinException("Invalid bone at index $k: missing or non-string 'name' field.");
			}

			$name = strtolower($bone["name"]);
			$parent = isset($bone["parent"]) && is_string($bone["parent"]) ? strtolower($bone["parent"]) : null;

			foreach ([$name, $parent] as $key) {
				if ($key === null) continue;

				switch ($key) {
					case "body":
						$body = $bone;
						break;
					case "head":
						$head = $bone;
						break;
					case "rightarm":
						$rightArm = $bone;
						break;
					case "leftarm":
						$leftArm = $bone;
						break;
					case "rightleg":
						$rightLeg = $bone;
						break;
					case "leftleg":
						$leftLeg = $bone;
						break;
				}
			}

			if (isset($bone["cubes"])) {
				$this->validCubes($bone);
			}

			if (isset($bone["positions"])) {
				throw new InvalidSkinException("Invalid bone '{$bone["name"]}': 'positions' field is not supported.");
			}
		}

		if (!$head || !$body || !$leftArm || !$rightArm || !$leftLeg || !$rightLeg) {
			throw new InvalidSkinException("Invalid geometry: one or more required bones are missing (head, body, leftArm, rightArm, leftLeg, rightLeg).");
		}

		$geometry["minecraft:geometry"] = [$matchedGeometry];
		return [$geometryName, json_encode($geometry)];
	}


	private function validCubes(array $bone) : void {
		$name = strtolower($bone["name"]);
		$parent = isset($bone["parent"]) && is_string($bone["parent"]) ? strtolower($bone["parent"]) : null;

		$cubes = $bone["cubes"];

		if (count($cubes) > 1) {
			throw new InvalidSkinException("Bone '$name' must have only one cube defined.");
		}

		$cube = array_shift($cubes);
		if (!isset($cube["size"])) {
			throw new InvalidSkinException("Cube of bone '$name' is missing 'size' property.");
		}

		if (!isset($cube["uv"])) {
			throw new InvalidSkinException("Cube of bone '$name' must have a string 'uv' property.");
		}

		$size = new Vector3($cube["size"][0], $cube["size"][1], $cube["size"][2]);
		$uv = new Vector2($cube["uv"][0], $cube["uv"][1]);

		$valid = false;
		foreach ([$name, $parent] as $key) {
			if ($key === null) continue;
			$key = strtolower($key);

			$expectedSize = match ($key) {
				"body" => new Vector3(9 , 12, 4),
				"head" => new Vector3(8, 8, 8),
				"rightarm", "leftarm", "rightleg", "leftleg" => new Vector3(4, 12, 4),
				default => null
			};

			$expectedUV = match ($key) {
				"body" => new Vector2(16, 16),
				"head" => new Vector2(0, 0),
				"rightarm" => new Vector2(40, 16),
				"leftarm" => new Vector2(32, 48),
				"rightleg" => new Vector2(0, 16),
				"leftleg" => new Vector2(16, 48),
				default => null
			};

			if($expectedSize == null){
				$valid = true;
				continue;
			}
			if($expectedUV == null) {
				$valid = true;
				continue;
			}

			if (
				($expectedSize->distance($size) <= 1) &&
				($expectedUV->distance($uv) <= 1)
			) {
				$valid = true;
			}
		}

		if(!$valid) {
			throw new InvalidSkinException("Bone '$name' has invalid cube dimensions or UV mapping. Expected size and UV must match standard body parts within a tolerance of 1 unit.");
		}
	}
}
