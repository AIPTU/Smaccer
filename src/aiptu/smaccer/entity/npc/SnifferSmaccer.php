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

class SnifferSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.7875 : 1.75;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.855 : 1.9;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::SNIFFER;
	}

	public function getName() : string {
		return 'Sniffer';
	}

	public function getBabyScale() : float {
		return 0.45;
	}
}
