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

namespace aiptu\smaccer;

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\event\NPCAttackEvent;
use aiptu\smaccer\event\NPCInteractEvent;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\utils\Queue;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function atan2;
use function rad2deg;
use function sqrt;

class EventHandler implements Listener {
	public function onQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();
		$playerName = $player->getName();

		Queue::removeFromAllQueues($playerName);
	}

	public function onMove(PlayerMoveEvent $event) : void {
		$player = $event->getPlayer();

		if ($event->getFrom()->distance($event->getTo()) < 0.1) {
			return;
		}

		$world = $player->getWorld();
		$playerLocation = $player->getLocation();
		$maxLookDistance = Smaccer::getInstance()->getDefaultSettings()->getMaxDistance();
		$boundingBox = $player->getBoundingBox()->expandedCopy($maxLookDistance, $maxLookDistance, $maxLookDistance);

		foreach ($world->getNearbyEntities($boundingBox, $player) as $entity) {
			if (($entity instanceof HumanSmaccer) || ($entity instanceof EntitySmaccer)) {
				$visibility = $entity->getVisibility();
				$creator = $entity->getCreator();
				if ($visibility === EntityVisibility::INVISIBLE_TO_EVERYONE
					|| ($visibility === EntityVisibility::VISIBLE_TO_CREATOR && ($creator === null || $creator !== $player))) {
					continue;
				}

				if (!$entity->canRotateToPlayers()) {
					return;
				}

				$entityLocation = $entity->getLocation();
				$xdiff = $playerLocation->x - $entityLocation->x;
				$zdiff = $playerLocation->z - $entityLocation->z;
				$ydiff = $playerLocation->y - $entityLocation->y;

				$yaw = rad2deg(atan2($zdiff, $xdiff)) - 90;
				$dist = sqrt($xdiff ** 2 + $zdiff ** 2);
				$pitch = rad2deg(atan2($dist, $ydiff)) - 90;

				$entity->setRotation($yaw, $pitch);
			}
		}
	}

	public function onEffectAdd(EntityEffectAddEvent $event) : void {
		$entity = $event->getEntity();

		if (($entity instanceof HumanSmaccer) || ($entity instanceof EntitySmaccer)) {
			$event->cancel();
		}
	}

	public function onAttack(EntityDamageEvent $event) : void {
		if ($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			$entity = $event->getEntity();

			if (($entity instanceof HumanSmaccer) || ($entity instanceof EntitySmaccer)) {
				if ($entity->getVisibility() === EntityVisibility::INVISIBLE_TO_EVERYONE) {
					return;
				}

				if ($damager instanceof Player) {
					$npcAttackEvent = new NPCAttackEvent($damager, $entity);
					$npcAttackEvent->call();
					if ($npcAttackEvent->isCancelled()) {
						$event->cancel();
						return;
					}

					$npcId = $entity->getId();
					$playerName = $damager->getName();
					if (Queue::isInQueue($playerName, Queue::ACTION_RETRIEVE)) {
						$damager->sendMessage(TextFormat::GREEN . 'NPC Entity ID: ' . $npcId);
						Queue::removeFromQueue($playerName, Queue::ACTION_RETRIEVE);
					} elseif (Queue::isInQueue($playerName, Queue::ACTION_DELETE)) {
						if (!$entity->isOwnedBy($damager) && !$damager->hasPermission(Permissions::COMMAND_DELETE_OTHERS)) {
							$damager->sendMessage(TextFormat::RED . "You don't have permission to delete this entity!");
							return;
						}

						SmaccerHandler::getInstance()->despawnNPC($entity->getCreatorId(), $entity)->onCompletion(
							function (bool $success) use ($damager, $npcId, $entity) : void {
								$damager->sendMessage(TextFormat::GREEN . 'NPC ' . $entity->getName() . ' with ID ' . $npcId . ' despawned successfully.');
							},
							function (\Throwable $e) use ($damager) : void {
								$damager->sendMessage(TextFormat::RED . 'Failed to despawn npc: ' . $e->getMessage());
							}
						);
						Queue::removeFromQueue($playerName, Queue::ACTION_DELETE);
					}
				}

				$event->cancel();
			}
		}
	}

	public function onInteract(PlayerEntityInteractEvent $event) : void {
		$player = $event->getPlayer();
		$entity = $event->getEntity();

		if (($entity instanceof HumanSmaccer || $entity instanceof EntitySmaccer)
			&& $entity->getVisibility() !== EntityVisibility::INVISIBLE_TO_EVERYONE) {
			$npcInteractEvent = new NPCInteractEvent($player, $entity);
			$npcInteractEvent->call();
			if ($npcInteractEvent->isCancelled()) {
				return;
			}

			if ($entity->canExecuteCommands($player)) {
				$entity->executeCommands($player);
			}

			if ($entity instanceof HumanSmaccer) {
				if ($entity->canSlapBack()) {
					$entity->broadcastAnimation(new ArmSwingAnimation($entity));
				}

				if ($entity->getActionEmote() !== null) {
					$emoteUuid = $entity->getActionEmote()->getUuid();
					$settings = Smaccer::getInstance()->getDefaultSettings();

					if ($settings->isActionEmoteCooldownEnabled()) {
						if ($player->hasPermission(Permissions::BYPASS_COOLDOWN) || $entity->handleActionEmoteCooldown($emoteUuid)) {
							$entity->broadcastEmote($emoteUuid, [$player]);
						}
					} else {
						$entity->broadcastEmote($emoteUuid, [$player]);
					}
				}
			}

			$event->cancel();
		}
	}
}
