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
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\utils\UUID;

class FloatingTextParticle extends Particle{
	//TODO: HACK!

	protected $text;
	protected $title;
	protected $entityId;
	protected $invisible = false;

	/**
	 * @param Vector3 $pos
	 * @param string  $text
	 * @param string  $title
	 */
	public function __construct(Vector3 $pos, string $text, string $title = ""){
		parent::__construct($pos->x, $pos->y, $pos->z);
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

	public function setInvisible(bool $value = true){
		$this->invisible = $value;
	}

	public function encode(){
		$p = [];

		if($this->entityId === null){
			$this->entityId = Entity::$entityCount++;
		}else{
			$pk0 = new RemoveEntityPacket();
			$pk0->entityUniqueId = $this->entityId;

			$p[] = $pk0;
		}

		if(!$this->invisible){
			$pk = new AddPlayerPacket();
			$pk->uuid = $uuid = UUID::fromRandom();
			$pk->username = "";
			$pk->entityRuntimeId = $this->entityId;
			$pk->position = $this->asVector3(); //TODO: check offset
			$pk->item = ItemFactory::get(Item::AIR, 0, 0);

			$flags = (
				(1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_IMMOBILE)
			);
			$pk->metadata = [
				Entity::DATA_FLAGS =>   [Entity::DATA_TYPE_LONG,   $flags],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")],
				Entity::DATA_SCALE =>   [Entity::DATA_TYPE_FLOAT,  0.01] //zero causes problems on debug builds
			];

			$p[] = $pk;

			$skinPk = new PlayerSkinPacket();
			$skinPk->uuid = $uuid;
			$skinPk->skin = new Skin("Standard_Custom", str_repeat("\x00", 8192));
			$p[] = $skinPk;
		}

		return $p;
	}
}
