<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class GlowSquidSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return $this->isBaby() ? 0.475 : 0.95;
	}

	public function getWidth() : float {
		return $this->isBaby() ? 0.475 : 0.95;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::GLOW_SQUID;
	}

	public function getName() : string {
		return 'Glow Squid';
	}
}
