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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\types\RuntimeBlockMapping;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;
use pocketmine\network\mcpe\serializer\NetworkNbtSerializer;
use function count;
use function file_get_contents;
use function json_decode;
use const pocketmine\RESOURCE_PATH;

class StartGamePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::START_GAME_PACKET;

	/** @var string|null */
	private static $blockTableCache = null;
	/** @var string|null */
	private static $itemTableCache = null;

	/** @var int */
	public $entityUniqueId;
	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $playerGamemode;

	/** @var Vector3 */
	public $playerPosition;

	/** @var float */
	public $pitch;
	/** @var float */
	public $yaw;

	/** @var int */
	public $seed;
	/** @var int */
	public $dimension;
	/** @var int */
	public $generator = 1; //default infinite - 0 old, 1 infinite, 2 flat
	/** @var int */
	public $worldGamemode;
	/** @var int */
	public $difficulty;
	/** @var int */
	public $spawnX;
	/** @var int */
	public $spawnY;
	/** @var int */
	public $spawnZ;
	/** @var bool */
	public $hasAchievementsDisabled = true;
	/** @var int */
	public $time = -1;
	/** @var int */
	public $eduEditionOffer = 0;
	/** @var bool */
	public $hasEduFeaturesEnabled = false;
	/** @var float */
	public $rainLevel;
	/** @var float */
	public $lightningLevel;
	/** @var bool */
	public $hasConfirmedPlatformLockedContent = false;
	/** @var bool */
	public $isMultiplayerGame = true;
	/** @var bool */
	public $hasLANBroadcast = true;
	/** @var int */
	public $xboxLiveBroadcastMode = 0; //TODO: find values
	/** @var int */
	public $platformBroadcastMode = 0;
	/** @var bool */
	public $commandsEnabled;
	/** @var bool */
	public $isTexturePacksRequired = true;
	/**
	 * @var mixed[][]
	 * @phpstan-var array<string, array{0: int, 1: bool|int|float}>
	 */
	public $gameRules = [ //TODO: implement this
		"naturalregeneration" => [1, false] //Hack for client side regeneration
	];
	/** @var bool */
	public $hasBonusChestEnabled = false;
	/** @var bool */
	public $hasStartWithMapEnabled = false;
	/** @var int */
	public $defaultPlayerPermission = PlayerPermissions::MEMBER; //TODO

	/** @var int */
	public $serverChunkTickRadius = 4; //TODO (leave as default for now)

	/** @var bool */
	public $hasLockedBehaviorPack = false;
	/** @var bool */
	public $hasLockedResourcePack = false;
	/** @var bool */
	public $isFromLockedWorldTemplate = false;
	/** @var bool */
	public $useMsaGamertagsOnly = false;
	/** @var bool */
	public $isFromWorldTemplate = false;
	/** @var bool */
	public $isWorldTemplateOptionLocked = false;
	/** @var bool */
	public $onlySpawnV1Villagers = false;

	/** @var string */
	public $vanillaVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK;
	/** @var string */
	public $levelId = ""; //base64 string, usually the same as world folder name in vanilla
	/** @var string */
	public $worldName;
	/** @var string */
	public $premiumWorldTemplateId = "";
	/** @var bool */
	public $isTrial = false;
	/** @var bool */
	public $isMovementServerAuthoritative = false;
	/** @var int */
	public $currentTick = 0; //only used if isTrial is true
	/** @var int */
	public $enchantmentSeed = 0;
	/** @var string */
	public $multiplayerCorrelationId = ""; //TODO: this should be filled with a UUID of some sort

	/** @var ListTag|null */
	public $blockTable = null;
	/**
	 * @var int[]|null string (name) => int16 (legacyID)
	 * @phpstan-var array<string, int>|null
	 */
	public $itemTable = null;

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->entityUniqueId = $in->getEntityUniqueId();
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$this->playerGamemode = $in->getVarInt();

		$this->playerPosition = $in->getVector3();

		$this->pitch = $in->getLFloat();
		$this->yaw = $in->getLFloat();

		//Level settings
		$this->seed = $in->getVarInt();
		$this->dimension = $in->getVarInt();
		$this->generator = $in->getVarInt();
		$this->worldGamemode = $in->getVarInt();
		$this->difficulty = $in->getVarInt();
		$in->getBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->hasAchievementsDisabled = $in->getBool();
		$this->time = $in->getVarInt();
		$this->eduEditionOffer = $in->getVarInt();
		$this->hasEduFeaturesEnabled = $in->getBool();
		$this->rainLevel = $in->getLFloat();
		$this->lightningLevel = $in->getLFloat();
		$this->hasConfirmedPlatformLockedContent = $in->getBool();
		$this->isMultiplayerGame = $in->getBool();
		$this->hasLANBroadcast = $in->getBool();
		$this->xboxLiveBroadcastMode = $in->getVarInt();
		$this->platformBroadcastMode = $in->getVarInt();
		$this->commandsEnabled = $in->getBool();
		$this->isTexturePacksRequired = $in->getBool();
		$this->gameRules = $in->getGameRules();
		$this->hasBonusChestEnabled = $in->getBool();
		$this->hasStartWithMapEnabled = $in->getBool();
		$this->defaultPlayerPermission = $in->getVarInt();
		$this->serverChunkTickRadius = $in->getLInt();
		$this->hasLockedBehaviorPack = $in->getBool();
		$this->hasLockedResourcePack = $in->getBool();
		$this->isFromLockedWorldTemplate = $in->getBool();
		$this->useMsaGamertagsOnly = $in->getBool();
		$this->isFromWorldTemplate = $in->getBool();
		$this->isWorldTemplateOptionLocked = $in->getBool();
		$this->onlySpawnV1Villagers = $in->getBool();

		$this->vanillaVersion = $in->getString();
		$this->levelId = $in->getString();
		$this->worldName = $in->getString();
		$this->premiumWorldTemplateId = $in->getString();
		$this->isTrial = $in->getBool();
		$this->isMovementServerAuthoritative = $in->getBool();
		$this->currentTick = $in->getLLong();

		$this->enchantmentSeed = $in->getVarInt();

		$offset = $in->getOffset();
		$blockTable = (new NetworkNbtSerializer())->read($in->getBuffer(), $offset, 512)->getTag();
		$in->setOffset($offset);
		if(!($blockTable instanceof ListTag)){
			throw new \UnexpectedValueException("Wrong block table root NBT tag type");
		}
		$this->blockTable = $blockTable;

		$this->itemTable = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$id = $in->getString();
			$legacyId = $in->getSignedLShort();

			$this->itemTable[$id] = $legacyId;
		}

		$this->multiplayerCorrelationId = $in->getString();
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putEntityUniqueId($this->entityUniqueId);
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$out->putVarInt($this->playerGamemode);

		$out->putVector3($this->playerPosition);

		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);

		//Level settings
		$out->putVarInt($this->seed);
		$out->putVarInt($this->dimension);
		$out->putVarInt($this->generator);
		$out->putVarInt($this->worldGamemode);
		$out->putVarInt($this->difficulty);
		$out->putBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$out->putBool($this->hasAchievementsDisabled);
		$out->putVarInt($this->time);
		$out->putVarInt($this->eduEditionOffer);
		$out->putBool($this->hasEduFeaturesEnabled);
		$out->putLFloat($this->rainLevel);
		$out->putLFloat($this->lightningLevel);
		$out->putBool($this->hasConfirmedPlatformLockedContent);
		$out->putBool($this->isMultiplayerGame);
		$out->putBool($this->hasLANBroadcast);
		$out->putVarInt($this->xboxLiveBroadcastMode);
		$out->putVarInt($this->platformBroadcastMode);
		$out->putBool($this->commandsEnabled);
		$out->putBool($this->isTexturePacksRequired);
		$out->putGameRules($this->gameRules);
		$out->putBool($this->hasBonusChestEnabled);
		$out->putBool($this->hasStartWithMapEnabled);
		$out->putVarInt($this->defaultPlayerPermission);
		$out->putLInt($this->serverChunkTickRadius);
		$out->putBool($this->hasLockedBehaviorPack);
		$out->putBool($this->hasLockedResourcePack);
		$out->putBool($this->isFromLockedWorldTemplate);
		$out->putBool($this->useMsaGamertagsOnly);
		$out->putBool($this->isFromWorldTemplate);
		$out->putBool($this->isWorldTemplateOptionLocked);
		$out->putBool($this->onlySpawnV1Villagers);

		$out->putString($this->vanillaVersion);
		$out->putString($this->levelId);
		$out->putString($this->worldName);
		$out->putString($this->premiumWorldTemplateId);
		$out->putBool($this->isTrial);
		$out->putBool($this->isMovementServerAuthoritative);
		$out->putLLong($this->currentTick);

		$out->putVarInt($this->enchantmentSeed);

		if($this->blockTable === null){
			if(self::$blockTableCache === null){
				//this is a really nasty hack, but it'll do for now
				self::$blockTableCache = (new NetworkNbtSerializer())->write(new TreeRoot(new ListTag(RuntimeBlockMapping::getBedrockKnownStates())));
			}
			$out->put(self::$blockTableCache);
		}else{
			$out->put((new NetworkNbtSerializer())->write(new TreeRoot($this->blockTable)));
		}
		if($this->itemTable === null){
			if(self::$itemTableCache === null){
				self::$itemTableCache = self::serializeItemTable(json_decode(file_get_contents(RESOURCE_PATH . '/vanilla/item_id_map.json'), true));
			}
			$out->put(self::$itemTableCache);
		}else{
			$out->put(self::serializeItemTable($this->itemTable));
		}

		$out->putString($this->multiplayerCorrelationId);
	}

	/**
	 * @param int[] $table
	 * @phpstan-param array<string, int> $table
	 */
	private static function serializeItemTable(array $table) : string{
		$stream = new NetworkBinaryStream();
		$stream->putUnsignedVarInt(count($table));
		foreach($table as $name => $legacyId){
			$stream->putString($name);
			$stream->putLShort($legacyId);
		}
		return $stream->getBuffer();
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleStartGame($this);
	}
}
