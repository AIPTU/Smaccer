<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class OcelotSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.7 : 0.7;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.6 : 0.6;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::OCELOT;
	}

	public function getName() : string {
		return 'Ocelot';
	}

	public function getBabyScale() : float {
		return 1;
	}
}
