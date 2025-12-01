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

namespace aiptu\smaccer\entity\trait;

use aiptu\smaccer\entity\command\CommandHandler;
use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Permissions;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_map;
use function microtime;
use function round;
use function str_replace;
use function strtolower;

trait CommandTrait {
	protected CommandHandler $commandHandler;

	protected array $commandCooldowns = [];

	public function initializeCommand(CompoundTag $nbt) : void {
		$this->commandHandler = new CommandHandler($nbt);
	}

	public function saveCommand(CompoundTag $nbt) : void {
		$commands = array_map(function ($commandData) {
			$commandTag = CompoundTag::create();
			$commandTag->setString(CommandHandler::KEY_COMMAND, $commandData[CommandHandler::KEY_COMMAND]);
			$commandTag->setString(CommandHandler::KEY_TYPE, $commandData[CommandHandler::KEY_TYPE]);
			return $commandTag;
		}, $this->commandHandler->getAll());

		$listTag = new ListTag($commands, NBT::TAG_Compound);
		$nbt->setTag(EntityTag::COMMANDS, $listTag);
	}

	public function canExecuteCommands(Player $player) : bool {
		$plugin = Smaccer::getInstance();
		$settings = $plugin->getDefaultSettings();
		$cooldownEnabled = $settings->isCommandCooldownEnabled();
		$cooldown = $settings->getCommandCooldownValue();

		if ($player->hasPermission(Permissions::BYPASS_COOLDOWN)) {
			return true;
		}

		if ($cooldownEnabled && $cooldown > 0) {
			$playerName = strtolower($player->getName());
			$npcId = $this->getId();
			$lastHitTime = $this->commandCooldowns[$playerName][$npcId] ?? 0.0;
			$currentTime = microtime(true);
			$remainingCooldown = ($cooldown + $lastHitTime) - $currentTime;

			if ($remainingCooldown > 0) {
				$player->sendMessage(TextFormat::RED . 'Please wait ' . round($remainingCooldown, 1) . ' seconds before interacting again.');
				return false;
			}

			$this->commandCooldowns[$playerName][$npcId] = $currentTime;
		}

		return true;
	}

	public function executeCommands(Player $player) : void {
		$commands = $this->commandHandler->getAll();
		$playerName = $player->getName();

		foreach ($commands as $commandData) {
			$command = str_replace('{player}', '"' . $playerName . '"', $commandData[CommandHandler::KEY_COMMAND]);
			$this->dispatchCommand($player, $command, $commandData[CommandHandler::KEY_TYPE]);
		}
	}

	public function dispatchCommand(Player $player, string $command, string $type) : void {
		$plugin = Smaccer::getInstance();
		$server = $plugin->getServer();
		$commandMap = $server->getCommandMap();

		match ($type) {
			EntityTag::COMMAND_TYPE_SERVER => $commandMap->dispatch(new ConsoleCommandSender($server, $server->getLanguage()), $command),
			EntityTag::COMMAND_TYPE_PLAYER => $commandMap->dispatch($player, $command),
			default => throw new \InvalidArgumentException("Invalid command type: {$type}")
		};
	}

	public function getCommandHandler() : CommandHandler {
		return $this->commandHandler;
	}

	public function addCommand(string $command, string $type) : ?int {
		return $this->commandHandler->add($command, $type);
	}

	public function editCommand(int $id, string $newCommand, string $newType) : bool {
		return $this->commandHandler->edit($id, $newCommand, $newType);
	}

	public function getCommands() : array {
		return $this->commandHandler->getAll();
	}

	public function removeCommandById(int $id) : bool {
		return $this->commandHandler->removeById($id);
	}

	public function clearCommands() : void {
		$this->commandHandler->clearAll();
	}
}
