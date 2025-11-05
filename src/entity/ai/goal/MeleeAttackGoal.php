<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\Living;
use pocketmine\entity\ai\Goal;
use pocketmine\entity\Entity;
use pocketmine\entity\Attribute;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use function atan2;
use function max;
use function rad2deg;
use function sqrt;

class MeleeAttackGoal implements Goal
{
    private int $attackCooldown = 0;

    public function __construct(
        private Living $mob,
        private float $speed,
        private float $attackReach,
        private int $attackInterval = 20
    ) {
    }

    public function canStart(): bool
    {
        $target = $this->mob->getAttackTarget();
        return $target !== null && !$target->isClosed() && $target->isAlive();
    }

    public function start(): void
    {
        $this->attackCooldown = 0;
    }

    public function tick(int $tickDiff): void
    {
        $target = $this->mob->getAttackTarget();
        if ($target === null) {
            return;
        }

        $this->attackCooldown = max(0, $this->attackCooldown - $tickDiff);

        $mobPos = $this->mob->getLocation();
        $targetPos = $target->getLocation();
        $dx = $targetPos->x - $mobPos->x;
        $dz = $targetPos->z - $mobPos->z;
        $dy = ($target->getEyeHeight() / 2 + $targetPos->y) - $this->mob->getEyePos()->y;

        $horizontalDist = sqrt($dx ** 2 + $dz ** 2);
        if ($horizontalDist > 0) {
            $yaw = rad2deg(atan2(-$dx, $dz));
            $pitch = rad2deg(-atan2($dy, $horizontalDist));
            $this->mob->setRotation($yaw, $pitch);
        }

        $distanceSq = $mobPos->distanceSquared($targetPos);
        $attackReachSq = $this->attackReach ** 2;

        if ($distanceSq > $attackReachSq) {
            if ($horizontalDist > 0) {
                $currentMotion = $this->mob->getMotion();
                $factor = $this->speed / $horizontalDist;
                $motion = new Vector3($dx * $factor, $currentMotion->y, $dz * $factor);
                $this->mob->setMotion($motion);
                $this->mob->setForceMovementUpdate();
            }
        } elseif ($this->attackCooldown <= 0) {
            $this->attackCooldown = $this->attackInterval;
            $damageAttribute = $this->mob->getAttributeMap()->get(Attribute::ATTACK_DAMAGE);
            $damage = $damageAttribute !== null ? $damageAttribute->getValue() : 2.0;
            $target->attack(new EntityDamageByEntityEvent($this->mob, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage));
        }
    }

    public function shouldContinue(): bool
    {
        $target = $this->mob->getAttackTarget();
        if ($target === null || $target->isClosed() || !$target->isAlive()) {
            return false;
        }
        return true;
    }

    public function stop(): void
    {
        $this->attackCooldown = 0;
    }
}
