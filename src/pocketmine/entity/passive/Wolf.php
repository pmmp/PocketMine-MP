<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\passive;

use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\FollowOwnerBehavior;
use pocketmine\entity\behavior\HurtByTargetBehavior;
use pocketmine\entity\behavior\JumpAttackBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MeleeAttackBehavior;
use pocketmine\entity\behavior\OwnerHurtByTargetBehavior;
use pocketmine\entity\behavior\OwnerHurtTargetBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\SittingBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\Entity;
use pocketmine\entity\Tamable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\Player;

class Wolf extends Tamable{
    public const NETWORK_ID = self::WOLF;

    public $width = 0.6;
    public $height = 0.8;

    protected function addBehaviors() : void{
        $this->behaviorPool->setBehavior(0, new FloatBehavior($this));
        $this->behaviorPool->setBehavior(1, new SittingBehavior($this));
        $this->behaviorPool->setBehavior(2, new JumpAttackBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(3, new MeleeAttackBehavior($this, 2.0));
        $this->behaviorPool->setBehavior(4, new FollowOwnerBehavior($this, 2.0));
        $this->behaviorPool->setBehavior(5, new WanderBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 8.0));
        $this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));

        $this->targetBehaviorPool->setBehavior(0, new HurtByTargetBehavior($this));
        $this->targetBehaviorPool->setBehavior(1, new OwnerHurtByTargetBehavior($this));
        $this->targetBehaviorPool->setBehavior(2, new OwnerHurtTargetBehavior($this));
    }

    protected function initEntity() : void{
        $this->setMaxHealth(8);
        $this->setMovementSpeed(0.3);
        $this->setAttackDamage(3);
        $this->setFollowRange(16);

        $this->propertyManager->setInt(self::DATA_COLOR, 14); // collar color

        parent::initEntity();
    }

    public function getName() : string{
        return "Wolf";
    }

    public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : void{
        if($this->isTamed()){
            if($this->getOwningEntityId() == $player->id){
                $this->setSitting(!$this->isSitting());
            }
        }else{
            if($item->getId() == Item::BONE){
                $player->getInventory()->removeItem($item->pop());

                if(mt_rand(0, 2) == 0){
                    $this->setOwningEntity($player);
                    $this->setTamed();
                    $this->setSitting();
                    $this->setAngry(false);
                    $this->setAttackDamage(4);

                    $this->broadcastEntityEvent(EntityEventPacket::TAME_SUCCESS);
                }else{
                    $this->broadcastEntityEvent(EntityEventPacket::TAME_FAIL);
                }
            }
        }
    }

    public function setTargetEntity(?Entity $target) : void{
        parent::setTargetEntity($target);
        if($target == null){
            $this->setAngry(false);
        }elseif(!$this->isTamed()){
            $this->setAngry();
        }
    }

    public function isAngry() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_ANGRY);
    }

    public function setAngry(bool $angry = true) : void{
        $this->setGenericFlag(self::DATA_FLAG_ANGRY, $angry);
    }

}