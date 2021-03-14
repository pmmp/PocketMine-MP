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
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\network\mcpe\protocol\types\EducationEditionOffer;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\network\mcpe\protocol\types\GameRule;
use pocketmine\network\mcpe\protocol\types\GeneratorType;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\network\mcpe\protocol\types\MultiplayerGameVisibility;
use pocketmine\network\mcpe\protocol\types\PlayerMovementSettings;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\types\SpawnSettings;
use function count;

class StartGamePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::START_GAME_PACKET;

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
	/** @var SpawnSettings */
	public $spawnSettings;
	/** @var int */
	public $generator = GeneratorType::OVERWORLD;
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
	public $eduEditionOffer = EducationEditionOffer::NONE;
	/** @var bool */
	public $hasEduFeaturesEnabled = false;
	/** @var string */
	public $eduProductUUID = "";
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
	public $xboxLiveBroadcastMode = MultiplayerGameVisibility::PUBLIC;
	/** @var int */
	public $platformBroadcastMode = MultiplayerGameVisibility::PUBLIC;
	/** @var bool */
	public $commandsEnabled;
	/** @var bool */
	public $isTexturePacksRequired = true;
	/**
	 * @var GameRule[]
	 * @phpstan-var array<string, GameRule>
	 */
	public $gameRules = [];
	/** @var Experiments */
	public $experiments;
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
	/** @var int */
	public $limitedWorldWidth = 0;
	/** @var int */
	public $limitedWorldLength = 0;
	/** @var bool */
	public $isNewNether = true;
	/** @var bool|null */
	public $experimentalGameplayOverride = null;

	/** @var string */
	public $levelId = ""; //base64 string, usually the same as world folder name in vanilla
	/** @var string */
	public $worldName;
	/** @var string */
	public $premiumWorldTemplateId = "";
	/** @var bool */
	public $isTrial = false;
	/** @var PlayerMovementSettings */
	public $playerMovementSettings;
	/** @var int */
	public $currentTick = 0; //only used if isTrial is true
	/** @var int */
	public $enchantmentSeed = 0;
	/** @var string */
	public $multiplayerCorrelationId = ""; //TODO: this should be filled with a UUID of some sort
	/** @var bool */
	public $enableNewInventorySystem = false; //TODO

	/**
	 * @var BlockPaletteEntry[]
	 * @phpstan-var list<BlockPaletteEntry>
	 */
	public $blockPalette = [];
	/**
	 * @var ItemTypeEntry[]
	 * @phpstan-var list<ItemTypeEntry>
	 */
	public $itemTable;

	protected function decodePayload(PacketSerializer $in) : void{
		$this->entityUniqueId = $in->getEntityUniqueId();
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$this->playerGamemode = $in->getVarInt();

		$this->playerPosition = $in->getVector3();

		$this->pitch = $in->getLFloat();
		$this->yaw = $in->getLFloat();

		//Level settings
		$this->seed = $in->getVarInt();
		$this->spawnSettings = SpawnSettings::read($in);
		$this->generator = $in->getVarInt();
		$this->worldGamemode = $in->getVarInt();
		$this->difficulty = $in->getVarInt();
		$in->getBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->hasAchievementsDisabled = $in->getBool();
		$this->time = $in->getVarInt();
		$this->eduEditionOffer = $in->getVarInt();
		$this->hasEduFeaturesEnabled = $in->getBool();
		$this->eduProductUUID = $in->getString();
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
		$this->experiments = Experiments::read($in);
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
		$this->limitedWorldWidth = $in->getLInt();
		$this->limitedWorldLength = $in->getLInt();
		$this->isNewNether = $in->getBool();
		if($in->getBool()){
			$this->experimentalGameplayOverride = $in->getBool();
		}else{
			$this->experimentalGameplayOverride = null;
		}

		$this->levelId = $in->getString();
		$this->worldName = $in->getString();
		$this->premiumWorldTemplateId = $in->getString();
		$this->isTrial = $in->getBool();
		$this->playerMovementSettings = PlayerMovementSettings::read($in);
		$this->currentTick = $in->getLLong();

		$this->enchantmentSeed = $in->getVarInt();

		$this->blockPalette = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$blockName = $in->getString();
			$state = $in->getNbtCompoundRoot();
			$this->blockPalette[] = new BlockPaletteEntry($blockName, $state);
		}

		$this->itemTable = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$stringId = $in->getString();
			$numericId = $in->getSignedLShort();
			$isComponentBased = $in->getBool();

			$this->itemTable[] = new ItemTypeEntry($stringId, $numericId, $isComponentBased);
		}

		$this->multiplayerCorrelationId = $in->getString();
		$this->enableNewInventorySystem = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putEntityUniqueId($this->entityUniqueId);
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$out->putVarInt($this->playerGamemode);

		$out->putVector3($this->playerPosition);

		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);

		//Level settings
		$out->putVarInt($this->seed);
		$this->spawnSettings->write($out);
		$out->putVarInt($this->generator);
		$out->putVarInt($this->worldGamemode);
		$out->putVarInt($this->difficulty);
		$out->putBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$out->putBool($this->hasAchievementsDisabled);
		$out->putVarInt($this->time);
		$out->putVarInt($this->eduEditionOffer);
		$out->putBool($this->hasEduFeaturesEnabled);
		$out->putString($this->eduProductUUID);
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
		$this->experiments->write($out);
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
		$out->putLInt($this->limitedWorldWidth);
		$out->putLInt($this->limitedWorldLength);
		$out->putBool($this->isNewNether);
		$out->putBool($this->experimentalGameplayOverride !== null);
		if($this->experimentalGameplayOverride !== null){
			$out->putBool($this->experimentalGameplayOverride);
		}

		$out->putString($this->levelId);
		$out->putString($this->worldName);
		$out->putString($this->premiumWorldTemplateId);
		$out->putBool($this->isTrial);
		$this->playerMovementSettings->write($out);
		$out->putLLong($this->currentTick);

		$out->putVarInt($this->enchantmentSeed);

		$out->putUnsignedVarInt(count($this->blockPalette));
		$nbtWriter = new NetworkNbtSerializer();
		foreach($this->blockPalette as $entry){
			$out->putString($entry->getName());
			$out->put($nbtWriter->write(new TreeRoot($entry->getStates())));
		}

		$out->putUnsignedVarInt(count($this->itemTable));
		foreach($this->itemTable as $entry){
			$out->putString($entry->getStringId());
			$out->putLShort($entry->getNumericId());
			$out->putBool($entry->isComponentBased());
		}

		$out->putString($this->multiplayerCorrelationId);
		$out->putBool($this->enableNewInventorySystem);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleStartGame($this);
	}
}
