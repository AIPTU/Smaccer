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

namespace aiptu\smaccer\event;

use aiptu\smaccer\entity\utils\EntityVisibility;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\entity\EntityEvent;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class NPCVisibilityChangeEvent extends EntityEvent implements Cancellable {
	use CancellableTrait;

	public function __construct(
		protected Entity                  $entity,
		private readonly EntityVisibility $oldVisibility,
		private EntityVisibility          $newVisibility
	) {}

	public function getOldVisibility() : EntityVisibility {
		return $this->oldVisibility;
	}

	public function getNewVisibility() : EntityVisibility {
		return $this->newVisibility;
	}

	public function setNewVisibility(EntityVisibility $newVisibility) : void {
		$this->newVisibility = $newVisibility;
	}
}
