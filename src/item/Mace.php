<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\Living;
use pocketmine\block\BlockToolType;
use pocketmine\world\sound\MaceHeavySmashGroundSound;
use pocketmine\world\particle\BlockBreakParticle;

class Mace extends TieredTool{

    public function getBlockToolType() : int{
        // Mace is not primarily a block tool
        return BlockToolType::NONE;
    }

    public function getAttackPoints() : int{
        // Mace base attack fixed to 7 as per design (stronger than iron sword)
        return 7;
    }

    public function getMaxDurability() : int{
        // Explicit durability for Mace (500)
        return 500;
    }

    public function getBlockToolHarvestLevel() : int{
        return $this->tier->getHarvestLevel();
    }

    public function onDestroyBlock(\pocketmine\block\Block $block, array &$returnedItems) : bool{
        if(!$block->getBreakInfo()->breaksInstantly()){
            return $this->applyDamage(2);
        }
        return false;
    }

    public function onAttackEntity(Entity $victim, array &$returnedItems) : bool{
        // Default durability cost
        $res = $this->applyDamage(2);

        // apply special mace effects if attacker exists and is a player
        $last = $victim->getLastDamageCause();
        if($last instanceof EntityDamageByEntityEvent){
            $damager = $last->getDamager();
            if($damager instanceof Player){
                $fall = $damager->getFallDistance();
                if($fall > 0.0){
                    // Bonus damage: ~1 extra per block fallen, clamped to 30
                    $bonus = (int) min(30, floor($fall));
                    if($bonus > 0){
                        $last->setModifier($last->getModifier(EntityDamageEvent::MODIFIER_STRENGTH) + $bonus, EntityDamageEvent::MODIFIER_STRENGTH);
                    }
                    // Knockback: use attacker's look direction for horizontal push
                    $dir = $damager->getDirectionVector();
                    $horiz = min(1.5, $fall * 0.15);
                    $vert = min(Living::DEFAULT_KNOCKBACK_VERTICAL_LIMIT, $fall * 0.12);
                    $victim->setMotion($victim->getMotion()->add($dir->x * $horiz, $vert, $dir->z * $horiz));

                    // Play heavy smash ground sound at the victim position
                    $world = $damager->getWorld();
                    $world->addSound($victim->getPosition(), new MaceHeavySmashGroundSound());

                    // Spawn block-break particles around the victim using the block under them
                    try{
                        $center = $victim->getPosition();
                        $blockUnder = $world->getBlockAt((int)floor($center->x), (int)floor($center->y) - 1, (int)floor($center->z));
                        $positions = [];
                        for($ox = -1; $ox <= 1; $ox++){
                            for($oz = -1; $oz <= 1; $oz++){
                                $positions[] = $center->add(0.5 + $ox, 0.1, 0.5 + $oz);
                            }
                        }

                        foreach($positions as $particlePos){
                            $world->addParticle($particlePos, new BlockBreakParticle($blockUnder));
                        }
                    }catch(\Throwable $e){
                        // Non-fatal: particle/sound should not crash attack; swallow exceptions silently
                    }
                }
            }
        }

        return $res;
    }
}
