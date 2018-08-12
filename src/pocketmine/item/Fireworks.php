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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\FireworksRocket;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\utils\Random;

class Fireworks extends Item{
	public const COLOR_BLACK = 0;
	public const COLOR_RED = 1;
	public const COLOR_GREEN = 2;
	public const COLOR_BROWN = 3;
	public const COLOR_BLUE = 4;
	public const COLOR_PURPLE = 5;
	public const COLOR_CYAN = 6;
	public const COLOR_LIGHT_GRAY = 7;
	public const COLOR_GRAY = 8;
	public const COLOR_PINK = 9;
	public const COLOR_LIME = 10;
	public const COLOR_YELLOW = 11;
	public const COLOR_LIGHT_BLUE = 12;
	public const COLOR_MAGENTA = 13;
	public const COLOR_ORANGE = 14;
	public const COLOR_WHITE = 15;

	public const TYPE_SMALL_BALL = 0;
	public const TYPE_LARGE_BALL = 1;
	public const TYPE_STAR_SHAPED = 2;
	public const TYPE_CREEPER_SHAPED = 3;
	public const TYPE_BURST = 4;

	/** @var float */
	public $spread = 5.0;

	public function __construct($meta = 0){
		parent::__construct(self::FIREWORKS, $meta, "Fireworks");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		$random = new Random;
		$yaw = $random->nextBoundedInt(360);
		$pitch = -1 * (float) (90 + ($random->nextFloat() * $this->spread - $this->spread / 2));
		$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5), null, $yaw, $pitch);
		if(($tags = $this->getNamedTagEntry("Fireworks")) !== null){
			$nbt->setTag($tags);
		}

		$rocket = new FireworksRocket($player->level, $nbt, $player, $this, $random);
		$player->level->addEntity($rocket);

		if($rocket instanceof Entity){
			if($player->isSurvival()){
				--$this->count;
			}
			$rocket->spawnToAll();
			return true;
		}
		return false;
	}

	public static function createNBT(FireworksData $fireworksData) : CompoundTag{
		$list = [];
		$compound = new CompoundTag;
		foreach($fireworksData->getExplosions() as $explosion){
			$tag = new CompoundTag;
			$tag->setByteArray("FireworkColor", $explosion->getColor());
			$tag->setByteArray("FireworkFade", $explosion->getFade());
			$tag->setByte("FireworkFlicker", $explosion->isFlickering());
			$tag->setByte("FireworkTrail", $explosion->hasTrail());
			$tag->setByte("FireworkType", $explosion->getType());
			$list[] = $tag;
		}
		$compound->setTag(new CompoundTag("Fireworks", [
				new ListTag("Explosions", $list, NBT::TAG_Compound),
				new ByteTag("Flight", $fireworksData->getFlight())
			])
		);
		return $compound;
	}
}
