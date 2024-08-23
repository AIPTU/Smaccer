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

use aiptu\smaccer\event\NPCNameTagChangeEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_keys;
use function array_values;
use function str_replace;

trait NametagTrait {
	public function setNameTag(string $nameTag) : void {
		$oldNameTag = $this->getNameTag();
		$event = new NPCNameTagChangeEvent($this, $oldNameTag, $nameTag);
		$event->call();

		if (!$event->isCancelled()) {
			parent::setNameTag($event->getNewNameTag());
		}
	}

	/**
	 * @param array<Player>|null      $targets
	 * @param array<MetadataProperty> $data    Properly formatted entity data, defaults to everything
	 *
	 * @phpstan-param array<int, MetadataProperty> $data
	 */
	public function sendData(?array $targets, ?array $data = null) : void {
		parent::sendData($targets, $data);

		foreach ($this->hasSpawned as $player) {
			$nametag = $this->applyNametag(null, $player);
			$data[EntityMetadataProperties::NAMETAG] = new StringMetadataProperty($nametag);
			$networkSession = $player->getNetworkSession();
			$networkSession->getEntityEventBroadcaster()->syncActorData([$networkSession], $this, $data);
		}
	}

	public function applyNametag(?string $nametag, Player $player) : string {
		$nametag ??= $this->getNameTag();

		$vars = [
			'{player}' => $player->getName(),
			'{display_name}' => $player->getDisplayName(),
			'{line}' => "\n",
		];

		return TextFormat::colorize(str_replace(array_keys($vars), array_values($vars), $nametag));
	}
}
