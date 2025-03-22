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

namespace aiptu\smaccer\event;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class NPCInteractEvent extends PlayerEvent implements Cancellable {
	use CancellableTrait;

	public function __construct(
		protected Player $player,
		private Entity $entity,
	) {}

	public function getEntity() : Entity {
		return $this->entity;
	}
}