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

/**
 * This class is an automatically generated stub. Do not edit it manually.
 */
interface PacketHandlerInterface{
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

	public function handleAddActor(AddActorPacket $packet) : bool;

	public function handleRemoveActor(RemoveActorPacket $packet) : bool;

	public function handleAddItemActor(AddItemActorPacket $packet) : bool;

	public function handleTakeItemActor(TakeItemActorPacket $packet) : bool;

	public function handleMoveActorAbsolute(MoveActorAbsolutePacket $packet) : bool;

	public function handleMovePlayer(MovePlayerPacket $packet) : bool;

	public function handleRiderJump(RiderJumpPacket $packet) : bool;

	public function handleUpdateBlock(UpdateBlockPacket $packet) : bool;

	public function handleAddPainting(AddPaintingPacket $packet) : bool;

	public function handleTickSync(TickSyncPacket $packet) : bool;

	public function handleLevelSoundEventPacketV1(LevelSoundEventPacketV1 $packet) : bool;

	public function handleLevelEvent(LevelEventPacket $packet) : bool;

	public function handleBlockEvent(BlockEventPacket $packet) : bool;

	public function handleActorEvent(ActorEventPacket $packet) : bool;

	public function handleMobEffect(MobEffectPacket $packet) : bool;

	public function handleUpdateAttributes(UpdateAttributesPacket $packet) : bool;

	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool;

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool;

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool;

	public function handleInteract(InteractPacket $packet) : bool;

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool;

	public function handleActorPickRequest(ActorPickRequestPacket $packet) : bool;

	public function handlePlayerAction(PlayerActionPacket $packet) : bool;

	public function handleActorFall(ActorFallPacket $packet) : bool;

	public function handleHurtArmor(HurtArmorPacket $packet) : bool;

	public function handleSetActorData(SetActorDataPacket $packet) : bool;

	public function handleSetActorMotion(SetActorMotionPacket $packet) : bool;

	public function handleSetActorLink(SetActorLinkPacket $packet) : bool;

	public function handleSetHealth(SetHealthPacket $packet) : bool;

	public function handleSetSpawnPosition(SetSpawnPositionPacket $packet) : bool;

	public function handleAnimate(AnimatePacket $packet) : bool;

	public function handleRespawn(RespawnPacket $packet) : bool;

	public function handleContainerOpen(ContainerOpenPacket $packet) : bool;

	public function handleContainerClose(ContainerClosePacket $packet) : bool;

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool;

	public function handleInventoryContent(InventoryContentPacket $packet) : bool;

	public function handleInventorySlot(InventorySlotPacket $packet) : bool;

	public function handleContainerSetData(ContainerSetDataPacket $packet) : bool;

	public function handleCraftingData(CraftingDataPacket $packet) : bool;

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool;

	public function handleGuiDataPickItem(GuiDataPickItemPacket $packet) : bool;

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool;

	public function handleBlockActorData(BlockActorDataPacket $packet) : bool;

	public function handlePlayerInput(PlayerInputPacket $packet) : bool;

	public function handleLevelChunk(LevelChunkPacket $packet) : bool;

	public function handleSetCommandsEnabled(SetCommandsEnabledPacket $packet) : bool;

	public function handleSetDifficulty(SetDifficultyPacket $packet) : bool;

	public function handleChangeDimension(ChangeDimensionPacket $packet) : bool;

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool;

	public function handlePlayerList(PlayerListPacket $packet) : bool;

	public function handleSimpleEvent(SimpleEventPacket $packet) : bool;

	public function handleEvent(EventPacket $packet) : bool;

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool;

	public function handleClientboundMapItemData(ClientboundMapItemDataPacket $packet) : bool;

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool;

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool;

	public function handleChunkRadiusUpdated(ChunkRadiusUpdatedPacket $packet) : bool;

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool;

	public function handleGameRulesChanged(GameRulesChangedPacket $packet) : bool;

	public function handleCamera(CameraPacket $packet) : bool;

	public function handleBossEvent(BossEventPacket $packet) : bool;

	public function handleShowCredits(ShowCreditsPacket $packet) : bool;

	public function handleAvailableCommands(AvailableCommandsPacket $packet) : bool;

	public function handleCommandRequest(CommandRequestPacket $packet) : bool;

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool;

	public function handleCommandOutput(CommandOutputPacket $packet) : bool;

	public function handleUpdateTrade(UpdateTradePacket $packet) : bool;

	public function handleUpdateEquip(UpdateEquipPacket $packet) : bool;

	public function handleResourcePackDataInfo(ResourcePackDataInfoPacket $packet) : bool;

	public function handleResourcePackChunkData(ResourcePackChunkDataPacket $packet) : bool;

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool;

	public function handleTransfer(TransferPacket $packet) : bool;

	public function handlePlaySound(PlaySoundPacket $packet) : bool;

	public function handleStopSound(StopSoundPacket $packet) : bool;

	public function handleSetTitle(SetTitlePacket $packet) : bool;

	public function handleAddBehaviorTree(AddBehaviorTreePacket $packet) : bool;

	public function handleStructureBlockUpdate(StructureBlockUpdatePacket $packet) : bool;

	public function handleShowStoreOffer(ShowStoreOfferPacket $packet) : bool;

