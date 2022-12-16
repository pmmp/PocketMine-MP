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
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\Location;
use pocketmine\entity\object\FireworkRocket as FireworkEntity;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use function lcg_value;
use function mt_rand;

class FireworkRocket extends Item{

	protected const TAG_FIREWORK_DATA = "Fireworks"; //TAG_Compound
	protected const TAG_FLIGH_DURATION = "Flight"; //TAG_Byte
	protected const TAG_EXPLOSIONS = "Explosions"; //TAG_List

	protected int $flightDuration = 1;

	/** @var FireworkRocketExplosion[] */
	protected array $explosions = [];

	public function getFlightDuration() : int{
		return $this->flightDuration;
	}

	public function setFlightDuration(int $duration) : void{
		if($duration < 1 || $duration > 255){
			throw new \InvalidArgumentException("Flight duration must be in range 1-255");
		}
		$this->flightDuration = $duration;
	}

	/**
	 * @return FireworkRocketExplosion[]
	 */
	public function getExplosions() : array{
		return $this->explosions;
	}

	/**
	 * @param FireworkRocketExplosion[] $explosions
	 */
	public function setExplosions(array $explosions) : void{
		Utils::validateArrayValueType($explosions, function(FireworkRocketExplosion $_) : void{});
		$this->explosions = $explosions;
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
		$correction = 0.15;
		$position = $blockClicked->getPosition()->addVector($clickVector);
		$position = match($face){
			Facing::DOWN => $position->add(0, -$correction, 0),
			Facing::UP => $position->add(0, $correction, 0),
			Facing::NORTH => $position->add(0, 0, -$correction),
			Facing::SOUTH => $position->add(0, 0, $correction),
			Facing::WEST => $position->add(-$correction, 0, 0),
			Facing::EAST => $position->add($correction, 0, 0),
			default => throw new AssumptionFailedError("Invalid facing $face")
		};

		$randomDuration = (($this->flightDuration + 1) * 10) + mt_rand(0, 12);

		$entity = new FireworkEntity(Location::fromObject($position, $player->getWorld(), lcg_value() * 360, 90), $randomDuration, $this->pop());
		$entity->setOwningEntity($player);
		$entity->setMotion(new Vector3(lcg_value() * 0.001, 0.05, lcg_value() * 0.001));
		$entity->spawnToAll();

		return ItemUseResult::SUCCESS();
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);

		$fireworksTag = $tag->getCompoundTag(self::TAG_FIREWORK_DATA);
		if($fireworksTag === null){
			throw new SavedDataLoadingException("Missing firework data");
		}

		$this->setFlightDuration($fireworksTag->getByte(self::TAG_FLIGH_DURATION, 1));

		if(($explosions = $fireworksTag->getListTag(self::TAG_EXPLOSIONS)) instanceof ListTag){
			/** @var CompoundTag $explosion */
			foreach($explosions as $explosion){
				$this->explosions[] = FireworkRocketExplosion::fromCompoundTag($explosion);
			}
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		$fireworksTag = CompoundTag::create();
		$fireworksTag->setByte(self::TAG_FLIGH_DURATION, $this->flightDuration);

		$explosions = new ListTag();
		foreach($this->explosions as $explosion){
			$explosions->push($explosion->toCompoundTag());
		}
		$fireworksTag->setTag(self::TAG_EXPLOSIONS, $explosions);

		$tag->setTag(self::TAG_FIREWORK_DATA, $fireworksTag);
	}
}
