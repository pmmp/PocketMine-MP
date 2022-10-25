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

namespace pocketmine\world\particle;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\ByteMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\IntMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;

class FloatingTextParticle implements Particle{
	//TODO: HACK!

	/** @var string */
	protected $text;
	/** @var string */
	protected $title;
	/** @var int|null */
	protected $entityId = null;
	/** @var bool */
	protected $invisible = false;

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

	public function encode(Vector3 $pos) : array{
		$p = [];

		if($this->entityId === null){
			$this->entityId = Entity::nextRuntimeId();
		}else{
			$p[] = RemoveActorPacket::create($this->entityId);
		}

		if(!$this->invisible){
			$name = $this->title . ($this->text !== "" ? "\n" . $this->text : "");

			$actorFlags = (
				1 << EntityMetadataFlags::IMMOBILE
			);
			$actorMetadata = [
				EntityMetadataProperties::FLAGS => new LongMetadataProperty($actorFlags),
				EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.01), //zero causes problems on debug builds
				EntityMetadataProperties::BOUNDING_BOX_WIDTH => new FloatMetadataProperty(0.0),
				EntityMetadataProperties::BOUNDING_BOX_HEIGHT => new FloatMetadataProperty(0.0),
				EntityMetadataProperties::NAMETAG => new StringMetadataProperty($name),
				EntityMetadataProperties::VARIANT => new IntMetadataProperty(RuntimeBlockMapping::getInstance()->toRuntimeId(VanillaBlocks::AIR()->getFullId())),
				EntityMetadataProperties::ALWAYS_SHOW_NAMETAG => new ByteMetadataProperty(1),
			];
			$p[] = AddActorPacket::create(
				$this->entityId, //TODO: actor unique ID
				$this->entityId,
				EntityIds::FALLING_BLOCK,
				$pos, //TODO: check offset (0.49?)
				null,
				0,
				0,
				0,
				0,
				[],
				$actorMetadata,
				new PropertySyncData([], []),
				[]
			);
		}

		return $p;
	}
}
