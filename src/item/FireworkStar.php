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

namespace pocketmine\item;

use pocketmine\block\utils\DyeColor;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Binary;

class FireworkStar extends Item{

	protected const TAG_EXPLOSION = "FireworksItem"; //TAG_Compound
	protected const TAG_COLOR = "customColor"; //TAG_Int

	protected FireworkRocketExplosion $explosion;

	public function __construct(ItemIdentifier $identifier, string $name){
		parent::__construct($identifier, $name);

		$this->explosion = new FireworkRocketExplosion(FireworkRocketType::SMALL_SPHERE(), DyeColor::BLACK(), null, false, false);
	}

	public function getExplosion() : FireworkRocketExplosion{
		return $this->explosion;
	}

	public function setExplosion(FireworkRocketExplosion $explosion) : void{
		$this->explosion = $explosion;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);

		$explosionTag = $tag->getTag(self::TAG_EXPLOSION);
		if (!$explosionTag instanceof CompoundTag) {
			throw new SavedDataLoadingException("Missing explosion data");
		}
		$this->explosion = FireworkRocketExplosion::fromCompoundTag($explosionTag);
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		$tag->setTag(self::TAG_EXPLOSION, $this->explosion->toCompoundTag());
		$tag->setInt(self::TAG_COLOR, Binary::signInt($this->explosion->getColor()->getRgbValue()->toARGB()));
	}
}
