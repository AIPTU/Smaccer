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

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\emote\EmoteType;
use aiptu\smaccer\entity\utils\EntityVisibility;

final class NPCData {
	private ?string $nameTag = null;
	private float $scale = 1.0;
	private bool $baby = false;
	private bool $rotationEnabled = true;
	private bool $nametagVisible = true;
	private EntityVisibility $visibility = EntityVisibility::VISIBLE_TO_EVERYONE;
	private bool $slapBack = false;
	private ?EmoteType $actionEmote = null;
	private ?EmoteType $emote = null;
	private bool $gravityEnabled = true;

	public static function create() : self {
		return new self();
	}

	public function getNameTag() : ?string {
		return $this->nameTag;
	}

	public function setNameTag(?string $nameTag) : self {
		$this->nameTag = $nameTag;
		return $this;
	}

	public function getScale() : float {
		return $this->scale;
	}

	public function setScale(float $scale) : self {
		$this->scale = $scale;
		return $this;
	}

	public function isBaby() : bool {
		return $this->baby;
	}

	public function setBaby(bool $baby) : self {
		$this->baby = $baby;
		return $this;
	}

	public function isRotationEnabled() : bool {
		return $this->rotationEnabled;
	}

	public function setRotationEnabled(bool $rotationEnabled) : self {
		$this->rotationEnabled = $rotationEnabled;
		return $this;
	}

	public function isNametagVisible() : bool {
		return $this->nametagVisible;
	}

	public function setNametagVisible(bool $nametagVisible) : self {
		$this->nametagVisible = $nametagVisible;
		return $this;
	}

	public function getVisibility() : EntityVisibility {
		return $this->visibility;
	}

	public function setVisibility(EntityVisibility $visibility) : self {
		$this->visibility = $visibility;
		return $this;
	}

	public function getSlapBack() : bool {
		return $this->slapBack;
	}

	public function setSlapBack(bool $slapBack) : self {
		$this->slapBack = $slapBack;
		return $this;
	}

	public function getActionEmote() : ?EmoteType {
		return $this->actionEmote;
	}

	public function setActionEmote(?EmoteType $actionEmote) : self {
		$this->actionEmote = $actionEmote;
		return $this;
	}

	public function getEmote() : ?EmoteType {
		return $this->emote;
	}

	public function setEmote(?EmoteType $emote) : self {
		$this->emote = $emote;
		return $this;
	}

	public function hasGravity() : bool {
		return $this->gravityEnabled;
	}

	public function setHasGravity(bool $gravityEnabled) : self {
		$this->gravityEnabled = $gravityEnabled;
		return $this;
	}
}
