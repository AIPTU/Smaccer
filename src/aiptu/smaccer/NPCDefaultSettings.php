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

namespace aiptu\smaccer;

use aiptu\smaccer\entity\utils\EntityVisibility;

class NPCDefaultSettings {
	public function __construct(
		private bool $commandCooldownEnabled,
		private float $commandCooldownValue,
		private bool $rotationEnabled,
		private float $maxDistance,
		private bool $nametagVisible,
		private EntityVisibility $entityVisibility,
		private bool $slapEnabled,
		private bool $emoteCooldownEnabled,
		private float $emoteCooldownValue,
		private bool $actionEmoteCooldownEnabled,
		private float $actionEmoteCooldownValue,
	) {}

	public function isCommandCooldownEnabled() : bool {
		return $this->commandCooldownEnabled;
	}

	public function getCommandCooldownValue() : float {
		return $this->commandCooldownValue;
	}

	public function isRotationEnabled() : bool {
		return $this->rotationEnabled;
	}

	public function getMaxDistance() : float {
		return $this->maxDistance;
	}

	public function isNametagVisible() : bool {
		return $this->nametagVisible;
	}

	public function getEntityVisibility() : EntityVisibility {
		return $this->entityVisibility;
	}

	public function isSlapEnabled() : bool {
		return $this->slapEnabled;
	}

	public function isEmoteCooldownEnabled() : bool {
		return $this->emoteCooldownEnabled;
	}

	public function getEmoteCooldownValue() : float {
		return $this->emoteCooldownValue;
	}

	public function isActionEmoteCooldownEnabled() : bool {
		return $this->actionEmoteCooldownEnabled;
	}

	public function getActionEmoteCooldownValue() : float {
		return $this->actionEmoteCooldownValue;
	}
}