	public function handlePurchaseReceipt(PurchaseReceiptPacket $packet) : bool;

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool;

	public function handleSubClientLogin(SubClientLoginPacket $packet) : bool;

	public function handleAutomationClientConnect(AutomationClientConnectPacket $packet) : bool;

	public function handleSetLastHurtBy(SetLastHurtByPacket $packet) : bool;

	public function handleBookEdit(BookEditPacket $packet) : bool;

	public function handleNpcRequest(NpcRequestPacket $packet) : bool;

	public function handlePhotoTransfer(PhotoTransferPacket $packet) : bool;

	public function handleModalFormRequest(ModalFormRequestPacket $packet) : bool;

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool;

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool;

	public function handleServerSettingsResponse(ServerSettingsResponsePacket $packet) : bool;

	public function handleShowProfile(ShowProfilePacket $packet) : bool;

	public function handleSetDefaultGameType(SetDefaultGameTypePacket $packet) : bool;

	public function handleRemoveObjective(RemoveObjectivePacket $packet) : bool;

	public function handleSetDisplayObjective(SetDisplayObjectivePacket $packet) : bool;

	public function handleSetScore(SetScorePacket $packet) : bool;

	public function handleLabTable(LabTablePacket $packet) : bool;

	public function handleUpdateBlockSynced(UpdateBlockSyncedPacket $packet) : bool;

	public function handleMoveActorDelta(MoveActorDeltaPacket $packet) : bool;

	public function handleSetScoreboardIdentity(SetScoreboardIdentityPacket $packet) : bool;

	public function handleSetLocalPlayerAsInitialized(SetLocalPlayerAsInitializedPacket $packet) : bool;

	public function handleUpdateSoftEnum(UpdateSoftEnumPacket $packet) : bool;

	public function handleNetworkStackLatency(NetworkStackLatencyPacket $packet) : bool;

	public function handleScriptCustomEvent(ScriptCustomEventPacket $packet) : bool;

	public function handleSpawnParticleEffect(SpawnParticleEffectPacket $packet) : bool;

	public function handleAvailableActorIdentifiers(AvailableActorIdentifiersPacket $packet) : bool;

	public function handleLevelSoundEventPacketV2(LevelSoundEventPacketV2 $packet) : bool;

	public function handleNetworkChunkPublisherUpdate(NetworkChunkPublisherUpdatePacket $packet) : bool;

	public function handleBiomeDefinitionList(BiomeDefinitionListPacket $packet) : bool;

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool;

	public function handleLevelEventGeneric(LevelEventGenericPacket $packet) : bool;

	public function handleLecternUpdate(LecternUpdatePacket $packet) : bool;

	public function handleAddEntity(AddEntityPacket $packet) : bool;

	public function handleRemoveEntity(RemoveEntityPacket $packet) : bool;

	public function handleClientCacheStatus(ClientCacheStatusPacket $packet) : bool;

	public function handleOnScreenTextureAnimation(OnScreenTextureAnimationPacket $packet) : bool;

	public function handleMapCreateLockedCopy(MapCreateLockedCopyPacket $packet) : bool;

	public function handleStructureTemplateDataRequest(StructureTemplateDataRequestPacket $packet) : bool;

	public function handleStructureTemplateDataResponse(StructureTemplateDataResponsePacket $packet) : bool;

	public function handleUpdateBlockProperties(UpdateBlockPropertiesPacket $packet) : bool;

	public function handleClientCacheBlobStatus(ClientCacheBlobStatusPacket $packet) : bool;

	public function handleClientCacheMissResponse(ClientCacheMissResponsePacket $packet) : bool;

	public function handleEducationSettings(EducationSettingsPacket $packet) : bool;

	public function handleEmote(EmotePacket $packet) : bool;

	public function handleMultiplayerSettings(MultiplayerSettingsPacket $packet) : bool;

	public function handleSettingsCommand(SettingsCommandPacket $packet) : bool;

	public function handleAnvilDamage(AnvilDamagePacket $packet) : bool;

	public function handleCompletedUsingItem(CompletedUsingItemPacket $packet) : bool;

	public function handleNetworkSettings(NetworkSettingsPacket $packet) : bool;

	public function handlePlayerAuthInput(PlayerAuthInputPacket $packet) : bool;

	public function handleCreativeContent(CreativeContentPacket $packet) : bool;

	public function handlePlayerEnchantOptions(PlayerEnchantOptionsPacket $packet) : bool;

	public function handleItemStackRequest(ItemStackRequestPacket $packet) : bool;

	public function handleItemStackResponse(ItemStackResponsePacket $packet) : bool;

	public function handlePlayerArmorDamage(PlayerArmorDamagePacket $packet) : bool;

	public function handleCodeBuilder(CodeBuilderPacket $packet) : bool;

	public function handleUpdatePlayerGameType(UpdatePlayerGameTypePacket $packet) : bool;

	public function handleEmoteList(EmoteListPacket $packet) : bool;

	public function handlePositionTrackingDBServerBroadcast(PositionTrackingDBServerBroadcastPacket $packet) : bool;

	public function handlePositionTrackingDBClientRequest(PositionTrackingDBClientRequestPacket $packet) : bool;

	public function handleDebugInfo(DebugInfoPacket $packet) : bool;

	public function handlePacketViolationWarning(PacketViolationWarningPacket $packet) : bool;
}
