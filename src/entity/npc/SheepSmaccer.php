<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class SheepSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.65 : 1.3;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.45 : 0.9;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::SHEEP;
	}

	public function getName() : string {
		return 'Sheep';
	}
}
