<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class AxolotlSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.21 : 0.42;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.375 : 0.75;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::AXOLOTL;
	}

	public function getName() : string {
		return 'Axolotl';
	}
}
