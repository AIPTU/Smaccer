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

use aiptu\libsounds\SoundInstance;
use aiptu\smaccer\entity\command\CommandHandler;
use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\utils\Queue;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function array_map;
use function microtime;
use function round;
use function str_replace;
use function strtolower;

abstract class EntitySmaccer extends Entity {
	protected string $creator;
	protected EntityVisibility $visibility = EntityVisibility::VISIBLE_TO_EVERYONE;
	protected CommandHandler $commandHandler;
	protected bool $rotateToPlayers = true;

	private array $commandCooldowns = [];

	public function __construct(Location $location, ?CompoundTag $nbt = null) {
		if ($nbt instanceof CompoundTag) {
			$this->creator = $nbt->getString(EntityTag::CREATOR);

			$this->commandHandler = new CommandHandler($nbt);
		}

		parent::__construct($location, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);

		$this->setScale($nbt->getFloat(EntityTag::SCALE, 1.0));
		$this->setRotateToPlayers((bool) $nbt->getByte(EntityTag::ROTATE_TO_PLAYERS, 1));
		$this->setNameTagAlwaysVisible((bool) $nbt->getByte(EntityTag::NAMETAG_VISIBLE, 1));
		$this->setNameTagVisible((bool) $nbt->getByte(EntityTag::NAMETAG_VISIBLE, 1));
		$this->setVisibility(EntityVisibility::fromInt($nbt->getInt(EntityTag::VISIBILITY, EntityVisibility::VISIBLE_TO_EVERYONE->value)));
		$this->setNoClientPredictions();
	}

	public function saveNBT() : CompoundTag {
		$nbt = parent::saveNBT();

		$nbt->setString(EntityTag::CREATOR, $this->creator);
		$nbt->setFloat(EntityTag::SCALE, $this->scale);
		$nbt->setByte(EntityTag::ROTATE_TO_PLAYERS, (int) $this->rotateToPlayers);
		$nbt->setByte(EntityTag::NAMETAG_VISIBLE, (int) $this->isNameTagVisible());
		$nbt->setInt(EntityTag::VISIBILITY, $this->visibility->value);

		$commands = array_map(function ($commandData) {
			$commandTag = CompoundTag::create();
			$commandTag->setString(CommandHandler::KEY_COMMAND, $commandData[CommandHandler::KEY_COMMAND]);
			$commandTag->setString(CommandHandler::KEY_TYPE, $commandData[CommandHandler::KEY_TYPE]);
			return $commandTag;
		}, $this->commandHandler->getAll());

		$listTag = new ListTag($commands, NBT::TAG_Compound);
		$nbt->setTag(EntityTag::COMMANDS, $listTag);

		return $nbt;
	}

	public function getVisibility() : EntityVisibility {
		return $this->visibility;
	}

	public function setVisibility(EntityVisibility $visibility) : void {
		if ($visibility === $this->visibility) {
			return;
		}

		$this->visibility = $visibility;

		switch ($visibility) {
			case EntityVisibility::VISIBLE_TO_EVERYONE:
				$this->spawnToAll();
				break;
			case EntityVisibility::VISIBLE_TO_CREATOR:
				$creator = $this->getCreator();
				if ($creator !== null) {
					$this->despawnFromAll();
					$this->spawnTo($creator);
				}

				break;
			case EntityVisibility::INVISIBLE_TO_EVERYONE:
				$this->despawnFromAll();
				break;
		}
	}

	public function spawnTo(Player $player) : void {
		if ($this->visibility === EntityVisibility::INVISIBLE_TO_EVERYONE) {
			return;
		}

		parent::spawnTo($player);
	}

	public function attack(EntityDamageEvent $source) : void {
		if ($this->visibility === EntityVisibility::INVISIBLE_TO_EVERYONE || !($source instanceof EntityDamageByEntityEvent)) {
			return;
		}

		$damager = $source->getDamager();
		if ($damager instanceof Player) {
			$npcId = $this->getId();
			$playerName = $damager->getName();
			if (Queue::isInQueue($playerName, Queue::ACTION_RETRIEVE)) {
				$damager->sendMessage(TextFormat::GREEN . 'NPC Entity ID: ' . $npcId);
				Queue::removeFromQueue($playerName, Queue::ACTION_RETRIEVE);
			} elseif (Queue::isInQueue($playerName, Queue::ACTION_DELETE)) {
				if (!$this->isOwnedBy($damager) && !$damager->hasPermission(Permissions::COMMAND_DELETE_OTHERS)) {
					$damager->sendMessage(TextFormat::RED . "You don't have permission to delete this entity!");
					return;
				}

				SmaccerHandler::getInstance()->despawnNPC($damager, $this);
				Queue::removeFromQueue($playerName, Queue::ACTION_DELETE);
			}
		}

		$source->cancel();
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool {
		if ($this->visibility === EntityVisibility::INVISIBLE_TO_EVERYONE) {
			return false;
		}

		if ($this->canExecuteCommands($player)) {
			$this->executeCommands($player);
		}

		return true;
	}

	private function canExecuteCommands(Player $player) : bool {
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

	private function executeCommands(Player $player) : void {
		$commands = $this->commandHandler->getAll();
		$playerName = $player->getName();

		foreach ($commands as $commandData) {
			$command = str_replace('{player}', '"' . $playerName . '"', $commandData[CommandHandler::KEY_COMMAND]);
			$this->dispatchCommand($player, $command, $commandData[CommandHandler::KEY_TYPE]);
		}
	}

	private function dispatchCommand(Player $player, string $command, string $type) : void {
		$plugin = Smaccer::getInstance();
		$server = $plugin->getServer();
		$commandMap = $server->getCommandMap();

		match ($type) {
			EntityTag::COMMAND_TYPE_SERVER => $commandMap->dispatch(new ConsoleCommandSender($server, $server->getLanguage()), $command),
			EntityTag::COMMAND_TYPE_PLAYER => $commandMap->dispatch($player, $command),
			default => throw new \InvalidArgumentException("Invalid command type: {$type}")
		};
	}

	public function addSound(SoundInstance $soundInstance) : void {
		$this->broadcastSound($soundInstance);
	}

	public function getCreatorId() : string {
		return $this->creator;
	}

	public function getCreator() : ?Player {
		return Server::getInstance()->getPlayerByRawUUID($this->creator);
	}

	public function isOwnedBy(Player $player) : bool {
		return $player->getUniqueId()->getBytes() === $this->creator;
	}

	public function getCommandHandler() : CommandHandler {
		return $this->commandHandler;
	}

	public function setRotateToPlayers(bool $value = true) : void {
		$this->rotateToPlayers = $value;
	}

	public function canRotateToPlayers() : bool {
		return $this->rotateToPlayers;
	}

	abstract protected function getInitialSizeInfo() : EntitySizeInfo;

	abstract public static function getNetworkTypeId() : string;

	abstract public function getName() : string;

	protected function getInitialDragMultiplier() : float {
		return 0.00;
	}

	protected function getInitialGravity() : float {
		return 0.00;
	}
}
