<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class CatSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.56 : 0.7;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.48 : 0.6;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::CAT;
	}

	public function getName() : string {
		return 'Cat';
	}

	public function getBabyScale() : float {
		return 0.8;
	}
}
