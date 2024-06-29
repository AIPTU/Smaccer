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

	public function initializeEmote(CompoundTag $nbt) : void {
		$this->actionEmote = $nbt->getTag(EntityTag::ACTION_EMOTE) instanceof StringTag ? Smaccer::getInstance()->getEmoteManager()->getEmote($nbt->getString(EntityTag::ACTION_EMOTE)) : null;
		$this->emote = $nbt->getTag(EntityTag::EMOTE) instanceof StringTag ? Smaccer::getInstance()->getEmoteManager()->getEmote($nbt->getString(EntityTag::EMOTE)) : null;
	}

	public function saveEmote(CompoundTag $nbt) : void {
		if ($this->actionEmote !== null) {
			$nbt->setString(EntityTag::ACTION_EMOTE, $this->actionEmote->getUuid());
		}

		if ($this->emote !== null) {
			$nbt->setString(EntityTag::EMOTE, $this->emote->getUuid());
		}
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

	public function handleEmoteCooldown(string $emote) : bool {
		$currentTime = microtime(true);
		if (isset($this->emoteCooldowns[$emote]) && ($currentTime - $this->emoteCooldowns[$emote]) < Smaccer::getInstance()->getDefaultSettings()->getEmoteCooldownValue()) {
			return false;
		}

		$this->emoteCooldowns[$emote] = $currentTime;
		return true;
	}

	public function handleActionEmoteCooldown(string $actionEmote) : bool {
		$currentTime = microtime(true);
		if (isset($this->actionEmoteCooldowns[$actionEmote]) && ($currentTime - $this->actionEmoteCooldowns[$actionEmote]) < Smaccer::getInstance()->getDefaultSettings()->getActionEmoteCooldownValue()) {
			return false;
		}

		$this->actionEmoteCooldowns[$actionEmote] = $currentTime;
		return true;
	}

	public function broadcastEmote(string $emote, ?array $targets = null) : void {
		NetworkBroadcastUtils::broadcastPackets($targets ?? $this->getViewers(), [
			EmotePacket::create($this->getId(), $emote, '', '', EmotePacket::FLAG_MUTE_ANNOUNCEMENT),
		]);
	}
}
