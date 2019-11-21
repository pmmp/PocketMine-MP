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

use pocketmine\entity\Skin;

class LegacySkinAdapter implements SkinAdapter{

	public function toSkinData(Skin $skin) : SkinData{
		return new SkinData(
			$skin->getSkinId(),
			json_encode(["geometry" => ["default" => $skin->getGeometryName()]]),
			SkinImage::fromLegacy($skin->getSkinData()), [],
			new SkinImage(32, 64, $skin->getCapeData()),
			$skin->getGeometryData());
	}

	public function fromSkinData(SkinData $data) : Skin{
		$skinData = $data->getSkinImage()->getData();
		if($data->isPersona()){
			return new Skin("Standard_Custom", str_repeat(random_bytes(3) . "\xff", 2048), "", "geometry.humanoid.custom");
		}
		return new Skin($data->getSkinId(), $skinData, $data->getCapeImage()->getData(), json_decode($data->getResourcePatch(), true)["geometry"]["default"], $data->getGeometryData());
	}
}