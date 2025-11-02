<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Shield extends Durable implements Releasable{

    public function getMaxStackSize(): int{
        return 1;
    }

    public function getMaxDurability() : int{
        // Vanilla shield durability
        return 336;
    }

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
        if($player->hasItemCooldown($this)){
            return ItemUseResult::FAIL;
        }

        $player->setUsingItem(true);
        return ItemUseResult::SUCCESS;
    }

    public function canStartUsingItem(Player $player) : bool{
        return !$player->hasItemCooldown($this);
    }

    public function getCooldownTag() : ?string{
        return ItemCooldownTags::SHIELD;
    }

    public function getCooldownTicks() : int{
        return 20; // short cooldown after blocking
    }

    public function onReleaseUsing(Player $player, array &$returnedItems) : ItemUseResult{
        $player->setUsingItem(false);
        if(!$player->hasItemCooldown($this)){
            $player->resetItemCooldown($this, $this->getCooldownTicks());
        }
        return ItemUseResult::SUCCESS;
    }
}
