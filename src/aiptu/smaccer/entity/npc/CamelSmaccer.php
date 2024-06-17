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

class CamelSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 1.06875 : 2.375;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.765 : 1.7;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::CAMEL;
	}

	public function getName() : string {
		return 'Camel';
	}

	public function getBabyScale() : float {
		return 0.45;
	}
}
