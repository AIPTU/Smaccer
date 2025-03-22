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

class TurtleSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.032 : 0.2;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.096 : 0.6;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::TURTLE;
	}

	public function getName() : string {
		return 'Turtle';
	}

	public function getBabyScale() : float {
		return 0.16;
	}
}