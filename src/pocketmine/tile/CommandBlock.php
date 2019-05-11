<?php

/*
 *
 *    _______                    _
 *   |__   __|                  (_)
 *      | |_   _ _ __ __ _ _ __  _  ___
 *      | | | | | '__/ _` | '_ \| |/ __|
 *      | | |_| | | | (_| | | | | | (__
 *      |_|\__,_|_|  \__,_|_| |_|_|\___|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Turanic
 *
 */

declare(strict_types=1);

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionAttachment;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

// TODO : OPTIMIZE
class CommandBlock{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
		createAdditionalNBT as createNameNBT;
	}

    const NORMAL = 0;
    const REPEATING = 1;
    const CHAIN = 2;

    private $permission;

    public function __construct(Level $level, CompoundTag $nbt){
        if(!$nbt->hasTag("Command", StringTag::class)){
            $nbt->setString("Command", ""); // komut
        }
        if(!$nbt->hasTag("blockType", IntTag::class)){
            $nbt->setInt("blockType", self::NORMAL); // komut bloğu tipi
        }
        if(!$nbt->hasTag("SuccessCount", IntTag::class)){
            $nbt->setInt("SuccessCount", 0); // başarı sayısı
        }
        if(!$nbt->hasTag("LastOutput", StringTag::class)){
            $nbt->setString("LastOutput", ""); // son çıkış
        }
        if(!$nbt->hasTag("TrackOutput", ByteTag::class)){
            $nbt->setByte("TrackOutput", 0); // komut çıkışı
        }
        if(!$nbt->hasTag("powered", ByteTag::class)){
            $nbt->setByte("powered", 0); // redstone
        }
        if(!$nbt->hasTag("conditionMet", ByteTag::class)){
            $nbt->setByte("conditionMet", 0); // koşul
        }
        if(!$nbt->hasTag("UpdateLastExecution", ByteTag::class)){
            $nbt->setByte("UpdateLastExecution", 0); // sadece bir kez çalışsın
        }
        if(!$nbt->hasTag("LastExecution", LongTag::class)){
            $nbt->setLong("LastExecution", 0); // son çalıştırma
        }
        if(!$nbt->hasTag("auto", ByteTag::class)){
            $nbt->setByte("auto", 0);
        }
        parent::__construct($level, $nbt);

        $this->permission = new PermissibleBase($this);

        $this->scheduleUpdate();
    }

    public function getDefaultName(): string{
        return "Command Block";
    }

    public function getCommand() : string{
        return $this->namedtag->getString("Command", "");
    }

    public function setCommand(string $command){
        $this->namedtag->setString("Command", $command);
    }

    public function getSuccessCount() : int{
        return $this->namedtag->getInt("SuccessCount", 0);
    }

    public function runCommand(){
        $this->server->dispatchCommand($this, $this->getCommand());
    }

    public function addAdditionalSpawnData(CompoundTag $nbt){
        $nbt->setTag($this->namedtag->getTag("blockType"));
        $nbt->setTag($this->namedtag->getTag("Command"));
        $nbt->setTag($this->namedtag->getTag("LastOutput"));
        $nbt->setTag($this->namedtag->getTag("TrackOutput"));
        $nbt->setTag($this->namedtag->getTag("SuccessCount"));
        $nbt->setTag($this->namedtag->getTag("auto"));
        $nbt->setTag($this->namedtag->getTag("powered"));
        $nbt->setTag($this->namedtag->getTag("conditionMet"));

        if($this->hasName()){
            $nbt->setTag($this->namedtag->getTag("CustomName"));
        }
    }

    public function isNormal(){
        return $this->getBlockType() == self::NORMAL;
    }

    public function isRepeating(){
        return $this->getBlockType() == self::REPEATING;
    }

    public function isChain(){
        return $this->getBlockType() == self::CHAIN;
    }

    public function getBlockType() : int{
        return $this->namedtag->getInt("blockType", self::NORMAL);
    }

    public function setBlockType(int $blockType){
        return $this->namedtag->setInt("blockType", ($blockType > 2 or $blockType < 0) ? self::NORMAL : $blockType);
    }

    public function isConditional() : int{
        return $this->namedtag->getByte("conditionMet", 0);
    }

    public function getPowered() : int{
        return $this->namedtag->getByte("powered", 0);
    }

    public function getAuto() : bool{
        return boolval($this->namedtag->getByte("auto", 0));
    }

    public function setConditional(bool $condition){
        $this->namedtag->setInt("conditionMet", +$condition);
    }

    public function setPowered(bool $powered){
        if ($this->getPowered() == $powered) {
            return;
        }
        $this->namedtag->setInt("powered", +$powered);
        if ($this->isNormal() && $powered && !$this->getAuto()) {
            $this->runCommand();
        }
    }

    public function setAuto(bool $auto){
        $this->namedtag->setInt("auto", (int) $auto);
    }

    public function setLastOutput(string $lastOutput){
        $this->namedtag->setString("LastOutput", $lastOutput);
    }

    public function getTrackOutput() : int{
        return $this->namedtag->getByte("TrackOutput", 0);
    }

    public function setTrackOutput(int $trackOutput) {
        return $this->namedtag->setByte("TrackOutput", $trackOutput);
    }

    public function getLastOutput() : string{
        return $this->namedtag->getString("LastOutput", "");
    }

    public function show(Player $player){
        $pk = new ContainerOpenPacket();
    	$pk->type = WindowTypes::COMMAND_BLOCK;
    	$pk->windowId = 64;
    	$pk->x = $this->getFloorX();
    	$pk->y = $this->getFloorY();
    	$pk->z = $this->getFloorZ();
    	$player->dataPacket($pk);
    }

    public function chainUpdate(){
        if ($this->getAuto() or $this->getPowered()) {
            $this->runCommand();
        }
    }

    public function onUpdate(){
        if ($this->closed) {
            return false;
        }
        if (!$this->isRepeating()) {
            return true;
        }
        $this->chainUpdate();
        return true;
    }

    /**
     * @param string $message
     */
    public function sendMessage($message){
        $this->setLastOutput($message);
    }

    /**
     * @return \pocketmine\Server
     */
    public function getServer(){
        return Server::getInstance();
    }

    /**
     * Checks if this instance has a permission overridden
     *
     * @param string|Permission $name
     *
     * @return bool
     */
    public function isPermissionSet($name){
        return $this->permission->isPermissionSet($name);
    }

    /**
     * Returns the permission value if overridden, or the default value if not
     *
     * @param string|Permission $name
     *
     * @return mixed
     */
    public function hasPermission($name){
        return $this->permission->hasPermission($name);
    }

    /**
     * @param Plugin $plugin
     * @param string $name
     * @param bool $value
     *
     * @return PermissionAttachment
     */
    public function addAttachment(Plugin $plugin, string $name = null, bool $value = null){
        return $this->permission->addAttachment($plugin, $name, $value);
    }

    /**
     * @param PermissionAttachment $attachment
     *
     * @return void
     */
    public function removeAttachment(PermissionAttachment $attachment){
        $this->permission->removeAttachment($attachment);
    }

    /**
     * @return void
     */
    public function recalculatePermissions(){
        $this->permission->recalculatePermissions();
    }

    public function getEffectivePermissions(){
        return $this->permission->getEffectivePermissions();
    }

    /**
     * Checks if the current object has operator permissions
     *
     * @return bool
     */
    public function isOp(){
        return true;
    }

    /**
     * Sets the operator permission for the current object
     *
     * @param bool $value
     *
     * @return void
     */
    public function setOp(bool $value){
    }

    public function getIdByBlockType($type){
        $id = [
            self::NORMAL => Block::COMMAND_BLOCK,
            self::REPEATING => Block::REPEATING_COMMAND_BLOCK,
            self::CHAIN => Block::CHAIN_COMMAND_BLOCK
        ];
        return isset($id[$type]) ? $id[$type] : Block::COMMAND_BLOCK;
    }

    public function getScreenLineHeight(): int{
        return 7;
    }

    public function setScreenLineHeight(int $height = null){
    }
}
