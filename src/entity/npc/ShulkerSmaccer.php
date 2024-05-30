<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\npc;

use aiptu\smaccer\entity\EntityAgeable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class ShulkerSmaccer extends EntityAgeable {
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo($this->getHeight(), $this->getWidth());
	}

	public function getHeight() : float {
		return 1;
	}

	public function getWidth() : float {
		return 1;
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::SHULKER;
	}

	public function getName() : string {
		return 'Shulker';
	}
}
