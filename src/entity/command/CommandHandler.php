<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\command;

use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function in_array;

class CommandHandler {
	public const KEY_COMMAND = 'command';
	public const KEY_TYPE = 'type';

	/** @var array<int, array{command: string, type: string}> */
	private array $commands = [];
	private int $nextId = 1;

	public function __construct(CompoundTag $nbt) {
		if (($commandsTag = $nbt->getTag(EntityTag::COMMANDS)) instanceof ListTag) {
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
		if (!in_array($type, [EntityTag::COMMAND_TYPE_PLAYER, EntityTag::COMMAND_TYPE_SERVER], true)) {
			return null;
		}

		foreach ($this->commands as $id => $data) {
			if ($data[self::KEY_COMMAND] === $command && $data[self::KEY_TYPE] === $type) {
				return null;
			}
		}

		$id = $this->nextId++;
		$this->commands[$id] = [self::KEY_COMMAND => $command, self::KEY_TYPE => $type];
		return $id;
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
}
