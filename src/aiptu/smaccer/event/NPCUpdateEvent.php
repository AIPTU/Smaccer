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

use aiptu\smaccer\entity\NPCData;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\entity\EntityEvent;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class NPCUpdateEvent extends EntityEvent implements Cancellable {
	use CancellableTrait;

	public function __construct(
		protected Entity $entity,
		protected NPCData $npcData
	) {}

	public function getNPCData() : NPCData {
		return $this->npcData;
	}

	public function setNPCData(NPCData $npcData) : void {
		$this->npcData = $npcData;
	}
}
