<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/libplaceholder
 */

declare(strict_types=1);

namespace aiptu\smaccer\libs\_29f5cd4b60e216f2\aiptu\libplaceholder;

use pocketmine\player\Player;

class PlaceholderContext {
	public function __construct(
		private ?Player $player = null,
		private array $data = []
	) {}

	/**
	 * Get the Player object, if available.
	 *
	 * @return Player|null the player, or null if not applicable
	 */
	public function getPlayer() : ?Player {
		return $this->player;
	}

	/**
	 * Get additional context data.
	 *
	 * @param string $key     the key to retrieve from context data
	 * @param mixed  $default the default value if the key doesn't exist
	 *
	 * @return mixed the value associated with the key
	 */
	public function getData(string $key, $default = null) {
		return $this->data[$key] ?? $default;
	}

	/**
	 * Set additional context data.
	 *
	 * @param string $key   the key for the data
	 * @param mixed  $value the value to set for the key
	 *
	 * @return $this for chaining
	 */
	public function setData(string $key, $value) : self {
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Check if context has a specific key.
	 *
	 * @param string $key the key to check for
	 *
	 * @return bool true if the key exists, false otherwise
	 */
	public function hasData(string $key) : bool {
		return isset($this->data[$key]);
	}
}