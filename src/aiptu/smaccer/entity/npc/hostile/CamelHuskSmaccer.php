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

use aiptu\smaccer\entity\EntitySmaccer;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class CamelHuskSmaccer extends EntitySmaccer {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return 2.375;
	}

	public function getWidth() : float {
		return 1.7;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::CAMEL_HUSK;
	}

	public function getName() : string {
		return 'Camel Husk';
	}

	public function getCategory() : string {
		return 'hostile';
	}
}