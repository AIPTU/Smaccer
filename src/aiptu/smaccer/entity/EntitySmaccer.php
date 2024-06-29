<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\trait\CommandTrait;
use aiptu\smaccer\entity\trait\CreatorTrait;
use aiptu\smaccer\entity\trait\RotationTrait;
use aiptu\smaccer\entity\trait\VisibilityTrait;
use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

abstract class EntitySmaccer extends Entity {
	use CreatorTrait;
	use RotationTrait;
	use VisibilityTrait;
	use CommandTrait;

	public function __construct(Location $location, ?CompoundTag $nbt = null) {
		if ($nbt instanceof CompoundTag) {
			$this->initializeCreator($nbt);
			$this->initializeCommand($nbt);
		}

		parent::__construct($location, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);

		$this->setScale($nbt->getFloat(EntityTag::SCALE, 1.0));
		$this->initializeRotation($nbt);
		$this->setNameTagAlwaysVisible((bool) $nbt->getByte(EntityTag::NAMETAG_VISIBLE, 1));
		$this->setNameTagVisible((bool) $nbt->getByte(EntityTag::NAMETAG_VISIBLE, 1));
		$this->initializeVisibility($nbt);

		$this->setNoClientPredictions();
	}

	public function saveNBT() : CompoundTag {
		$nbt = parent::saveNBT();

		$this->saveCreator($nbt);
		$nbt->setFloat(EntityTag::SCALE, $this->scale);
		$this->saveRotation($nbt);
		$nbt->setByte(EntityTag::NAMETAG_VISIBLE, (int) $this->isNameTagVisible());
		$this->saveVisibility($nbt);
		$this->saveCommand($nbt);

		return $nbt;
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
