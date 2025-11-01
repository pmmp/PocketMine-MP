<?php

declare(strict_types=1);

namespace pocketmine\command\interceptor;

use Closure;
use pocketmine\command\utils\Utils;
use pocketmine\event\HandlerListManager;
use pocketmine\event\RegisteredListener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use function assert;
use function count;
use function is_a;
use function spl_object_id;

final class PacketInterceptorListener implements IPacketInterceptor{

	private ?RegisteredListener $incoming_event_handler = null;
	private ?RegisteredListener $outgoing_event_handler = null;

	/** @var array<int, array<int, Closure(ServerboundPacket, NetworkSession) : bool>> */
	private array $incoming_handlers = [];

	/** @var array<int, array<int, Closure(ClientboundPacket, NetworkSession) : bool>> */
	private array $outgoing_handlers = [];

	public function __construct(
		readonly private Plugin $register,
		readonly private PacketPool $pool,
		readonly private int $priority,
		readonly private bool $handle_cancelled
	){}

	/**
	 * @template TPacket of Packet
	 * @template UPacket of TPacket
	 * @param Closure(UPacket, NetworkSession) : bool $handler
	 * @param class-string<TPacket> $class
	 * @return non-empty-list<int>
	 */
	private function parsePidsFromHandler(Closure $handler, string $class) : array{
		$classes = Utils::parseClosureSignature($handler, [$class, NetworkSession::class], "bool");
		return Utils::flattenPacketPidsFromGroups($this->pool, $classes[0]);
	}

	public function interceptIncoming(Closure $handler) : IPacketInterceptor{
		foreach($this->parsePidsFromHandler($handler, ServerboundPacket::class) as $pid){
			$this->incoming_handlers[$pid][spl_object_id($handler)] = $handler;
		}
		$this->incoming_event_handler ??= Server::getInstance()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event) : void{
			/** @var DataPacket&ServerboundPacket $packet */
			$packet = $event->getPacket();
			if(isset($this->incoming_handlers[$pid = $packet::NETWORK_ID])){
				$origin = $event->getOrigin();
				foreach($this->incoming_handlers[$pid] as $handler){
					if(!$handler($packet, $origin)){
						$event->cancel();
						break;
					}
				}
			}
		}, $this->priority, $this->register, $this->handle_cancelled);
		return $this;
	}

	public function interceptOutgoing(Closure $handler) : IPacketInterceptor{
		foreach($this->parsePidsFromHandler($handler, ClientboundPacket::class) as $pid){
			$this->outgoing_handlers[$pid][spl_object_id($handler)] = $handler;
		}
		$this->outgoing_event_handler ??= Server::getInstance()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event) : void{
			$original_targets = $event->getTargets();
			$packets = $event->getPackets();

			/** @var DataPacket&ClientboundPacket $packet */
			foreach($packets as $packet){
				if(isset($this->outgoing_handlers[$pid = $packet::NETWORK_ID])){
					$remaining_targets = $original_targets;

					foreach($remaining_targets as $i => $target){
						foreach($this->outgoing_handlers[$pid] as $handler){
							if(!$handler($packet, $target)){
								unset($remaining_targets[$i]);
								break;
							}
						}
					}

					$remaining_targets_c = count($remaining_targets);
					if($remaining_targets_c !== count($original_targets)){
						$event->cancel();
						if($remaining_targets_c > 0){
							$new_target_players = [];
							foreach($remaining_targets as $new_target){
								$new_target_player = $new_target->getPlayer();
								if($new_target_player !== null){
									$new_target_players[] = $new_target_player;
								}
							}

							NetworkBroadcastUtils::broadcastPackets($new_target_players, $packets);
						}
						break;
					}
				}
			}
		}, $this->priority, $this->register, $this->handle_cancelled);
		return $this;
	}

	public function unregisterIncomingInterceptor(Closure $handler) : IPacketInterceptor{
		$hid = spl_object_id($handler);
		foreach($this->parsePidsFromHandler($handler, ServerboundPacket::class) as $pid){
			if(isset($this->incoming_handlers[$pid][$hid])){
				unset($this->incoming_handlers[$pid][$hid]);
				if(count($this->incoming_handlers[$pid]) === 0){
					unset($this->incoming_handlers[$pid]);
					if(count($this->incoming_handlers) === 0){
						HandlerListManager::global()->getListFor(DataPacketReceiveEvent::class)->unregister($this->incoming_event_handler);
						$this->incoming_event_handler = null;
					}
				}
			}
		}
		return $this;
	}

	public function unregisterOutgoingInterceptor(Closure $handler) : IPacketInterceptor{
		$hid = spl_object_id($handler);
		foreach($this->parsePidsFromHandler($handler, ClientboundPacket::class) as $pid){
			if(isset($this->outgoing_handlers[$pid][$hid])){
				unset($this->outgoing_handlers[$pid][$hid]);
				if(count($this->outgoing_handlers[$pid]) === 0){
					unset($this->outgoing_handlers[$pid]);
					if(count($this->outgoing_handlers) === 0){
						HandlerListManager::global()->getListFor(DataPacketSendEvent::class)->unregister($this->outgoing_event_handler);
						$this->outgoing_event_handler = null;
					}
				}
			}
		}
		return $this;
	}
}