<?php

declare(strict_types=1);

namespace pocketmine\block\tile;

use pocketmine\block\Block;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

final class CopperGolemStatue extends Spawnable{
    private const TAG_POSE = "Pose";

    private int $pose = 0;

    public function getPose() : int{
        return $this->pose;
    }

    public function setPose(int $pose) : void{
        $this->pose = $pose;
        $this->clearSpawnCompoundCache();
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        $nbt->setInt(self::TAG_POSE, $this->pose);
        // Provide a render-update workaround property in case the block state needs nudging
        $props = $this->getRenderUpdateBugWorkaroundStateProperties($this->getBlock());
        foreach($props as $k => $v){
            $nbt->setTag((string) $k, $v);
        }
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->pose = $nbt->getInt(self::TAG_POSE, 0);
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $nbt->setInt(self::TAG_POSE, $this->pose);
    }

    public function getRenderUpdateBugWorkaroundStateProperties(Block $block) : array{
        // No special workaround; override if needed later
        return [];
    }
}
