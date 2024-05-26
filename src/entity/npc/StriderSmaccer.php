<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class StriderSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.85 : 1.7;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.45 : 0.9;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::STRIDER;
	}

	public function getName() : string {
		return 'Strider';
	}
}
