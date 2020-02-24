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

	protected function decodePayload() : void{
		$this->entityUniqueId = $this->buf->getEntityUniqueId();
		$this->entityRuntimeId = $this->buf->getEntityRuntimeId();
		$this->playerGamemode = $this->buf->getVarInt();

		$this->playerPosition = $this->buf->getVector3();

		$this->pitch = $this->buf->getLFloat();
		$this->yaw = $this->buf->getLFloat();

		//Level settings
		$this->seed = $this->buf->getVarInt();
		$this->dimension = $this->buf->getVarInt();
		$this->generator = $this->buf->getVarInt();
		$this->worldGamemode = $this->buf->getVarInt();
		$this->difficulty = $this->buf->getVarInt();
		$this->buf->getBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->hasAchievementsDisabled = $this->buf->getBool();
		$this->time = $this->buf->getVarInt();
		$this->eduEditionOffer = $this->buf->getVarInt();
		$this->hasEduFeaturesEnabled = $this->buf->getBool();
		$this->rainLevel = $this->buf->getLFloat();
		$this->lightningLevel = $this->buf->getLFloat();
		$this->hasConfirmedPlatformLockedContent = $this->buf->getBool();
		$this->isMultiplayerGame = $this->buf->getBool();
		$this->hasLANBroadcast = $this->buf->getBool();
		$this->xboxLiveBroadcastMode = $this->buf->getVarInt();
		$this->platformBroadcastMode = $this->buf->getVarInt();
		$this->commandsEnabled = $this->buf->getBool();
		$this->isTexturePacksRequired = $this->buf->getBool();
		$this->gameRules = $this->buf->getGameRules();
		$this->hasBonusChestEnabled = $this->buf->getBool();
		$this->hasStartWithMapEnabled = $this->buf->getBool();
		$this->defaultPlayerPermission = $this->buf->getVarInt();
		$this->serverChunkTickRadius = $this->buf->getLInt();
		$this->hasLockedBehaviorPack = $this->buf->getBool();
		$this->hasLockedResourcePack = $this->buf->getBool();
		$this->isFromLockedWorldTemplate = $this->buf->getBool();
		$this->useMsaGamertagsOnly = $this->buf->getBool();
		$this->isFromWorldTemplate = $this->buf->getBool();
		$this->isWorldTemplateOptionLocked = $this->buf->getBool();
		$this->onlySpawnV1Villagers = $this->buf->getBool();

		$this->vanillaVersion = $this->buf->getString();
		$this->levelId = $this->buf->getString();
		$this->worldName = $this->buf->getString();
		$this->premiumWorldTemplateId = $this->buf->getString();
		$this->isTrial = $this->buf->getBool();
		$this->isMovementServerAuthoritative = $this->buf->getBool();
		$this->currentTick = $this->buf->getLLong();

		$this->enchantmentSeed = $this->buf->getVarInt();

		$offset = $this->buf->getOffset();
		$blockTable = (new NetworkNbtSerializer())->read($this->buf->getBuffer(), $offset, 512)->getTag();
		$this->buf->setOffset($offset);
		if(!($blockTable instanceof ListTag)){
			throw new \UnexpectedValueException("Wrong block table root NBT tag type");
		}
		$this->blockTable = $blockTable;

		$this->itemTable = [];
		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$id = $this->buf->getString();
			$legacyId = $this->buf->getSignedLShort();

			$this->itemTable[$id] = $legacyId;
		}

		$this->multiplayerCorrelationId = $this->buf->getString();
	}

	protected function encodePayload() : void{
		$this->buf->putEntityUniqueId($this->entityUniqueId);
		$this->buf->putEntityRuntimeId($this->entityRuntimeId);
		$this->buf->putVarInt($this->playerGamemode);

		$this->buf->putVector3($this->playerPosition);

		$this->buf->putLFloat($this->pitch);
		$this->buf->putLFloat($this->yaw);

		//Level settings
		$this->buf->putVarInt($this->seed);
		$this->buf->putVarInt($this->dimension);
		$this->buf->putVarInt($this->generator);
		$this->buf->putVarInt($this->worldGamemode);
		$this->buf->putVarInt($this->difficulty);
		$this->buf->putBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->buf->putBool($this->hasAchievementsDisabled);
		$this->buf->putVarInt($this->time);
		$this->buf->putVarInt($this->eduEditionOffer);
		$this->buf->putBool($this->hasEduFeaturesEnabled);
		$this->buf->putLFloat($this->rainLevel);
		$this->buf->putLFloat($this->lightningLevel);
		$this->buf->putBool($this->hasConfirmedPlatformLockedContent);
		$this->buf->putBool($this->isMultiplayerGame);
		$this->buf->putBool($this->hasLANBroadcast);
		$this->buf->putVarInt($this->xboxLiveBroadcastMode);
		$this->buf->putVarInt($this->platformBroadcastMode);
		$this->buf->putBool($this->commandsEnabled);
		$this->buf->putBool($this->isTexturePacksRequired);
		$this->buf->putGameRules($this->gameRules);
		$this->buf->putBool($this->hasBonusChestEnabled);
		$this->buf->putBool($this->hasStartWithMapEnabled);
		$this->buf->putVarInt($this->defaultPlayerPermission);
		$this->buf->putLInt($this->serverChunkTickRadius);
		$this->buf->putBool($this->hasLockedBehaviorPack);
		$this->buf->putBool($this->hasLockedResourcePack);
		$this->buf->putBool($this->isFromLockedWorldTemplate);
		$this->buf->putBool($this->useMsaGamertagsOnly);
		$this->buf->putBool($this->isFromWorldTemplate);
		$this->buf->putBool($this->isWorldTemplateOptionLocked);
		$this->buf->putBool($this->onlySpawnV1Villagers);

		$this->buf->putString($this->vanillaVersion);
		$this->buf->putString($this->levelId);
		$this->buf->putString($this->worldName);
		$this->buf->putString($this->premiumWorldTemplateId);
		$this->buf->putBool($this->isTrial);
		$this->buf->putBool($this->isMovementServerAuthoritative);
		$this->buf->putLLong($this->currentTick);

		$this->buf->putVarInt($this->enchantmentSeed);

		if($this->blockTable === null){
			if(self::$blockTableCache === null){
				//this is a really nasty hack, but it'll do for now
				self::$blockTableCache = (new NetworkNbtSerializer())->write(new TreeRoot(new ListTag(RuntimeBlockMapping::getBedrockKnownStates())));
			}
			$this->buf->put(self::$blockTableCache);
		}else{
			$this->buf->put((new NetworkNbtSerializer())->write(new TreeRoot($this->blockTable)));
		}
		if($this->itemTable === null){
			if(self::$itemTableCache === null){
				self::$itemTableCache = self::serializeItemTable(json_decode(file_get_contents(RESOURCE_PATH . '/vanilla/item_id_map.json'), true));
			}
			$this->buf->put(self::$itemTableCache);
		}else{
			$this->buf->put(self::serializeItemTable($this->itemTable));
		}

		$this->buf->putString($this->multiplayerCorrelationId);
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
