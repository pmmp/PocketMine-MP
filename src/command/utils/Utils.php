<?php

declare(strict_types=1);

namespace pocketmine\command\utils;

use Closure;
use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\utils\Utils as PocketMineUtils;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use function assert;
use function count;
use function implode;
use function in_array;
use function is_a;
use function is_int;

final class Utils{

	/**
	 * Parses a closure and validates it against $params and $return_type. Returns a type group list for
	 * each parameter.
	 * fn(A1, B1&B2) : bool, [ABase::class, BBase::class], "bool" = [[[A1]], [[B1, B2]]]
	 *
	 * @param Closure $closure
	 * @param list<string> $params
	 * @param string $return_type
	 * @return list<non-empty-list<non-empty-list<string>>>
	 */
	public static function parseClosureSignature(Closure $closure, array $params, string $return_type) : array{
		/** @noinspection PhpUnhandledExceptionInspection */
		$method = new ReflectionFunction($closure);
		$type = $method->getReturnType();
		if(!($type instanceof ReflectionNamedType) || $type->allowsNull() || $type->getName() !== $return_type){
			throw new InvalidArgumentException("Return value of " . PocketMineUtils::getNiceClosureName($closure) . " must be {$return_type}");
		}

		$parsed_params = [];
		$parameters = $method->getParameters();
		count($parameters) === count($params) || throw new InvalidArgumentException("Method " . PocketMineUtils::getNiceClosureName($closure) . " must accept exactly " . count($params) . " parameters");

		$parameter_index = 0;
		foreach($parameters as $index => $parameter){
			$parameter_type = $parameter->getType();
			$parameter_compare = $params[$parameter_index++];
			$parameter_type !== null || throw new InvalidArgumentException("Argument " . ($index + 1) . " of " . PocketMineUtils::getNiceClosureName($closure) . " must accept a value of type {$parameter_compare}");
			!$parameter_type->allowsNull() || throw new InvalidArgumentException("Argument " . ($index + 1) . " of " . PocketMineUtils::getNiceClosureName($closure) . " must not be nullable");
			$names = self::groupTypes($parameter_type);
			foreach($names as $group){
				foreach($group as $name){
					is_a($name, $parameter_compare, true) || throw new InvalidArgumentException("Parameter {$name} in " . PocketMineUtils::getNiceClosureName($closure) . " does not satisfy type {$parameter_compare}");
				}
			}
			$parsed_params[] = $names;
		}

		count($parsed_params) === count($params) || throw new InvalidArgumentException(PocketMineUtils::getNiceClosureName($closure) . " must satisfy signature (" . implode(", ", $params) . ") : {$return_type}");
		return $parsed_params;
	}

	/**
	 * Groups composite type names based on the kind of join.
	 * A = [[A]]
	 * A|B = [[A], [B]]
	 * (A*C)|(B*C)|D = [[A, C], [B, C], [D]]
	 *
	 * @param ReflectionType $type
	 * @return non-empty-list<non-empty-list<string>>
	 */
	public static function groupTypes(ReflectionType $type) : array{
		$result = [];
		$stack = [[$type, 0]];
		$offset = 0;
		while(isset($stack[$offset])){
			[$type, $index] = $stack[$offset++];
			if($type instanceof ReflectionNamedType){ // perform the frequent case first
				$result[$index][] = $type->getName();
			}elseif($type instanceof ReflectionUnionType){
				foreach($type->getTypes() as $child){
					$index = count($result);
					$result[$index] = [];
					$stack[] = [$child, $index];
				}
			}elseif($type instanceof ReflectionIntersectionType){
				$result[$index] = [];
				foreach($type->getTypes() as $child){
					$stack[] = [$child, $index];
				}
			}else{
				throw new InvalidArgumentException("Don't know how to resolve " . $type . "(" . $type::class . ")");
			}
		}
		/** @var non-empty-list<non-empty-list<string>> $result */
		return $result;
	}

	/**
	 * @param PacketPool $pool
	 * @param non-empty-list<non-empty-list<string>> $groups
	 * @return non-empty-list<int>
	 */
	public static function flattenPacketPidsFromGroups(PacketPool $pool, array $groups) : array{
		$network_ids = [];
		foreach($groups as $group){
			if(count($group) === 1){ // perform the frequent case first
				// union and non-composite types can be derived directly
				$packet = $group[0];
				assert(is_a($packet, DataPacket::class, true));
				assert(is_int($packet::NETWORK_ID));
				$network_ids[] = $packet::NETWORK_ID;
			}else{
				// intersection types (A&B) are more complex - they require a pool lookup
				static $packets = null;
				$packets ??= (new ReflectionProperty($pool, "pool"))->getValue($pool);
				$found = 0;
				/**
				 * @var int $pid
				 * @var DataPacket $packet
				 */
				foreach($packets as $pid => $packet){
					foreach($group as $validate){
						if(!is_a($validate, $packet::class, true)){
							continue 2;
						}
					}
					if(in_array($pid, $network_ids, true)){
						continue;
					}
					$network_ids[] = $pid;
					$found++;
				}
				$found > 0 || throw new InvalidArgumentException("No packets match criteria " . implode("&", $group));
			}
		}
		/** @var non-empty-list<int> $network_ids */
		return $network_ids;
	}
}