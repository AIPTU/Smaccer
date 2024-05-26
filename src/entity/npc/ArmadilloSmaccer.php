<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class ArmadilloSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.39 : 0.65;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.42 : 0.7;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::ARMADILLO;
	}

	public function getName() : string {
		return 'Armadillo';
	}

	public function getBabyScale() : float {
		return 0.6;
	}
}
