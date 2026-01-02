<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\item\enchantment\VanillaEnchantments as Enchantments;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;

class Spear extends TieredTool implements Releasable{
	private const MIN_JAB_RANGE = 2.0;
	private const MAX_JAB_RANGE = 4.5;
	private const CREATIVE_MAX_JAB_RANGE = 7.5;
	private const HITBOX_MARGIN = 0.25;
	private const HOLD_TICK_INTERVAL = 4;
	private const HOLD_DAMAGE_MULTIPLIER = 0.65;
	private const HOLD_HIT_COOLDOWN_TICKS = 6;
	private const KINETIC_DELAY_TICKS = 6;
	private const MIN_CHARGE_TICKS = 10;
	private const MAX_CHARGE_TICKS = 18;
	public const ATTACK_COOLDOWN_TICKS = 8;

	/** @var array<int, array<int, int>> */
	private static array $recentHoldHits = [];

	public function getBlockToolType(): int{
		return BlockToolType::SPEAR;
	}

	public function getAttackPoints(): int{
		return match($this->tier){
			ToolTier::WOOD => 1,
			ToolTier::GOLD => 1,
			ToolTier::STONE => 2,
			ToolTier::COPPER => 2,
			ToolTier::IRON => 3,
			ToolTier::DIAMOND => 4,
			ToolTier::NETHERITE => 5,
		};
	}

	public function getAttackCooldownTicks(): int{
		return self::ATTACK_COOLDOWN_TICKS;
	}

	public function getCooldownTag(): ?string{
		return ItemCooldownTags::SPEAR;
	}

	public function canStartUsingItem(Player $player): bool{
		return !$this->isBroken();
	}

