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

use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;

trait CreatorTrait {
	protected string $creator;

	public function initializeCreator(CompoundTag $nbt) : void {
		$this->creator = $nbt->getString(EntityTag::CREATOR);
	}

	public function saveCreator(CompoundTag $nbt) : void {
		$nbt->setString(EntityTag::CREATOR, $this->creator);
	}

	public function getCreatorId() : string {
		return $this->creator;
	}

	public function getCreator() : ?Player {
		return Server::getInstance()->getPlayerByRawUUID($this->creator);
	}

	public function isOwnedBy(Player $player) : bool {
		return $player->getUniqueId()->getBytes() === $this->creator;
	}
}
