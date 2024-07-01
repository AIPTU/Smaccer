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
use aiptu\smaccer\event\NPCSlapBackActionEvent;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\nbt\tag\CompoundTag;

trait SlapBackTrait {
	protected bool $slapBack = true;

	public function initializeSlapBack(CompoundTag $nbt) : void {
		$this->setSlapBack((bool) $nbt->getByte(EntityTag::SLAP_BACK, 1));
	}

	public function saveSlapBack(CompoundTag $nbt) : void {
		$nbt->setByte(EntityTag::SLAP_BACK, (int) $this->slapBack);
	}

	public function setSlapBack(bool $value = true) : void {
		$this->slapBack = $value;
	}

	public function canSlapBack() : bool {
		return $this->slapBack;
	}

	public function slapBack() : void {
		$event = new NPCSlapBackActionEvent($this, $this->slapBack);
		$event->call();
		if ($event->isCancelled()) {
			return;
		}

		$slapBack = $event->canSlapBack();
		if ($slapBack) {
			$this->broadcastAnimation(new ArmSwingAnimation($this));
		}
	}
}