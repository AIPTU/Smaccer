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

use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\entity\Ageable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;

abstract class EntityAgeable extends EntitySmaccer implements Ageable {
	protected bool $baby = false;

	protected function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);

		$this->setBaby((bool) $nbt->getByte(EntityTag::BABY, 0));
	}

	public function saveNBT() : CompoundTag {
		$nbt = parent::saveNBT();

		$nbt->setByte(EntityTag::BABY, (int) $this->baby);
		return $nbt;
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void {
		parent::syncNetworkData($properties);
		$properties->setGenericFlag(EntityMetadataFlags::BABY, $this->isBaby());
	}

	public function isBaby() : bool {
		return $this->baby;
	}

	public function setBaby(bool $value = true) : void {
		$this->baby = $value;

		$this->setScale($value ? $this->getBabyScale() : 1.0);

		$this->networkPropertiesDirty = true;
	}

	public function getBabyScale() : float {
		return 0.5;
	}
}
