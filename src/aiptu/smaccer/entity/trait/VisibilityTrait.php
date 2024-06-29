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

namespace aiptu\smaccer\entity\trait;

use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\entity\utils\EntityVisibility;
use pocketmine\nbt\tag\CompoundTag;

trait VisibilityTrait {
	protected EntityVisibility $visibility = EntityVisibility::VISIBLE_TO_EVERYONE;

	public function initializeVisibility(CompoundTag $nbt) : void {
		$this->setVisibility(EntityVisibility::fromInt($nbt->getInt(EntityTag::VISIBILITY, EntityVisibility::VISIBLE_TO_EVERYONE->value)));
	}

	public function saveVisibility(CompoundTag $nbt) : void {
		$nbt->setInt(EntityTag::VISIBILITY, $this->visibility->value);
	}

	public function getVisibility() : EntityVisibility {
		return $this->visibility;
	}

	public function setVisibility(EntityVisibility $visibility) : void {
		$this->visibility = $visibility;

		switch ($visibility) {
			case EntityVisibility::VISIBLE_TO_EVERYONE:
				$this->spawnToAll();
				break;
			case EntityVisibility::VISIBLE_TO_CREATOR:
				$creator = $this->getCreator();
				if ($creator !== null) {
					$this->despawnFromAll();
					$this->spawnTo($creator);
				}

				break;
			case EntityVisibility::INVISIBLE_TO_EVERYONE:
				$this->despawnFromAll();
				break;
		}
	}
}
