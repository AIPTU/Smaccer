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

namespace aiptu\smaccer\entity\npc\hostile;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class ZombieHorseSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 1.12 : 2.24;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.98 : 1.96;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::ZOMBIE_HORSE;
	}

	public function getName() : string {
		return 'Zombie Horse';
	}

	public function getCategory() : string {
		return 'hostile';
	}
}