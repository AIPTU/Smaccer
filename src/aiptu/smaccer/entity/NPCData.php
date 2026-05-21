<?php

/*
 * Copyright (c) 2024-2026 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\emote\EmoteType;
use aiptu\smaccer\entity\utils\EntityVisibility;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;

class NPCData {
	public function __construct(
		private string $type,
		private ?Location $location = null,
		private ?Vector3 $motion = null,
		private ?string $nameTag = null,
		private float $scale = 1.0,
		private bool $isBaby = false,
		private bool $rotationEnabled = true,
		private bool $nametagVisible = true,
		private EntityVisibility $visibility = EntityVisibility::VISIBLE_TO_EVERYONE,
		private bool $slapBack = false,
		private ?EmoteType $actionEmote = null,
		private ?EmoteType $emote = null,
		private bool $gravityEnabled = true,
	) {}

	public static function create(string $type) : self {
		return new self($type);
	}

	public function getType() : string {
		return $this->type;
	}

	public function getLocation() : ?Location {
		return $this->location;
	}

	public function getMotion() : ?Vector3 {
		return $this->motion;
	}

	public function getNameTag() : ?string {
		return $this->nameTag;
	}

	public function getScale() : float {
		return $this->scale;
	}

	public function isBaby() : bool {
		return $this->isBaby;
	}

	public function isRotationEnabled() : bool {
		return $this->rotationEnabled;
	}

	public function isNametagVisible() : bool {
		return $this->nametagVisible;
	}

	public function getVisibility() : EntityVisibility {
		return $this->visibility;
	}

	public function getSlapBack() : bool {
		return $this->slapBack;
	}

	public function getActionEmote() : ?EmoteType {
		return $this->actionEmote;
	}

	public function getEmote() : ?EmoteType {
		return $this->emote;
	}

	public function hasGravity() : bool {
		return $this->gravityEnabled;
	}

	public function setLocation(?Location $location) : self {
		$this->location = $location;
		return $this;
	}

	public function setMotion(?Vector3 $motion) : self {
		$this->motion = $motion;
		return $this;
	}

	public function setNameTag(?string $nameTag) : self {
		$this->nameTag = $nameTag;
		return $this;
	}

	public function setScale(float $scale) : self {
		$this->scale = $scale;
		return $this;
	}

	public function setBaby(bool $isBaby) : self {
		$this->isBaby = $isBaby;
		return $this;
	}

	public function setRotationEnabled(bool $rotationEnabled) : self {
		$this->rotationEnabled = $rotationEnabled;
		return $this;
	}

	public function setNametagVisible(bool $nametagVisible) : self {
		$this->nametagVisible = $nametagVisible;
		return $this;
	}

	public function setVisibility(EntityVisibility $visibility) : self {
		$this->visibility = $visibility;
		return $this;
	}

	public function setSlapBack(bool $slapBack) : self {
		$this->slapBack = $slapBack;
		return $this;
	}

	public function setActionEmote(?EmoteType $actionEmote) : self {
		$this->actionEmote = $actionEmote;
		return $this;
	}

	public function setEmote(?EmoteType $emote) : self {
		$this->emote = $emote;
		return $this;
	}

	public function setHasGravity(bool $gravityEnabled) : self {
		$this->gravityEnabled = $gravityEnabled;
		return $this;
	}
}