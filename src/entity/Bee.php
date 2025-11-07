<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\entity\ai\goal\BeeStingGoal;
use pocketmine\entity\ai\goal\FloatGoal;
use pocketmine\entity\ai\goal\HurtByTargetGoal;
use pocketmine\entity\ai\goal\LookAtPlayerGoal;
use pocketmine\entity\ai\goal\RandomFlyGoal;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use function max;
use function min;
use function mt_rand;
use const PHP_FLOAT_MAX;

class Bee extends Living
{
    private const TAG_HAS_STUNG = "HasStung";
    private const TAG_ANGER_TICKS = "AngerTicks";
    private const TAG_POST_STING_TICKS = "PostStingTicks";

    private const MAX_ANGER_TICKS = 200;
    private const POST_STING_LIFESPAN_TICKS = 1200; // 60 seconds
    private const POST_STING_DAMAGE_INTERVAL = 200;

    private bool $hasStung = false;
    private int $angerTicks = 0;
    private int $postStingTicks = 0;

    public static function getNetworkTypeId(): string
    {
        return EntityIds::BEE;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.7, 0.6);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.02;
    }

    protected function getInitialGravity(): float
    {
        return 0.03;
    }

    public function initEntity(CompoundTag $nbt): void
    {
        $this->setMaxHealth(10);
        parent::initEntity($nbt);

        $this->setHasGravity(false);

        $this->hasStung = $nbt->getByte(self::TAG_HAS_STUNG, 0) !== 0;
        $this->angerTicks = max(0, $nbt->getShort(self::TAG_ANGER_TICKS, 0));
        $this->postStingTicks = max(0, $nbt->getInt(self::TAG_POST_STING_TICKS, 0));

        if ($this->angerTicks > 0 && $this->getAttackTarget() === null) {
            $target = $this->findNearestAttackTarget();
            if ($target !== null) {
                $this->setAttackTarget($target);
            }
        }

        if ($this->isAngry()) {
            $this->networkPropertiesDirty = true;
        }
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setByte(self::TAG_HAS_STUNG, $this->hasStung ? 1 : 0);
        $nbt->setShort(self::TAG_ANGER_TICKS, $this->angerTicks);
        $nbt->setInt(self::TAG_POST_STING_TICKS, $this->postStingTicks);
        return $nbt;
    }

    public function getName(): string
    {
        return "Bee";
    }

    /**
     * @return Item[]
     */
    public function getDrops(): array
    {
        return [];
    }

    public function getXpDropAmount(): int
    {
        return mt_rand(1, 3);
    }

    protected function registerGoals(): void
    {
        parent::registerGoals();
        $goals = $this->getGoalSelector();
        $goals->addGoal(0, new FloatGoal($this));
        $goals->addGoal(1, new BeeStingGoal($this, 0.35));
        $goals->addGoal(2, new RandomFlyGoal($this, 0.2));
        $goals->addGoal(3, new LookAtPlayerGoal($this, 6.0));

        $targets = $this->getTargetSelector();
        $targets->addGoal(0, new HurtByTargetGoal($this));
    }

    public function hasStung(): bool
    {
        return $this->hasStung;
    }

    public function stingTarget(Living $target): bool
    {
        if ($this->hasStung || !$target->isAlive()) {
            return false;
        }

        $event = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 2.0);
        $target->attack($event);
        if ($event->isCancelled()) {
            return false;
        }

        if ($target->isAlive()) {
            $target->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 200, 0));
        }

        $this->hasStung = true;
        $this->postStingTicks = 0;
        $this->angerTicks = 0;
        $this->setAttackTarget(null);
        $this->networkPropertiesDirty = true;

        return true;
    }

    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        if ($source->isCancelled()) {
            return;
        }

        if ($source instanceof EntityDamageByEntityEvent) {
            $this->becomeAngryAt($source->getDamager());
        }
    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        $wasAngry = $this->isAngry();
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if ($this->angerTicks > 0) {
            $this->angerTicks = max(0, $this->angerTicks - $tickDiff);
            if ($this->angerTicks === 0) {
                $this->setAttackTarget(null);
            } else {
                $target = $this->getAttackTarget();
                if ($target === null || !$target->isAlive()) {
                    $newTarget = $this->findNearestAttackTarget();
                    if ($newTarget !== null) {
                        $this->setAttackTarget($newTarget);
                    }
                }
            }
            $hasUpdate = true;
        }

        if ($wasAngry !== $this->isAngry()) {
            $this->networkPropertiesDirty = true;
        }

        if ($this->hasStung && $this->isAlive()) {
            $this->postStingTicks = min(self::POST_STING_LIFESPAN_TICKS, $this->postStingTicks + $tickDiff);
            if ($this->postStingTicks % self::POST_STING_DAMAGE_INTERVAL === 0) {
                $this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_CUSTOM, 1.0));
            }
            if ($this->postStingTicks >= self::POST_STING_LIFESPAN_TICKS && $this->isAlive()) {
                $this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_CUSTOM, $this->getHealth()));
            }
            $hasUpdate = true;
        }

        return $hasUpdate;
    }

    private function becomeAngryAt(?Entity $entity): void
    {
        $wasAngry = $this->isAngry();
        $this->angerTicks = self::MAX_ANGER_TICKS;
        if ($entity instanceof Living && $this->isValidAttackTarget($entity)) {
            $this->setAttackTarget($entity);
        }
        if (!$wasAngry && $this->isAngry()) {
            $this->networkPropertiesDirty = true;
        }
    }

    private function findNearestAttackTarget(): ?Living
    {
        $world = $this->getWorld();
        $radius = 8.0;
        $vertical = 4.0;
        $bb = $this->getBoundingBox()->expandedCopy($radius, $vertical, $radius);

        $nearest = null;
        $nearestDist = PHP_FLOAT_MAX;
        foreach ($world->getNearbyEntities($bb, $this) as $entity) {
            if (!$entity instanceof Living || !$entity->isAlive()) {
                continue;
            }
            if (!$this->isValidAttackTarget($entity)) {
                continue;
            }

            $dist = $entity->getPosition()->distanceSquared($this->getPosition());
            if ($dist < $nearestDist) {
                $nearestDist = $dist;
                $nearest = $entity;
            }
        }

        return $nearest;
    }

    private function isValidAttackTarget(Living $entity): bool
    {
        if ($entity === $this) {
            return false;
        }

        if ($entity instanceof Bee) {
            return false;
        }

        if ($entity instanceof Player && !$entity->hasFiniteResources()) {
            return false;
        }

        return true;
    }

    public function knockBack(float $x, float $z, float $force = self::DEFAULT_KNOCKBACK_FORCE, ?float $verticalLimit = self::DEFAULT_KNOCKBACK_VERTICAL_LIMIT): void
    {
        $reducedForce = min($force * 0.35, 0.2);
        $reducedVertical = ($verticalLimit ?? $force) * 0.25;
        parent::knockBack($x, $z, $reducedForce, $reducedVertical);
    }

    public function isAngry(): bool
    {
        return !$this->hasStung && $this->angerTicks > 0;
    }

    protected function syncNetworkData(EntityMetadataCollection $properties): void
    {
        parent::syncNetworkData($properties);
        $properties->setGenericFlag(EntityMetadataFlags::ANGRY, $this->isAngry());
    }
}