<?php

declare(strict_types=1);

namespace pocketmine\command\monitor;

use Closure;
use pocketmine\command\utils\Utils;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\event\RegisteredListener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use function spl_object_id;

final class PacketMonitorListener implements IPacketMonitor{

	private ?RegisteredListener $incoming_event_handler = null;
	private ?RegisteredListener $outgoing_event_handler = null;

	/** @var array<int, array<int, Closure(ServerboundPacket, NetworkSession) : void>> */
	private array $incoming_handlers = [];

	/** @var array<int, array<int, Closure(ClientboundPacket, NetworkSession) : void>> */
	private array $outgoing_handlers = [];

	public function __construct(
		readonly private Plugin $register,
		readonly private PacketPool $pool,
		readonly private bool $handle_cancelled
	){}

	/**
	 * @template TPacket of Packet
	 * @template UPacket of TPacket
	 * @param Closure(UPacket, NetworkSession) : void $handler
	 * @param class-string<TPacket> $class
	 * @return non-empty-list<int>
	 */
	private function parsePidsFromHandler(Closure $handler, string $class) : array{
		$classes = Utils::parseClosureSignature($handler, [$class, NetworkSession::class], "void");
		return Utils::flattenPacketPidsFromGroups($this->pool, $classes[0]);
	}

	public function monitorIncoming(Closure $handler) : IPacketMonitor{
		foreach($this->parsePidsFromHandler($handler, ServerboundPacket::class) as $pid){
			$this->incoming_handlers[$pid][spl_object_id($handler)] = $handler;
		}
		$this->incoming_event_handler ??= Server::getInstance()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event) : void{
			/** @var DataPacket&ServerboundPacket $packet */
			$packet = $event->getPacket();
			if(isset($this->incoming_handlers[$pid = $packet::NETWORK_ID])){
				$origin = $event->getOrigin();
				foreach($this->incoming_handlers[$pid] as $handler){
					$handler($packet, $origin);
				}
			}
		}, EventPriority::MONITOR, $this->register, $this->handle_cancelled);
		return $this;
	}

	public function monitorOutgoing(Closure $handler) : IPacketMonitor{
		foreach($this->parsePidsFromHandler($handler, ClientboundPacket::class) as $pid){
			$this->outgoing_handlers[$pid][spl_object_id($handler)] = $handler;
		}
		$this->outgoing_event_handler ??= Server::getInstance()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event) : void{
			/** @var DataPacket&ClientboundPacket $packet */
			foreach($event->getPackets() as $packet){
				if(isset($this->outgoing_handlers[$pid = $packet::NETWORK_ID])){
					foreach($event->getTargets() as $target){
						foreach($this->outgoing_handlers[$pid] as $handler){
							$handler($packet, $target);
						}
					}
				}
			}
		}, EventPriority::MONITOR, $this->register, $this->handle_cancelled);
		return $this;
	}

	public function unregisterIncomingMonitor(Closure $handler) : IPacketMonitor{
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

	public function unregisterOutgoingMonitor(Closure $handler) : IPacketMonitor{
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