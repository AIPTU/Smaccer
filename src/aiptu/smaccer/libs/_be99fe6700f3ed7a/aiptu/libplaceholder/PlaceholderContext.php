<?php

/*
 * Copyright (c) 2024 - 2025 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/libplaceholder
 */

declare(strict_types=1);

namespace aiptu\smaccer\libs\_be99fe6700f3ed7a\aiptu\libplaceholder;

use pocketmine\player\Player;
use function array_key_exists;

/**
 * @phpstan-type ContextData array<string, mixed>
 */
final readonly class PlaceholderContext {
	/**
	 * @param ContextData $data
	 */
	public function __construct(
		private ?Player $player = null,
		private array $data = []
	) {}

	public function getPlayer() : ?Player {
		return $this->player;
	}

	/**
	 * @template T
	 *
	 * @param T $default
	 *
	 * @return mixed|T
	 */
	public function getData(string $key, mixed $default = null) : mixed {
		return $this->data[$key] ?? $default;
	}

	/**
	 * @return self New instance with updated data (immutable)
	 */
	public function withData(string $key, mixed $value) : self {
		return new self($this->player, [...$this->data, $key => $value]);
	}

	public function hasData(string $key) : bool {
		return array_key_exists($key, $this->data);
	}

	/**
	 * @return ContextData
	 */
	public function getAllData() : array {
		return $this->data;
	}
}