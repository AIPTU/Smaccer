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

namespace aiptu\smaccer\entity\emote;

use function array_key_exists;

class EmoteManager {
	/** @var array<string, EmoteType> */
	private array $emotesByUuid = [];

	/** @var array<string, EmoteType> */
	private array $emotesByTitle = [];

	/**
	 * @param list<array{uuid: string, title: string, image: string}> $emotes
	 */
	public function __construct(array $emotes) {
		$this->loadEmotes($emotes);
	}

	/**
	 * Load emotes from the given array.
	 *
	 * @param list<array{uuid: string, title: string, image: string}> $emotes
	 */
	public function loadEmotes(array $emotes) : void {
		$this->emotesByUuid = [];
		$this->emotesByTitle = [];

		foreach ($emotes as $emoteData) {
			$uuid = $emoteData['uuid'];
			$title = $emoteData['title'];
			$image = $emoteData['image'];

			$originalTitle = $title;
			$counter = 2;

			while (array_key_exists($title, $this->emotesByTitle)) {
				$title = $originalTitle . ' ' . $counter;
				++$counter;
			}

			$emote = new EmoteType($uuid, $title, $image);

			$this->emotesByUuid[$uuid] = $emote;
			$this->emotesByTitle[$title] = $emote;
		}
	}

	/**
	 * Check if a title already exists.
	 */
	public function ensureUniqueTitle(string $title) : bool {
		return array_key_exists($title, $this->emotesByTitle);
	}

	/**
	 * Get an emote by its UUID.
	 */
	public function getEmote(string $uuid) : ?EmoteType {
		return $this->emotesByUuid[$uuid] ?? null;
	}

	/**
	 * Return all emotes indexed by UUID.
	 *
	 * @return array<string, EmoteType>
	 */
	public function getAll() : array {
		return $this->emotesByUuid;
	}
}