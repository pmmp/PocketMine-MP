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

namespace pocketmine\level\particle;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Skin;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\utils\UUID;
use function str_repeat;

class FloatingTextParticle implements Particle{
	//TODO: HACK!

	protected $text;
	protected $title;
	protected $entityId;
	protected $invisible = false;

	/**
	 * @param string $text
	 * @param string $title
	 */
	public function __construct(string $text, string $title = ""){
		$this->text = $text;
		$this->title = $title;
	}

	public function getText() : string{
		return $this->text;
	}

	public function setText(string $text) : void{
		$this->text = $text;
	}

	public function getTitle() : string{
		return $this->title;
	}

	public function setTitle(string $title) : void{
		$this->title = $title;
	}

	public function isInvisible() : bool{
		return $this->invisible;
	}

	public function setInvisible(bool $value = true) : void{
		$this->invisible = $value;
	}

	public function encode(Vector3 $pos){
		$p = [];

		if($this->entityId === null){
			$this->entityId = EntityFactory::nextRuntimeId();
		}else{
			$pk0 = new RemoveEntityPacket();
			$pk0->entityUniqueId = $this->entityId;

			$p[] = $pk0;
		}

		if(!$this->invisible){
			$uuid = UUID::fromRandom();
			$name = $this->title . ($this->text !== "" ? "\n" . $this->text : "");

			$add = new PlayerListPacket();
			$add->type = PlayerListPacket::TYPE_ADD;
			$add->entries = [PlayerListEntry::createAdditionEntry($uuid, $this->entityId, $name, new Skin("Standard_Custom", str_repeat("\x00", 8192)))];
			$p[] = $add;

			$pk = new AddPlayerPacket();
			$pk->uuid = $uuid;
			$pk->username = $name;
			$pk->entityRuntimeId = $this->entityId;
			$pk->position = $pos; //TODO: check offset
			$pk->item = ItemFactory::air();

			$flags = (
				1 << Entity::DATA_FLAG_IMMOBILE
			);
			$pk->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
				Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.01] //zero causes problems on debug builds
			];

			$p[] = $pk;

			$remove = new PlayerListPacket();
			$remove->type = PlayerListPacket::TYPE_REMOVE;
			$remove->entries = [PlayerListEntry::createRemovalEntry($uuid)];
			$p[] = $remove;
		}

		return $p;
	}
}
