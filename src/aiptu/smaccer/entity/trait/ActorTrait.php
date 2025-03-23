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

namespace aiptu\smaccer\entity\trait;

use aiptu\smaccer\entity\utils\ActorHandler;
use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

trait ActorTrait {
	protected int $actorId;

	public function initializeActor(CompoundTag $nbt) : void {
		ActorHandler::init();

		if ($nbt->getTag(EntityTag::ACTOR_ID) instanceof IntTag) {
			$this->actorId = $nbt->getInt(EntityTag::ACTOR_ID);
		} else {
			$this->actorId = ActorHandler::assignActorId($this);
		}

		ActorHandler::registerEntity($this);
	}

	public function getActorId() : int {
		return $this->actorId;
	}

	public function saveActor(CompoundTag $nbt) : void {
		$nbt->setInt(EntityTag::ACTOR_ID, $this->actorId);
	}

	public function removeActorId() : void {
		ActorHandler::removeActorId($this->actorId);
	}
}