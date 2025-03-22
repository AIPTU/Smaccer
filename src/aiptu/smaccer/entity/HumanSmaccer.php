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

use aiptu\smaccer\entity\trait\CommandTrait;
use aiptu\smaccer\entity\trait\CreatorTrait;
use aiptu\smaccer\entity\trait\EmoteTrait;
use aiptu\smaccer\entity\trait\InventoryTrait;
use aiptu\smaccer\entity\trait\NametagTrait;
use aiptu\smaccer\entity\trait\QueryTrait;
use aiptu\smaccer\entity\trait\RotationTrait;
use aiptu\smaccer\entity\trait\SkinTrait;
use aiptu\smaccer\entity\trait\SlapBackTrait;
use aiptu\smaccer\entity\trait\VisibilityTrait;
use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;

class HumanSmaccer extends Human {
	use CreatorTrait;
	use NametagTrait;
	use RotationTrait;
	use VisibilityTrait;
	use SlapBackTrait;
	use EmoteTrait;
	use CommandTrait;
	use InventoryTrait;
	use SkinTrait;
	use QueryTrait;

	public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
		if ($nbt instanceof CompoundTag) {
			$this->initializeCreator($nbt);
			$this->initializeCommand($nbt);
		}

		parent::__construct($location, $skin, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);

		$this->setScale($nbt->getFloat(EntityTag::SCALE, 1.0));
		$this->initializeRotation($nbt);
		$this->setNameTagAlwaysVisible((bool) $nbt->getByte(EntityTag::NAMETAG_VISIBLE, 1));
		$this->setNameTagVisible((bool) $nbt->getByte(EntityTag::NAMETAG_VISIBLE, 1));
		$this->initializeVisibility($nbt);
		$this->initializeSlapBack($nbt);
		$this->initializeEmote($nbt);
		$this->setHasGravity((bool) $nbt->getByte(EntityTag::GRAVITY, 1));
		$this->initializeQuery($nbt);
	}

	public function saveNBT() : CompoundTag {
		$nbt = parent::saveNBT();

		$this->saveCreator($nbt);
		$this->saveCommand($nbt);
		$nbt->setFloat(EntityTag::SCALE, $this->scale);
		$this->saveRotation($nbt);
		$nbt->setByte(EntityTag::NAMETAG_VISIBLE, (int) $this->isNameTagVisible());
		$this->saveVisibility($nbt);
		$this->saveEmote($nbt);
		$this->saveSlapBack($nbt);
		$nbt->setByte(EntityTag::GRAVITY, (int) $this->hasGravity());
		$this->saveQuery($nbt);

		return $nbt;
	}

	public function getName() : string {
		return 'Human';
	}

	public function setHasGravity(bool $v = true) : void {
		parent::setHasGravity($v);

		$this->networkPropertiesDirty = true;

		$this->setForceMovementUpdate();
	}
}
