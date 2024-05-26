<?php

declare(strict_types=1);

namespace aiptu\smaccer;

use aiptu\smaccer\entity\utils\EntityVisibility;

class NPCDefaultSettings {
	public function __construct(
		private bool $cooldownEnabled,
		private float $cooldownValue,
		private bool $rotationEnabled,
		private float $maxDistance,
		private bool $nametagVisible,
		private EntityVisibility $entityVisibility,
		private bool $slapEnabled
	) {}

	public function isCooldownEnabled() : bool {
		return $this->cooldownEnabled;
	}

	public function getCooldownValue() : float {
		return $this->cooldownValue;
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
}
