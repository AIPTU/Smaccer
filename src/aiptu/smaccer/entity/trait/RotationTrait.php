<?php

/*
 * Copyright (c) 2024-2025 AIPTU
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

trait RotationTrait {
	protected bool $rotateToPlayers = true;

	public function initializeRotation(CompoundTag $nbt) : void {
		$this->setRotateToPlayers((bool) $nbt->getByte(EntityTag::ROTATE_TO_PLAYERS, 1));
	}

	public function saveRotation(CompoundTag $nbt) : void {
		$nbt->setByte(EntityTag::ROTATE_TO_PLAYERS, (int) $this->rotateToPlayers);
	}

	public function setRotateToPlayers(bool $value = true) : void {
		$this->rotateToPlayers = $value;
	}

	public function canRotateToPlayers() : bool {
		return $this->rotateToPlayers;
	}
}
