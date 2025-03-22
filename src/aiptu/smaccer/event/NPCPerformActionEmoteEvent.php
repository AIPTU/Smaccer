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

use aiptu\smaccer\entity\emote\EmoteType;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\entity\EntityEvent;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class NPCPerformActionEmoteEvent extends EntityEvent implements Cancellable {
	use CancellableTrait;

	public function __construct(
		protected Entity $entity,
		private EmoteType $actionEmote
	) {}

	public function getActionEmote() : EmoteType {
		return $this->actionEmote;
	}

	public function setActionEmote(EmoteType $actionEmote) : void {
		$this->actionEmote = $actionEmote;
	}
}