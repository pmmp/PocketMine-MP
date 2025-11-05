<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\command\args\Vector3Argument;
use pocketmine\command\args\EntityEnumArgument;
use pocketmine\command\args\RawStringArgument;
use pocketmine\command\args\TargetArgument;
use pocketmine\command\args\TextArgument;
use pocketmine\command\args\FloatArgument;
use pocketmine\command\CommandoCommand;
use pocketmine\entity\Axolotl;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\entity\LightningBolt;
use pocketmine\entity\Location;
use pocketmine\utils\Utils;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use pocketmine\entity\EntityType as PMEntityType;

class SummonCommand extends CommandoCommand{

    protected function prepare(): void {
        // Register overloads similar to /tp so multiple permutations are accepted.
        // entityType is always first
        $this->registerArgument(0, new EntityEnumArgument("entity", false));

    // Overload A/B: try spawnPos first (span=3) so it doesn't get swallowed by a single-token nameTag
    // Overload B: /summon <entityType> [spawnPos: x y z] [yRot] [xRot] [spawnEvent] [nameTag]
    $this->registerArgument(1, new Vector3Argument("spawnPos", true));

    // Overload A: /summon <entityType> <nameTag> [spawnPos]
    // Register nameTag after spawnPos so multi-token position isn't captured as a name
    $this->registerArgument(1, new \pocketmine\command\args\RawStringArgument("nameTag", true));
        $this->registerArgument(2, new FloatArgument("yRot", true));
        $this->registerArgument(3, new FloatArgument("xRot", true));
        $this->registerArgument(4, new RawStringArgument("spawnEvent", true));
        $this->registerArgument(5, new TextArgument("nameTag2", true));

    // Overload C: /summon <entity> [spawnPos] facing <lookAtEntity: target> [spawnEvent] [nameTag]
    // We register a literal 'facing' and a TargetArgument for the lookAtEntity.
    $this->registerArgument(2, new \pocketmine\command\args\LiteralArgument("facing", 'facing', true));
    $this->registerArgument(3, new TargetArgument("lookAtEntity", true));

        // Overload D: /summon <entity> [spawnPos] facing <lookAtPosition: x y z> [spawnEvent] [nameTag]
        // Register a Vector3 for the lookAt position at the same index so the framework can accept either.
        $this->registerArgument(3, new Vector3Argument("lookAtPos", true));
    }

    public function __construct(){
        parent::__construct("summon", "Summons an entity to the world",
            "/summon <entityType: EntityType> <nameTag: string> [spawnPos: x y z]\n" .
            "/summon <entityType: EntityType> [spawnPos: x y z] [yRot: value] [xRot: value] [spawnEvent: string] [nameTag: string]\n" .
            "/summon <entityType: EntityType> [spawnPos: x y z] facing <lookAtEntity: target> [spawnEvent: string] [nameTag: string]\n" .
            "/summon <entityType: EntityType> [spawnPos: x y z] facing <lookAtPosition: x y z> [spawnEvent: string] [nameTag: string]"
        );
        $this->setPermission("beeltymine.command.summon");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }

        // Normalize args - handle arrays from overlapping argument names
        foreach ($args as $key => $value) {
            if (is_array($value)) {
                $args[$key] = $value[0] ?? null;
            }
        }

        $entityArg = $args['entity'] ?? '';
        $saveName = null;
        $className = null;
        if ($entityArg instanceof PMEntityType) {
            $saveName = $entityArg->getSaveName();
            $className = $entityArg->getClassName();
        } else {
            $saveName = strtolower((string)$entityArg);
        }

        // determine position and world
        if ($sender instanceof Player) {
            $world = $sender->getWorld();
            $pos = $sender->getPosition()->asVector3();
        } else {
            $world = $sender->getServer()->getWorldManager()->getDefaultWorld();
            $pos = $world->getSpawnLocation()->asVector3();
        }

        $yRot = null;
        $xRot = null;
        $spawnEvent = null;
        $nameTag = null;

