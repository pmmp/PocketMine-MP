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


namespace pocketmine\network\mcpe;


use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\AddHangingEntityPacket;
use pocketmine\network\mcpe\protocol\AddItemEntityPacket;
use pocketmine\network\mcpe\protocol\AddItemPacket;
use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\ClientToServerHandshakePacket;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\CommandStepPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\DropItemPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\network\mcpe\protocol\HurtArmorPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryActionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\EntityFallPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\RemoveBlockPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\ReplaceItemInSlotPacket;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\RespawnPacket;
use pocketmine\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket;
use pocketmine\network\mcpe\protocol\SetCommandsEnabledPacket;
use pocketmine\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\SetHealthPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\network\mcpe\protocol\UseItemPacket;
use pocketmine\Server;

interface NetworkSession{

	/**
	 * @return Server
	 */
	public function getServer();

	public function handleDataPacket(DataPacket $pk);

	public function handleLogin(LoginPacket $packet) : bool;

	public function handlePlayStatus(PlayStatusPacket $packet) : bool;

	public function handleServerToClientHandshake(ServerToClientHandshakePacket $packet) : bool;

	public function handleClientToServerHandshake(ClientToServerHandshakePacket $packet) : bool;

	public function handleDisconnect(DisconnectPacket $packet) : bool;

	public function handleResourcePacksInfo(ResourcePacksInfoPacket $packet) : bool;

	public function handleResourcePackStack(ResourcePackStackPacket $packet) : bool;

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool;

	public function handleText(TextPacket $packet) : bool;

	public function handleSetTime(SetTimePacket $packet) : bool;

	public function handleStartGame(StartGamePacket $packet) : bool;

	public function handleAddPlayer(AddPlayerPacket $packet) : bool;

	public function handleAddEntity(AddEntityPacket $packet) : bool;

	public function handleRemoveEntity(RemoveEntityPacket $packet) : bool;

	public function handleAddItemEntity(AddItemEntityPacket $packet) : bool;

	public function handleAddHangingEntity(AddHangingEntityPacket $packet) : bool;

	public function handleTakeItemEntity(TakeItemEntityPacket $packet) : bool;

	public function handleMoveEntity(MoveEntityPacket $packet) : bool;

	public function handleMovePlayer(MovePlayerPacket $packet) : bool;

	public function handleRiderJump(RiderJumpPacket $packet) : bool;

	public function handleRemoveBlock(RemoveBlockPacket $packet) : bool;

	public function handleUpdateBlock(UpdateBlockPacket $packet) : bool;

	public function handleAddPainting(AddPaintingPacket $packet) : bool;

	public function handleExplode(ExplodePacket $packet) : bool;

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool;

	public function handleLevelEvent(LevelEventPacket $packet) : bool;

	public function handleBlockEvent(BlockEventPacket $packet) : bool;

	public function handleEntityEvent(EntityEventPacket $packet) : bool;

	public function handleMobEffect(MobEffectPacket $packet) : bool;

	public function handleUpdateAttributes(UpdateAttributesPacket $packet) : bool;

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool;

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool;

	public function handleInteract(InteractPacket $packet) : bool;

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool;

	public function handleUseItem(UseItemPacket $packet) : bool;

	public function handlePlayerAction(PlayerActionPacket $packet) : bool;

	public function handleEntityFall(EntityFallPacket $packet) : bool;

	public function handleHurtArmor(HurtArmorPacket $packet) : bool;

	public function handleSetEntityData(SetEntityDataPacket $packet) : bool;

	public function handleSetEntityMotion(SetEntityMotionPacket $packet) : bool;

	public function handleSetEntityLink(SetEntityLinkPacket $packet) : bool;

	public function handleSetHealth(SetHealthPacket $packet) : bool;

	public function handleSetSpawnPosition(SetSpawnPositionPacket $packet) : bool;

	public function handleAnimate(AnimatePacket $packet) : bool;

	public function handleRespawn(RespawnPacket $packet) : bool;

	public function handleDropItem(DropItemPacket $packet) : bool;

	public function handleInventoryAction(InventoryActionPacket $packet) : bool;

	public function handleContainerOpen(ContainerOpenPacket $packet) : bool;

	public function handleContainerClose(ContainerClosePacket $packet) : bool;

	public function handleContainerSetSlot(ContainerSetSlotPacket $packet) : bool;

	public function handleContainerSetData(ContainerSetDataPacket $packet) : bool;

	public function handleContainerSetContent(ContainerSetContentPacket $packet) : bool;

	public function handleCraftingData(CraftingDataPacket $packet) : bool;

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool;

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool;

	public function handleBlockEntityData(BlockEntityDataPacket $packet) : bool;

	public function handlePlayerInput(PlayerInputPacket $packet) : bool;

	public function handleFullChunkData(FullChunkDataPacket $packet) : bool;

	public function handleSetCommandsEnabled(SetCommandsEnabledPacket $packet) : bool;

	public function handleSetDifficulty(SetDifficultyPacket $packet) : bool;

	public function handleChangeDimension(ChangeDimensionPacket $packet) : bool;

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool;

	public function handlePlayerList(PlayerListPacket $packet) : bool;

	//public function handleTelemetryEvent(EventPacket $packet) : bool; //TODO

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool;

	public function handleClientboundMapItemData(ClientboundMapItemDataPacket $packet) : bool;

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool; //TODO

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool;

	public function handleChunkRadiusUpdated(ChunkRadiusUpdatedPacket $packet) : bool;

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool;

	public function handleReplaceItemInSlot(ReplaceItemInSlotPacket $packet) : bool;

	//public function handleGameRulesChanged(GameRulesChangedPacket $packet) : bool; //TODO

	//public function handleCamera(CameraPacket $packet) : bool; //edu only :(

	public function handleAddItem(AddItemPacket $packet) : bool;

	public function handleBossEvent(BossEventPacket $packet) : bool;

	public function handleShowCredits(ShowCreditsPacket $packet) : bool;

	public function handleAvailableCommands(AvailableCommandsPacket $packet) : bool;

	public function handleCommandStep(CommandStepPacket $packet) : bool;

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool;

	public function handleUpdateTrade(UpdateTradePacket $packet) : bool;

	public function handleResourcePackDataInfo(ResourcePackDataInfoPacket $packet) : bool;

	public function handleResourcePackChunkData(ResourcePackChunkDataPacket $packet) : bool;

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool;

	public function handleTransfer(TransferPacket $packet) : bool;

	public function handlePlaySound(PlaySoundPacket $packet) : bool;

	public function handleStopSound(StopSoundPacket $packet) : bool;

	public function handleSetTitle(SetTitlePacket $packet) : bool;
}