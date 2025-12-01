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

namespace aiptu\smaccer\entity\emote;

class EmoteType {
	public function __construct(
		private string $uuid,
		private string $title,
		private string $image
	) {}

	/**
	 * The UUID of the emote.
	 */
	public function getUuid() : string {
		return $this->uuid;
	}

	/**
	 * The Title of the emote.
	 */
	public function getTitle() : string {
		return $this->title;
	}

	/**
	 * The Image Url of the emote.
	 */
	public function getImage() : string {
		return $this->image;
	}
}