	public function onAttackEntity(Entity $victim, array &$returnedItems): bool{
		return $this->applyDamage(1);
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult{
		return ItemUseResult::SUCCESS;
	}

	public function onUsingTick(Player $player, int $ticksUsed): void{
		if($ticksUsed <= 0 || $ticksUsed < self::KINETIC_DELAY_TICKS){
			return;
		}
		if(($ticksUsed % self::HOLD_TICK_INTERVAL) !== 0){
			return;
		}

		$this->applyHoldingDamage($player, $ticksUsed);
	}

	private function applyHoldingDamage(Player $player, int $ticksUsed): void{
		$dir = $player->getDirectionVector();
		$eye = $player->getEyePos();
		$maxRange = $this->getMaxReach($player);
		$bb = $player->getBoundingBox()->expandedCopy($maxRange + self::HITBOX_MARGIN, 1.5, $maxRange + self::HITBOX_MARGIN);
		$chargeLevel = (int) min(3, floor($ticksUsed / 10));

		foreach($player->getWorld()->getNearbyEntities($bb, $player) as $entity){
			if($entity === $player || !$entity->isAlive()){
				continue;
			}
			if(self::wasRecentlyHit($player, $entity, self::HOLD_HIT_COOLDOWN_TICKS)){
				continue;
			}
			$toEnt = $entity->getLocation()->subtractVector($eye);
			if($toEnt->lengthSquared() <= 0){
				continue;
			}
			$dot = $dir->dot($toEnt->normalize());
			if($dot < 0.0){
				continue;
			}
			$dist = $eye->distance($entity->getLocation());
			if(!$this->isWithinReach($dist, $maxRange)){
				continue;
			}

			$relativeSpeed = $this->computeRelativeForwardSpeed($player, $entity, $dir);
			$damage = $this->calculateDamage($chargeLevel, $relativeSpeed, self::HOLD_DAMAGE_MULTIPLIER);
			$ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
			$entity->attack($ev);
			if($ev->isCancelled() || $ev->getFinalDamage() <= 0){
				continue;
			}

			self::markHoldHit($player, $entity);
			try{
				$entity->addMotion($dir->x * 0.2, 0.05, $dir->z * 0.2);
			}catch(\Throwable $_){}
			$this->applyDamage(1);
			$player->getHungerManager()->exhaust(0.05, PlayerExhaustEvent::CAUSE_ATTACK);
			break;
		}
	}

	public function onReleaseUsing(Player $player, array &$returnedItems): ItemUseResult{
		self::clearRecentHoldHits($player);

		$useTicks = min(self::MAX_CHARGE_TICKS, $player->getItemUseDuration());
		if($useTicks <= 0){
			return $player->isSprinting() && $this->performLungeAttack($player, $this->calculateDamage(0, max(0.0, $player->getMotion()->dot($player->getDirectionVector()))))
				? ItemUseResult::SUCCESS
				: ItemUseResult::NONE;
		}

		$chargeLevel = max(0, (int) floor($useTicks / self::MIN_CHARGE_TICKS));
		$world = $player->getWorld();
		$eye = $player->getEyePos();
		$dir = $player->getDirectionVector();
		$maxRange = $this->getMaxReach($player);
		$bb = $player->getBoundingBox()->expandedCopy($maxRange + self::HITBOX_MARGIN, 1.5, $maxRange + self::HITBOX_MARGIN);

		$hitSomething = false;
		$hitIds = [];
		foreach($world->getNearbyEntities($bb, $player) as $entity){
			if($entity === $player || !$entity->isAlive()){
				continue;
			}
			$toEnt = $entity->getLocation()->subtractVector($eye);
			if($toEnt->lengthSquared() <= 0){
				continue;
			}
			$dot = $dir->dot($toEnt->normalize());
			if($dot < 0.0){
				continue;
			}
			$dist = $eye->distance($entity->getLocation());
			if(!$this->isWithinReach($dist, $maxRange)){
				continue;
			}
			if(in_array($entity->getId(), $hitIds, true)){
				continue;
			}

			$relativeSpeed = $this->computeRelativeForwardSpeed($player, $entity, $dir);
			$damage = $this->calculateDamage($chargeLevel, $relativeSpeed);

			$ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
			$entity->attack($ev);
			if($ev->isCancelled() || $ev->getFinalDamage() <= 0){
				continue;
			}

			$hitSomething = true;
			$hitIds[] = $entity->getId();
			try{
				$entity->addMotion($dir->x * 0.35, 0.08, $dir->z * 0.35);
			}catch(\Throwable $_){}
		}

		if($hitSomething){
			$this->applyDamage(1);
			$this->maybeTriggerLungeMotion($player, $chargeLevel);
			$player->getHungerManager()->exhaust(0.1, PlayerExhaustEvent::CAUSE_ATTACK);
			return ItemUseResult::SUCCESS;
		}

		return ItemUseResult::NONE;
	}

	public function performManualJabFallback(Player $player, Entity $originalTarget): ?EntityDamageByEntityEvent{
		$eye = $player->getEyePos();
		$dir = $player->getDirectionVector();
		$maxRange = $this->getMaxReach($player);
		$distance = $eye->distance($originalTarget->getLocation());
		if(!$this->isWithinReach($distance, $maxRange)){
			return null;
		}

		$toTarget = $originalTarget->getLocation()->subtractVector($eye);
		if($toTarget->lengthSquared() <= 0){
			return null;
		}
		$dot = $dir->dot($toTarget->normalize());
		if($dot < 0.2){
			return null;
		}

		$damage = $this->calculateDamage(0, $this->computeRelativeForwardSpeed($player, $originalTarget, $dir));
		$ev = new EntityDamageByEntityEvent($player, $originalTarget, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
		$originalTarget->attack($ev);
		if($ev->isCancelled() || $ev->getFinalDamage() <= 0){
			return null;
		}

		$this->applyDamage(1);
		return $ev;
	}

	private function performLungeAttack(Player $player, int $damage): bool{
		$dir = $player->getDirectionVector();
		$eye = $player->getEyePos();
		$maxRange = $this->getMaxReach($player);
		$bb = $player->getBoundingBox()->expandedCopy($maxRange + self::HITBOX_MARGIN, 1.5, $maxRange + self::HITBOX_MARGIN);
		foreach($player->getWorld()->getNearbyEntities($bb, $player) as $entity){
			if($entity === $player || !$entity->isAlive()){
				continue;
			}
			$toEnt = $entity->getLocation()->subtractVector($eye);
			if($toEnt->lengthSquared() <= 0){
				continue;
			}
			$dot = $dir->dot($toEnt->normalize());
			if($dot < 0.2){
				continue;
			}
			$dist = $eye->distance($entity->getLocation());
			if(!$this->isWithinReach($dist, $maxRange)){
				continue;
			}

			$ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
			$entity->attack($ev);
			if($ev->isCancelled() || $ev->getFinalDamage() <= 0){
				continue;
			}

			try{
				$entity->addMotion($dir->x * 0.35, 0.1, $dir->z * 0.35);
			}catch(\Throwable $_){}
			$this->applyDamage(1);
			return true;
		}

		return false;
	}

	private function calculateDamage(int $chargeLevel, float $relativeSpeed, float $scale = 1.0): int{
		$base = $this->getAttackPoints();
		$base += (int) round($chargeLevel * 1.5);
		$base += (int) round(max(0.0, $relativeSpeed) * 2.0);

		return (int) max(1, ceil($base * $scale));
	}

	private function computeRelativeForwardSpeed(Player $player, Entity $entity, Vector3 $forward): float{
		$playerSpeed = max(0.0, $player->getMotion()->dot($forward));
		$entitySpeed = max(0.0, $entity->getMotion()->dot($forward));
		return max(0.0, $playerSpeed - $entitySpeed);
	}

	private function isWithinReach(float $distance, float $maxRange): bool{
		return $distance <= ($maxRange + self::HITBOX_MARGIN);
	}

	private function getMaxReach(Player $player): float{
		return $player->isCreative() ? self::CREATIVE_MAX_JAB_RANGE : self::MAX_JAB_RANGE;
	}

	private function maybeTriggerLungeMotion(Player $player, int $chargeLevel): void{
		$lungeLevel = $this->getEnchantmentLevel(Enchantments::LUNGE());
		if($lungeLevel <= 0 || $chargeLevel <= 0){
			return;
		}

		$dir = $player->getDirectionVector()->normalize();
		$horizontal = 0.4 + 0.15 * $lungeLevel;
		$vertical = 0.1 + 0.05 * $lungeLevel;
		$motion = new Vector3($dir->x * $horizontal, $vertical, $dir->z * $horizontal);
		$player->setMotion($player->getMotion()->add($motion));
		$player->preserveMotionNextTick();
	}

	private static function markHoldHit(Player $player, Entity $entity): void{
		self::$recentHoldHits[$player->getId()][$entity->getId()] = Server::getInstance()->getTick();
	}

	private static function clearRecentHoldHits(Player $player): void{
		unset(self::$recentHoldHits[$player->getId()]);
	}

	private static function wasRecentlyHit(Player $player, Entity $entity, int $cooldownTicks): bool{
		$pid = $player->getId();
		$eid = $entity->getId();
		if(!isset(self::$recentHoldHits[$pid][$eid])){
			return false;
		}

		$current = Server::getInstance()->getTick();
		if(($current - self::$recentHoldHits[$pid][$eid]) >= $cooldownTicks){
			unset(self::$recentHoldHits[$pid][$eid]);
			if(self::$recentHoldHits[$pid] === []){
				unset(self::$recentHoldHits[$pid]);
			}
			return false;
		}

		return true;
	}
}