        // Overload A: immediate nameTag
        if (isset($args['nameTag'])) {
            $nameTag = $args['nameTag'];
            if (isset($args['spawnPos']) && $args['spawnPos'] instanceof Vector3) {
                $pos = $args['spawnPos'];
            }
        } else {
            // Overload B/C/D: spawnPos etc
            if (isset($args['spawnPos']) && $args['spawnPos'] instanceof Vector3) {
                $pos = $args['spawnPos'];
            }

            if (isset($args['yRot'])) {
                $yRot = (float)$args['yRot'];
            }
            if (isset($args['xRot'])) {
                $xRot = (float)$args['xRot'];
            }

            if (isset($args['spawnEvent'])) {
                $spawnEvent = $args['spawnEvent'];
            }

            if (isset($args['nameTag2'])) {
                $nameTag = $args['nameTag2'];
            }
        }

            // Check for 'facing' token in the remaining string args (spawnEvent/nameTag fields)
            $facingTarget = null;
            $lookAtPos = null;
            $rawTokens = [];
            foreach (['spawnEvent', 'nameTag2', 'nameTag'] as $k) {
                if (isset($args[$k]) && is_string($args[$k])) {
                    $parts = preg_split('/\s+/', trim($args[$k]));
                    foreach ($parts as $p) {
                        if ($p !== '') $rawTokens[] = $p;
                    }
                }
            }

