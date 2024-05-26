<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\utils\EntityVisibility;

readonly class NPCData {
	public function __construct(
		private int $id,
		private string $creator,
		private string $nameTag,
		private float $scale,
		private EntityVisibility $visibility
	) {}

	public function getId() : int {
		return $this->id;
	}

	public function getCreator() : string {
		return $this->creator;
	}

	public function getNameTag() : string {
		return $this->nameTag;
	}

	public function getScale() : float {
		return $this->scale;
	}

	public function getVisibility() : EntityVisibility {
		return $this->visibility;
	}
}
