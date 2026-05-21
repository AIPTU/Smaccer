<?php

/*
 * Copyright (c) 2024-2026 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc\placed;

use aiptu\smaccer\entity\EntitySmaccer;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class EnderCrystalSmaccer extends EntitySmaccer {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return 2;
	}

	public function getWidth() : float {
		return 2;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::ENDER_CRYSTAL;
	}

	public function getName() : string {
		return 'Ender Crystal';
	}

	public function getCategory() : string {
		return 'placed';
	}
}