            $facingIndex = array_search('facing', array_map('strtolower', $rawTokens), true);
            if ($facingIndex !== false) {
                // tokens after 'facing' may be a selector (@p, @s, etc.) or 3 coordinates
                $after = array_slice($rawTokens, $facingIndex + 1);
                if (count($after) > 0) {
                    $first = $after[0];
                    if ($first !== null && strlen($first) > 0 && $first[0] === '@') {
                        $facingTarget = $this->resolveSelector($first, $sender, $world);
                    } elseif (count($after) >= 3 && ($after[0] === '~' || is_numeric($after[0]) || (strlen($after[0])>0 && $after[0][0] === '~'))) {
                        // parse as relative/absolute coords
                        $tx = $after[0];
                        $ty = $after[1];
                        $tz = $after[2];
                        $lookAtPos = $this->parseRelativePosition($pos, [$tx, $ty, $tz], $world);
                    } else {
                        // try resolve as player name
                        $facingTarget = $this->resolveSelector($first, $sender, $world);
                    }
                }
            }
        // instantiate entity
        $entity = null;
        if ($className !== null && class_exists($className)) {
            // Use the concrete class if available
            $yaw = $yRot ?? Utils::getRandomFloat() * 360;
            $pitch = $xRot ?? 0;
            $entity = new $className(Location::fromObject($pos, $world, $yaw, $pitch));
        } else {
            // Fallback to known switch mapping for some common entities
            switch ($saveName) {
                case 'axolotl':
                case 'minecraft:axolotl':
                    $entity = new Axolotl(Location::fromObject($pos, $world, $yRot ?? Utils::getRandomFloat() * 360, $xRot ?? 0));
                    break;
                case 'squid':
                case 'minecraft:squid':
                    $entity = new Squid(Location::fromObject($pos, $world, $yRot ?? Utils::getRandomFloat() * 360, $xRot ?? 0));
                    break;
                case 'villager':
                case 'minecraft:villager':
                    $entity = new Villager(Location::fromObject($pos, $world, $yRot ?? Utils::getRandomFloat() * 360, $xRot ?? 0));
                    break;
                case 'zombie':
                case 'minecraft:zombie':
                    $entity = new Zombie(Location::fromObject($pos, $world, $yRot ?? Utils::getRandomFloat() * 360, $xRot ?? 0));
                    break;
                case 'lightning':
                case 'lightning_bolt':
                case 'minecraft:lightning_bolt':
                    $entity = new LightningBolt(Location::fromObject($pos, $world, 0, 0));
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . "Unknown entity: " . $saveName);
                    return;
            }
        }

        if ($entity === null) {
            $sender->sendMessage(TextFormat::RED . "Failed to summon entity: " . $saveName);
            return;
        }

        // apply spawnEvent semantics
        if ($spawnEvent !== null) {
            $se = strtolower($spawnEvent);
            if ($se === 'minecraft:entity_born' || $se === 'entity_born') {
                if ($entity instanceof Axolotl) {
                    $entity->setBaby(true);
                }
            }
        }

        // apply nameTag if present
        if ($nameTag !== null && $nameTag !== '') {
            $entity->setNameTag($nameTag);
            $entity->setNameTagVisible(true);
        }

        // apply facing/lookAt if requested
        if ($facingTarget !== null) {
            $entity->lookAt($facingTarget->getLocation());
        } elseif ($lookAtPos !== null) {
            $entity->lookAt($lookAtPos);
        }

        $entity->spawnToAll();
        $sender->sendMessage("Spawned entity: " . $saveName);
    }

    /**
     * Parse relative coordinates like '~', '~1', '-5', etc. Returns a Vector3 absolute position.
     */
    private function parseRelativePosition(Vector3 $base, array $tokens, World $world): Vector3 {
        [$tx, $ty, $tz] = $tokens;
        $parse = fn($t, $baseVal) => ($t === '~' ? $baseVal : (strlen((string)$t) > 0 && $t[0] === '~' ? $baseVal + (float)substr($t, 1) : (float)$t));
        return new Vector3($parse($tx, $base->x), $parse($ty, $base->y), $parse($tz, $base->z));
    }

    /**
     * Resolve a simple selector or player name to an Entity (player preferred). Supports @s, @p, @r, @a, @e or player name.
     */
    private function resolveSelector(string $token, CommandSender $sender, World $world): ?\pocketmine\entity\Entity {
        $server = $sender->getServer();
        // Enhanced selector parsing: support @s, @p, @r, @a, and @e with simple filters like [type=...,name=...,distance=..,limit=...]
        if ($token === '@s') {
            return $sender instanceof \pocketmine\entity\Entity ? $sender : null;
        }
        if ($token === '@p') {
            if ($sender instanceof Player) {
                // nearest player to sender
                $nearest = null;
                $bestDist = PHP_INT_MAX;
                foreach ($server->getOnlinePlayers() as $p) {
                    if ($p->getWorld() !== $world) continue;
                    $d = $p->getPosition()->distanceSquared($sender->getPosition());
                    if ($d < $bestDist) { $bestDist = $d; $nearest = $p; }
                }
                return $nearest;
            }
            return null;
        }
        if ($token === '@r') {
            $players = array_values(array_filter($server->getOnlinePlayers(), fn($p) => $p->getWorld() === $world));
            if (count($players) === 0) return null;
            return $players[array_rand($players)];
        }
        if (str_starts_with($token, '@e')) {
            // parse optional filters inside brackets
            $filters = [];
            if (preg_match('/@e\[(.*)\]/', $token, $m)) {
                $inside = $m[1];
                $pairs = explode(',', $inside);
                foreach ($pairs as $pair) {
                    $kv = explode('=', $pair, 2);
                    if (count($kv) === 2) {
                        $filters[strtolower(trim($kv[0]))] = trim($kv[1]);
                    }
                }
            }
            $maxDistance = null;
            if (isset($filters['distance'])) {
                $maxDistance = (float)$filters['distance'];
            }
            $typeFilter = $filters['type'] ?? null;
            $nameFilter = $filters['name'] ?? null;
            $limit = isset($filters['limit']) ? (int)$filters['limit'] : 1;

            $results = [];
            foreach ($world->getEntities() as $ent) {
                if ($typeFilter !== null) {
                    $tf = strtolower($typeFilter);
                    $match = false;
                    if (strtolower($ent::getNetworkTypeId()) === $tf || strtolower((new \ReflectionClass($ent))->getShortName()) === $tf || strtolower(get_class($ent)) === $tf) {
                        $match = true;
                    }
                    if (!$match) continue;
                }
                if ($nameFilter !== null) {
                    $entName = $ent instanceof \pocketmine\player\Player ? $ent->getName() : $ent->getNameTag();
                    if (strtolower($entName) !== strtolower($nameFilter) && strtolower($ent->getNameTag()) !== strtolower($nameFilter)) continue;
                }
                if ($maxDistance !== null && $sender instanceof \pocketmine\entity\Entity) {
                    $d = $ent->getPosition()->distance($sender->getPosition());
                    if ($d > $maxDistance) continue;
                }
                $results[] = $ent;
                if (count($results) >= $limit) break;
            }

            return $results[0] ?? null;
        }
        if ($token === '@a') {
            $players = $server->getOnlinePlayers();
            return count($players) > 0 ? reset($players) : null;
        }
        // else try player by prefix
        return $server->getPlayerByPrefix($token);
    }
}
