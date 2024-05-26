<?php

declare(strict_types=1);

namespace aiptu\smaccer;

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\utils\Queue;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use function atan2;
use const M_PI;

class EventHandler implements Listener {
	public function onQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();
		$playerId = $player->getUniqueId()->getBytes();

		if (Queue::isInQueue($playerId)) {
			Queue::removeFromQueue($playerId);
		}
	}

	public function onMove(PlayerMoveEvent $event) : void {
		$player = $event->getPlayer();

		if ($event->getFrom()->distance($event->getTo()) < 0.1) {
			return;
		}

		$world = $player->getWorld();
		$playerPos = $player->getLocation();
		$playerVector2 = new Vector2($playerPos->x, $playerPos->z);
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

				$entityPos = $entity->getLocation();
				$angle = atan2($playerPos->z - $entityPos->z, $playerPos->x - $entityPos->x);
				$yaw = (($angle * 180) / M_PI) - 90;

				$distance2D = $playerVector2->distance(new Vector2($entityPos->x, $entityPos->z));
				$pitchAngle = atan2($distance2D, $playerPos->y - $entityPos->y);
				$pitch = (($pitchAngle * 180) / M_PI) - 90;

				if ($entity instanceof HumanSmaccer) {
					$pk = new MovePlayerPacket();
					$pk->actorRuntimeId = $entity->getId();
					$pk->position = $entityPos->add(0, $entity->getEyeHeight(), 0);
					$pk->yaw = $yaw;
					$pk->pitch = $pitch;
					$pk->headYaw = $yaw;
					$pk->onGround = $entity->onGround;
				} else {
					$pk = new MoveActorAbsolutePacket();
					$pk->actorRuntimeId = $entity->getId();
					$pk->position = $entityPos->asVector3();
					$pk->yaw = $yaw;
					$pk->pitch = $pitch;
					$pk->headYaw = $yaw;
				}

				$player->getNetworkSession()->sendDataPacket($pk);
			}
		}
	}
}
