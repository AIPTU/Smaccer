<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_e7a2ed4aa25b1b85\muqsit\simplepackethandler\interceptor;

use Closure;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

interface IPacketInterceptor{

	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function interceptIncoming(Closure $handler) : IPacketInterceptor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function interceptOutgoing(Closure $handler) : IPacketInterceptor;

	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function unregisterIncomingInterceptor(Closure $handler) : IPacketInterceptor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function unregisterOutgoingInterceptor(Closure $handler) : IPacketInterceptor;
}