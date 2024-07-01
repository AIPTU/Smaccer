<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\entity\trait;

use aiptu\smaccer\entity\emote\EmoteType;
use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\event\NPCPerformActionEmoteEvent;
use aiptu\smaccer\event\NPCPerformEmoteEvent;
use aiptu\smaccer\Smaccer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\EmotePacket;
use function microtime;

trait EmoteTrait {
	protected ?EmoteType $actionEmote = null;
	protected ?EmoteType $emote = null;

	protected array $emoteCooldowns = [];
	protected array $actionEmoteCooldowns = [];

	private function initializeEmoteFromNBT(CompoundTag $nbt, string $tag) : ?EmoteType {
		return $nbt->getTag($tag) instanceof StringTag ? Smaccer::getInstance()->getEmoteManager()->getEmote($nbt->getString($tag)) : null;
	}

	private function saveEmoteToNBT(CompoundTag $nbt, ?EmoteType $emote, string $tag) : void {
		if ($emote !== null) {
			$nbt->setString($tag, $emote->getUuid());
		}
	}

	public function initializeEmote(CompoundTag $nbt) : void {
		$this->actionEmote = $this->initializeEmoteFromNBT($nbt, EntityTag::ACTION_EMOTE);
		$this->emote = $this->initializeEmoteFromNBT($nbt, EntityTag::EMOTE);
	}

	public function saveEmote(CompoundTag $nbt) : void {
		$this->saveEmoteToNBT($nbt, $this->actionEmote, EntityTag::ACTION_EMOTE);
		$this->saveEmoteToNBT($nbt, $this->emote, EntityTag::EMOTE);
	}

	public function setActionEmote(?EmoteType $actionEmote) : void {
		$this->actionEmote = $actionEmote;
		$this->saveNBT();
	}

	public function getActionEmote() : ?EmoteType {
		return $this->actionEmote;
	}

	public function setEmote(?EmoteType $emote) : void {
		$this->emote = $emote;
		$this->saveNBT();
	}

	public function getEmote() : ?EmoteType {
		return $this->emote;
	}

	public function canPerformEmote(string $emote) : bool {
		$currentTime = microtime(true);
		if (isset($this->emoteCooldowns[$emote]) && ($currentTime - $this->emoteCooldowns[$emote]) < Smaccer::getInstance()->getDefaultSettings()->getEmoteCooldownValue()) {
			return false;
		}

		$this->emoteCooldowns[$emote] = $currentTime;
		return true;
	}

	public function canPerformActionEmote(string $actionEmote) : bool {
		$currentTime = microtime(true);
		if (isset($this->actionEmoteCooldowns[$actionEmote]) && ($currentTime - $this->actionEmoteCooldowns[$actionEmote]) < Smaccer::getInstance()->getDefaultSettings()->getActionEmoteCooldownValue()) {
			return false;
		}

		$this->actionEmoteCooldowns[$actionEmote] = $currentTime;
		return true;
	}

	public function performEmote(string $emote, ?array $targets = null) : void {
		$emoteType = Smaccer::getInstance()->getEmoteManager()->getEmote($emote);
		if ($emoteType === null) {
			return;
		}

		$event = new NPCPerformEmoteEvent($this, $emoteType);
		$event->call();
		if ($event->isCancelled()) {
			return;
		}

		$this->broadcastEmote($event->getEmote()->getUuid(), $targets);
	}

	public function performActionEmote(string $actionEmote, ?array $targets = null) : void {
		$actionEmoteType = Smaccer::getInstance()->getEmoteManager()->getEmote($actionEmote);
		if ($actionEmoteType === null) {
			return;
		}

		$event = new NPCPerformActionEmoteEvent($this, $actionEmoteType);
		$event->call();
		if ($event->isCancelled()) {
			return;
		}

		$this->broadcastEmote($event->getActionEmote()->getUuid(), $targets);
	}

	private function broadcastEmote(string $emote, ?array $targets = null) : void {
		NetworkBroadcastUtils::broadcastPackets($targets ?? $this->getViewers(), [
			EmotePacket::create($this->getId(), $emote, '', '', EmotePacket::FLAG_MUTE_ANNOUNCEMENT),
		]);
	}
}
