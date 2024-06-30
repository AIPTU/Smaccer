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

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityEvent;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class NPCDespawnEvent extends EntityEvent {
	public function __construct(protected Entity $entity) {}
}
