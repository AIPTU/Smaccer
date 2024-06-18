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

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class HoglinSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.425 : 0.85;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.425 : 0.85;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::HOGLIN;
	}

	public function getName() : string {
		return 'Hoglin';
	}
}