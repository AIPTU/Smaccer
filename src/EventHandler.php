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
}
