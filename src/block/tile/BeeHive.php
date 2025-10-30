<?php

declare(strict_types=1);

namespace pocketmine\block\tile;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ByteTag;

class BeeHive extends Spawnable{
    private const TAG_OCCUPANTS = "Occupants";
    private const TAG_SHOULD_SPAWN = "ShouldSpawnBees";

    /** @var array<int, array{ActorIdentifier: string, SaveData: CompoundTag, TicksLeftToStay: int}> */
    private array $occupants = [];
    private bool $shouldSpawnBees = false;

    public function readSaveData(CompoundTag $nbt) : void{
        $this->occupants = [];

        $occ = $nbt->getTag(self::TAG_OCCUPANTS);
        // Some worlds store a CompoundTag that contains a ListTag named "Occupants",
        // others directly store a ListTag. Handle both.
        if($occ instanceof CompoundTag && ($list = $occ->getTag(self::TAG_OCCUPANTS)) instanceof ListTag){
            $this->readOccupantsList($list);
        }elseif($occ instanceof ListTag){
            $this->readOccupantsList($occ);
        }

        $should = $nbt->getTag(self::TAG_SHOULD_SPAWN);
        if($should instanceof ByteTag){
            $this->shouldSpawnBees = $should->getValue() !== 0;
        }
    }

    private function readOccupantsList(ListTag $list) : void{
        foreach($list->getValue() as $entry){
            if($entry instanceof CompoundTag){
                $actor = $entry->getString("ActorIdentifier", "");
                $saveData = $entry->getCompoundTag("SaveData") ?? CompoundTag::create();
                $ticks = 0;
                if(($t = $entry->getTag("TicksLeftToStay")) instanceof IntTag){
                    $ticks = $t->getValue();
                }
                $this->occupants[] = ["ActorIdentifier" => $actor, "SaveData" => $saveData, "TicksLeftToStay" => $ticks];
            }
        }
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        if(count($this->occupants) > 0){
            $list = new ListTag();
            foreach($this->occupants as $occ){
                $ct = CompoundTag::create()
                    ->setString("ActorIdentifier", $occ["ActorIdentifier"])
                    ->setTag("SaveData", $occ["SaveData"])
                    ->setInt("TicksLeftToStay", $occ["TicksLeftToStay"]);
                $list->push($ct);
            }
            // match the format written by the block: a CompoundTag containing a ListTag named "Occupants"
            $compound = CompoundTag::create()->setTag(self::TAG_OCCUPANTS, $list);
            $nbt->setTag(self::TAG_OCCUPANTS, $compound);
        }

        $nbt->setByte(self::TAG_SHOULD_SPAWN, $this->shouldSpawnBees ? 1 : 0);
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        // add the same tags used for saving so clients receive consistent data
        if(count($this->occupants) > 0){
            $list = new ListTag();
            foreach($this->occupants as $occ){
                $ct = CompoundTag::create()
                    ->setString("ActorIdentifier", $occ["ActorIdentifier"])
                    ->setTag("SaveData", $occ["SaveData"])
                    ->setInt("TicksLeftToStay", $occ["TicksLeftToStay"]);
                $list->push($ct);
            }
            $compound = CompoundTag::create()->setTag(self::TAG_OCCUPANTS, $list);
            $nbt->setTag(self::TAG_OCCUPANTS, $compound);
        }

        $nbt->setByte(self::TAG_SHOULD_SPAWN, $this->shouldSpawnBees ? 1 : 0);
    }

    /** @return array<int, array{ActorIdentifier: string, SaveData: CompoundTag, TicksLeftToStay: int}> */
    public function getOccupants() : array{
        return $this->occupants;
    }

    public function setOccupants(array $occupants) : void{
        $this->occupants = $occupants;
        $this->clearSpawnCompoundCache();
    }

    public function getShouldSpawnBees() : bool{
        return $this->shouldSpawnBees;
    }

    public function setShouldSpawnBees(bool $val) : void{
        $this->shouldSpawnBees = $val;
        $this->clearSpawnCompoundCache();
    }
}
