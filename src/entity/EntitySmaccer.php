<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\trait\SmaccerTrait;
use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

abstract class EntitySmaccer extends Entity {
	use SmaccerTrait;

	public function __construct(Location $location, ?CompoundTag $nbt = null) {
		if ($nbt instanceof CompoundTag) {
			$this->creator = $nbt->getString(EntityTag::CREATOR);
		}

		parent::__construct($location, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);

		$this->setNoClientPredictions();
	}

	abstract protected function getInitialSizeInfo() : EntitySizeInfo;

	abstract public static function getNetworkTypeId() : string;

	abstract public function getName() : string;

	protected function getInitialDragMultiplier() : float {
		return 0.00;
	}

	protected function getInitialGravity() : float {
		return 0.00;
	}
}
