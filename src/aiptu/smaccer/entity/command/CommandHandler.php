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

namespace aiptu\smaccer\entity\command;

use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function in_array;
use function str_starts_with;
use function substr;

class CommandHandler {
	public const string KEY_COMMAND = 'command';
	public const string KEY_TYPE = 'type';

	/** @var array<int, array{command: string, type: string}> */
	private array $commands = [];
	private int $nextId = 1;

	public function __construct(CompoundTag $nbt) {
		$commandsTag = $nbt->getTag(EntityTag::COMMANDS);
		if ($commandsTag instanceof ListTag) {
			foreach ($commandsTag as $tag) {
				if ($tag instanceof CompoundTag) {
					$command = $tag->getString(self::KEY_COMMAND);
					$type = $tag->getString(self::KEY_TYPE);
					$this->add($command, $type);
				}
			}
		}
	}

	/**
	 * Adds a command and returns its ID. If the command already exists or the type is invalid, returns null.
	 */
	public function add(string $command, string $type) : ?int {
		if (!$this->isValidType($type)) {
			return null;
		}

		$existingId = $this->getIdByCommandAndType($command, $type);
		if ($existingId !== null) {
			return null;
		}

		if (str_starts_with($command, '/')) {
			$command = substr($command, 1);
		}

		$id = $this->nextId++;
		$this->commands[$id] = [self::KEY_COMMAND => $command, self::KEY_TYPE => $type];
		return $id;
	}

	/**
	 * Edits an existing command identified by its ID.
	 * Returns true if successful, false if the command ID does not exist.
	 */
	public function edit(int $id, string $newCommand, string $newType) : bool {
		if (!$this->exists($id)) {
			return false;
		}

		if (!$this->isValidType($newType)) {
			return false;
		}

		$existingId = $this->getIdByCommandAndType($newCommand, $newType);
		if ($existingId !== null && $existingId !== $id) {
			return false;
		}

		$this->commands[$id] = [self::KEY_COMMAND => $newCommand, self::KEY_TYPE => $newType];
		return true;
	}

	/**
	 * Checks if the given type is valid.
	 */
	public function isValidType(string $type) : bool {
		return in_array($type, [EntityTag::COMMAND_TYPE_PLAYER, EntityTag::COMMAND_TYPE_SERVER], true);
	}

	/**
	 * Checks if a command with the given ID exists.
	 */
	public function exists(int $id) : bool {
		return isset($this->commands[$id]);
	}

	/**
	 * Retrieves the ID associated with the given command and type.
	 */
	public function getIdByCommandAndType(string $command, string $type) : ?int {
		foreach ($this->commands as $id => $data) {
			if ($data[self::KEY_COMMAND] === $command && $data[self::KEY_TYPE] === $type) {
				return $id;
			}
		}

		return null;
	}

	/**
	 * Retrieves all commands.
	 *
	 * @return array<int, array{command: string, type: string}>
	 */
	public function getAll() : array {
		return $this->commands;
	}

	/**
	 * Removes the command with the specified ID.
	 */
	public function removeById(int $id) : bool {
		if ($this->exists($id)) {
			unset($this->commands[$id]);
			return true;
		}

		return false;
	}

	/**
	 * Removes or clears all commands.
	 */
	public function clearAll() : void {
		$this->commands = [];
		$this->nextId = 1;
	}
}
