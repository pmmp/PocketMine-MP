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
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use function array_map;
use function mt_rand;

class FireworkRocket extends Item{

	public const TAG_FIREWORK_DATA = "Fireworks"; //TAG_Compound
	protected const TAG_FLIGHT_TIME_MULTIPLIER = "Flight"; //TAG_Byte
	public const TAG_EXPLOSIONS = "Explosions"; //TAG_List

	protected int $flightTimeMultiplier = 1;

	/** @var FireworkRocketExplosion[] */
	protected array $explosions = [];

	/**
	 * Returns the value that will be used to calculate a randomized flight duration
	 * for the firework (equals the amount of gunpowder used in crafting the rocket).
	 *
	 * The higher this value, the longer the flight duration.
	 */
	public function getFlightTimeMultiplier() : int{
		return $this->flightTimeMultiplier;
	}

	/**
	 * Sets the value that will be used to calculate a randomized flight duration
	 * for the firework.
	 *
	 * The higher this value, the longer the flight duration.
	 *
	 * @return $this
	 */
	public function setFlightTimeMultiplier(int $multiplier) : self{
		if($multiplier < 1 || $multiplier > 127){
			throw new \InvalidArgumentException("Flight time multiplier must be in range 1-127");
		}
		$this->flightTimeMultiplier = $multiplier;

		return $this;
	}

	/**
	 * @return FireworkRocketExplosion[]
	 */
	public function getExplosions() : array{
		return $this->explosions;
	}

	/**
	 * @param FireworkRocketExplosion[] $explosions
	 *
	 * @return $this
	 */
	public function setExplosions(array $explosions) : self{
		Utils::validateArrayValueType($explosions, function(FireworkRocketExplosion $_) : void{});
		$this->explosions = $explosions;

		return $this;
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
		//TODO: this would be nicer if Vector3::getSide() accepted floats for distance
		$position = $blockClicked->getPosition()->addVector($clickVector)->addVector(Vector3::zero()->getSide($face)->multiply(0.15));

		$randomDuration = (($this->flightTimeMultiplier + 1) * 10) + mt_rand(0, 12);

		$entity = new FireworkEntity(Location::fromObject($position, $player->getWorld(), Utils::getRandomFloat() * 360, 90), $randomDuration, $this->explosions);
		$entity->setOwningEntity($player);
		$entity->setMotion(new Vector3(
			(Utils::getRandomFloat() - Utils::getRandomFloat()) * 0.0023,
			0.05,
			(Utils::getRandomFloat() - Utils::getRandomFloat()) * 0.0023
		));
		$entity->spawnToAll();

		$this->pop();

		return ItemUseResult::SUCCESS;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);

		$fireworkData = $tag->getCompoundTag(self::TAG_FIREWORK_DATA);
		if($fireworkData === null){
			throw new SavedDataLoadingException("Missing firework data");
		}

		$this->setFlightTimeMultiplier($fireworkData->getByte(self::TAG_FLIGHT_TIME_MULTIPLIER, 1));

		if(($explosions = $fireworkData->getListTag(self::TAG_EXPLOSIONS, CompoundTag::class)) !== null){
			foreach($explosions as $explosion){
				$this->explosions[] = FireworkRocketExplosion::fromCompoundTag($explosion);
			}
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		$fireworkData = CompoundTag::create();
		$fireworkData->setByte(self::TAG_FLIGHT_TIME_MULTIPLIER, $this->flightTimeMultiplier);
		$fireworkData->setTag(self::TAG_EXPLOSIONS, new ListTag(array_map(fn(FireworkRocketExplosion $e) => $e->toCompoundTag(), $this->explosions)));

		$tag->setTag(self::TAG_FIREWORK_DATA, $fireworkData);
	}
}
