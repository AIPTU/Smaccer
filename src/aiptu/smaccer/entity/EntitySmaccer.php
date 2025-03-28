<?php

/*
 * Copyright (c) 2024-2025 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\trait\ActorTrait;
use aiptu\smaccer\entity\trait\CommandTrait;
use aiptu\smaccer\entity\trait\CreatorTrait;
use aiptu\smaccer\entity\trait\NametagTrait;
use aiptu\smaccer\entity\trait\QueryTrait;
use aiptu\smaccer\entity\trait\RotationTrait;
use aiptu\smaccer\entity\trait\VisibilityTrait;
use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

abstract class EntitySmaccer extends Entity {
	use ActorTrait;
	use CreatorTrait;
	use NametagTrait;
	use RotationTrait;
	use VisibilityTrait;
	use CommandTrait;
	use QueryTrait;

	public function __construct(Location $location, ?CompoundTag $nbt = null) {
		if ($nbt instanceof CompoundTag) {
			$this->initializeActor($nbt);
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
		$this->setHasGravity((bool) $nbt->getByte(EntityTag::GRAVITY, 1));
		$this->initializeQuery($nbt);
	}

	public function saveNBT() : CompoundTag {
		$nbt = parent::saveNBT();

		$this->saveActor($nbt);
		$this->saveCreator($nbt);
		$this->saveCommand($nbt);
		$nbt->setFloat(EntityTag::SCALE, $this->scale);
		$this->saveRotation($nbt);
		$nbt->setByte(EntityTag::NAMETAG_VISIBLE, (int) $this->isNameTagVisible());
		$this->saveVisibility($nbt);
		$nbt->setByte(EntityTag::GRAVITY, (int) $this->hasGravity());
		$this->saveQuery($nbt);

		return $nbt;
	}

	abstract protected function getInitialSizeInfo() : EntitySizeInfo;

	abstract public static function getNetworkTypeId() : string;

	abstract public function getName() : string;

	protected function getInitialDragMultiplier() : float {
		return 0.02;
	}

	protected function getInitialGravity() : float {
		return 0.08;
	}

	public function setHasGravity(bool $v = true) : void {
		parent::setHasGravity($v);

		$this->networkPropertiesDirty = true;

		$this->setForceMovementUpdate();
	}
}
