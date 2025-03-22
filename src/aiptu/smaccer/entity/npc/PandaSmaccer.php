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

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class PandaSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.5 : 1.25;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.52 : 1.3;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::PANDA;
	}

	public function getName() : string {
		return 'Panda';
	}

	public function getBabyScale() : float {
		return 0.4;
	}
}